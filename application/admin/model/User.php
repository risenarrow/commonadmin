<?php
namespace app\admin\model;

use app\common\utils\Yutils;
use app\admin\validate\User as Uservalidate;
use app\admin\model\PublicModel;
use think\facade\Config;
use think\facade\Hook;
use think\Db;
use app\admin\model\AdminRolePriv as AdminRolePrivModel;

class User extends PublicModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__ADMIN__';

    /**
     * 后台登录操作
     * @param string $username
     * @param string $password
     * @param array $params
     */
    public static function login($username='',$password='',$verifycode=''){
        $return = self::checkLogin($username,$password,$verifycode);
        if('0' == $return['status']) return ['status'=>0,'msg'=>$return['msg']];
        $admin_info = $return['data'];
        Hook::listen('after_login',$admin_info);
        return ['status'=>1];
    }


    public static function logout(){
        session('admin_id',null);
        session('admin',null);
        session('admin_name',null);
        return true;
    }

    /**
     *管理员操作日志
     */

   public  static function admin_log($admin_id,$type,$admin_name,$des=''){
        $data = array(
            'admin_id'=>$admin_id,
            'type'=>$type,
            'admin_name'=>$admin_name,
            'des'=>$des,
            'add_time'=>time()
        );
        Db::name('admin_action')->insert($data);
    }




    /*检查权限*/
    public static function check_auth($curModule='',$curController='',$curAction){
        $admin = session('admin');
        if(self::isSuperAdmin($admin['role_id'])){
            return true;
        }
        $privs = AdminRolePrivModel::getAuth($admin['role_id']);

        $count = false;
        foreach($privs as $k=>$v){
            if(strpos(strtolower($v['m'].'/'.$v['c'].'/'.$v['a']), $curModule.'/'.$curController.'/'.$curAction) !== false){
                $count = true;
                break;
            }
        }

        return $count;
    }



    //检查登录
    public static function checkLogin($username='',$password='',$verifycode=''){
        $admin_id = session('admin_id');
        if(!empty($admin_id)){
            return ['status'=>0,'msg'=>'你已登录！'];
        }
        //检查登录
        $validata = new Uservalidate();
        $data = ['admin_name'=>$username,'password'=>$password,'verifycode'=>$verifycode];
        if(!$validata->scene('Login')->check($data)){
            return ['status'=>0,'msg'=>$validata->getError()];
        }

        $admin_info = self::where('admin_name','=',$username)->find();
        if(empty($admin_info)) return ['status'=>0,'msg'=>'账号或密码错误！'];
        if(Yutils::encrypt($password,$admin_info['salt']) != $admin_info['password']) return ['status'=>0,'msg'=>'账号或密码错误！'];
        if($admin_info['status'] != 1) return ['status'=>0,'msg'=>'账号已被禁用'];

        //检查登录时间
        if(!self::isSuperAdmin($admin_info['role_id'])){
            $SITE_ALLOWLOGIN = Yutils::getSysConfig('SITE_ALLOWLOGIN');

            $w = date('w')%8;;
            if(strpos($SITE_ALLOWLOGIN,strval($w)) === false){
                return ['status'=>0,'msg'=>'不在登录时间！'];
            }
        }

        return ['status'=>1,'data'=>$admin_info];
    }

    /**
     *
     * 获取管理员列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/25
     */
    public function getAdminuserList(){
        $limit = $this->param['limit'];
        $list = db('admin')->paginate($limit);
        $arr = [];
        $rolelist = $this->getRoleList();
        foreach($list as $k=>$v){
            $v['lastlogin_time'] = date('Y-m-d H:i:s',$v['lastlogin_time']);
            $v['role_id'] = $rolelist[$v['role_id']]['role_name'];
            $arr[$k] = $v;
        }
        return ['data'=>$arr,'count'=>$list->total()];
    }

    /**
     * 添加管里员
     * @return bool
     * @author yang
     * Date: 2022/5/25
     */

    public function add(){
        $data = $this->param;
        $validata = new Uservalidate;
        if(!$validata->check($data)){
            $this->msg = $validata->getError();
            return false;
        }
        //重新组装数据
        $arr = [];
        $arr['role_id'] = $data['role_id'];
        $arr['admin_name'] = $data['admin_name'];
        $arr['salt'] = Yutils::getSalt();
        $arr['password'] = Yutils::encrypt($data['password'],$arr['salt']);
        //插入数据
        if(db('admin')->insert($arr)){
            $this->msg = '添加成功';
            return true;
        }
        $this->msg = '添加失败！';
        return false;
    }

    /**
     * 编辑管理员
     * @return bool
     * @author yang
     * Date: 2022/5/25
     */

    public function edit(){
        $data = $this->param;
        $validata = new Uservalidate;
        if(!$validata->scene('edit')->check($data)){
            $this->msg = $validata->getError();
            return false;
        }
        //重新组装数据
        $arr = [];
        $arr['role_id'] = $data['role_id'];
        $arr['admin_name'] = $data['admin_name'];
        $arr['salt'] = Yutils::getSalt();
        $arr['password'] = Yutils::encrypt($data['password'],$arr['salt']);
        //插入数据
        if(db('admin')->where('id','=',$data['id'])->update($arr)){
            $this->msg='更新成功';
            return true;
        }
        $this->msg = '更新失败！';
        return false;
    }


    public function del(){
        $data = $this->param;
        $data['id'] = intval($data['id']);
        if($data['id'] == 1){
            $this->msg = '超级管理员不能删除';return false;
        }
        if(db('admin')->where('id','=',$data['id'])->delete()){

            $this->msg = '删除成功';return true;
        }
        $this->msg ='删除失败';return false;
    }




    /**
     * 获取角色列表
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/25
     */
    public function getRoleList(){
        $arr = db('admin_role')->select();
        $arr1 = array_column($arr,'id');
        $arr = array_combine($arr1,$arr);
        return $arr;
    }

    /**
     *
     * 是否超级管理员
     * @param int $admin_id
     * @author yang
     * Date: 2022/6/2
     */
    public static function isSuperAdmin($role_id  = 0){
        if($role_id == 1){
            return true;
        }
        return false;
    }

}