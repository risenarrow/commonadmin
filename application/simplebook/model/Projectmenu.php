<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/13
 * Time: 14:43
 */

namespace app\simplebook\model;
use app\common\model\FrontModel;
use think\Db;
use think\Exception;
class Projectmenu extends FrontModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SIMPLEBOOK_MENU__';

    public function getList(){
        $limit = $this->param['limit'];
        $data = $this->param;
        $where = [];
        if(isset($data['project_id']) && intval($data['project_id']) > 0){
            $where[] = ['project_id','=',intval($data['project_id'])];
        }
        $list = self::where($where);

        if(isset($this->param['order']) && in_array($this->param['order'],['asc','desc'])
            &&isset($this->param['field'])&& in_array($this->param['field'],['addtime'])){
            $list = $list->order($this->param['field'].' '. $this->param['order']);
        }
        $list = $list->field('menu_title,price,id')->paginate($limit);
        $arr = [];
        foreach($list as $k=>$v){
            $arr[$k] = $v;
        }
        return ['data'=>$arr,'count'=>$list->total()];
    }

    public function getDetail(){
        $data = $this->param;
        $info = $this->getInfoById($data['menu_id']);
        if($info){
            //获取属性
            $attrcat_list = Db::name('simplebook_attrcat')->where('menu_id','=',$info['id'])->select();
            foreach ($attrcat_list as $k=>$v){
                $attrcat_list[$k]['attr_list'] = Db::name('simplebook_attr')->where('attr_cat_id','=',$v['id'])->select();

            }
            $info['attr_cat_list']  = $attrcat_list;
          $this->msg="获取成功";
           return $info;
        }
        $this->msg = "菜单不存在";
        return false;

    }




    public function getInfoById($id=0){
        return self::where('id','=',$id)->find();
    }
}