<?php
namespace app\admin\model;

use think\facade\Cache;
use think\facade\Config;
use think\Model;

class Menu extends Model
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__MENU__';


    /**
     *  获取顶级id和次级id
     * @param string $curModule     当前模型
     * @param string $curController     当前控制器
     * @param string $curAction       当前操作
     * @return array
     * @author yang
     * Date: 2019/11/18
     */
    public static function topId_leftId($curModule='',$curController='',$curAction=''){
//        $topId_leftId = Cache::get('topId_leftId');
//        if(!$topId_leftId){
            $topId_leftId = self::get_topId_leftId($curModule,$curController,$curAction);
//            Cache::set('topId_leftId',$topId_leftId);
//        }
        return $topId_leftId;
    }

    private static function get_topId_leftId($curModule='',$curController='',$curAction=''){
        $flag = true;
        $topid = 0;
        $leftid = 0;
        $map[] = ['m','=',$curModule];
        $map[] = ['c','=',$curController];
        $map[] = ['a','=',$curAction];

        $curlink = self::where($map)->find();

        while ($flag&&$curlink){
            if($curlink['parentid'] == '0'){
                $topid = $curlink['id'];
                $flag = false;
            }else{
                $leftid = $curlink['id'];
                $curlink = self::where(['id'=>$curlink['parentid']])->find();
            }
        }
        return array('topid'=>$topid,'leftid'=>$leftid);
    }


    /**
     * 过滤不属于该角色菜单
     * @param $menu         菜单列表
     * @author yang
     * Date: 2019/11/18
     */
    public static function filter_auth_menu(&$menu,$admin=array()){
        if($admin['role_id'] == 1){
            return ;
        }
        $admin_auth = AdminRolePriv::getAuth($admin['role_id']);
        $menuids = array_column($admin_auth,'menu_id');
        self::filter_auth_menu_priv($menu,$menuids);
    }

    private static function filter_auth_menu_priv(&$menu,$menuids=[]){
        foreach($menu as $k=>$v){
            if(!in_array($v['id'],$menuids)){
                unset($menu[$k]);
            }else{
                if(!empty($v['child'])){
                    self::filter_auth_menu_priv($menu[$k]['child'],$menuids);
                }
            }

        }
    }

    //获取顶部导航
    public static function getTopNav(){
        $topNav = self::where(['parentid'=>0,'show'=>1])->select();
        foreach($topNav as $k=>$v){
            $topNav[$k]['url'] = url($v['m'].'/'.$v['c']."/".$v['a']);
        }
        return $topNav;
    }

    //获取左边导航
    public static function getLeftNav($curModule='',$parentid=0,$level=0){
        $list = Cache::get('LeftNav');
        $arr = [];
        if(!isset($list[$curModule])){
            $list = self::getLeftNavPriv('',$parentid,$level);

            $list = is_object($list)? $list->toArray():$list;

            $list = array_combine(array_column($list,'module_name'),$list);

            Cache::set('LeftNav',$list);
        }
        if(!$curModule){
            return $list;
        }
        $arr[$curModule] = $list[$curModule];
        return $arr;
    }


    private static function getLeftNavPriv($curModule='',$parentid=0,$level=0){

        if($level === 0 && $curModule){
            $parentid =  self::where(['parentid'=>0,'m'=> $curModule])->value('id');
        }
        $list = self::where(['parentid'=>$parentid,'show'=>1])->select();

        foreach($list as $k=>$v){
            if($level === 0){
                $list[$k]['module_name'] = $v['m'];
            }
            $list[$k]['level'] = $level;
            $list[$k]['url'] = admin_url($v['m']."/".$v['c']."/".$v['a']);
            $list[$k]['child'] = self::getLeftNavPriv($curModule,$v['id'],$level+1);
        }
        return $list;
    }



    /**
     * 获取菜单列表 有样式
     */
    public static function list_menu($parentid=0,$level=0){
        $list = self::where(['parentid'=>$parentid])->select();
        $arr = array();
        foreach($list as $k=>$v){
            $str = "";
            if($level == 1){
                $str = '&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp|--';
            }elseif($level > 1){
                $str .= "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
                for($i=1;$i<$level;$i++){
                    $str .= "|&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
                }
                $str .= '|--';
            }
            //$str .= '<i class="fa '.($v['icon']?$v['icon']:'fa-circle-o-notch').'"></i>'.$v['name'];
            $v['name'] = $str.$v['name'];
            $child = self::list_menu($v['id'],$level+1);

            $arr[] = $v;
            $arr = array_merge($arr,$child);
        }
        return $arr;
    }

    /*获取菜单列表*/
   public static function list_priv_menu($parentid=0,$level=0){
        $list = self::where(['parentid'=>$parentid])->select();
        $arr = array();
        foreach($list as $k=>$v){
            $child = self::list_priv_menu($v['id'],$level+1);
            $v['level'] = $level;
            $v['hassub'] = !empty($child)?1:0;
            $arr[] = $v;
            $arr = array_merge($arr,$child);
        }
        return $arr;
    }

    /**
     *
     * 如果当前链接在no_public_assign配置中，则不需要输出页面
     * @param string $curModule
     * @param string $curController
     * @param string $curAction
     * @return bool
     * @author yang
     * Date: 2022/6/4
     */
    public static function noPublicAssign($curModule='',$curController='',$curAction=''){
        $no_public_assign =Config::get('admin.no_public_assign');

        if(in_array($curModule.'/'.$curController.'/'.$curAction,$no_public_assign)){
            return true;
        }
        return false;
    }

}