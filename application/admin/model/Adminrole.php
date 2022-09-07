<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/5/25
 * Time: 14:47
 */

namespace app\admin\model;

use app\admin\model\PublicModel;
use think\facade\Env;

class Adminrole extends PublicModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__ADMIN_ROLE__';

    public function __construct($data = [])
    {
        parent::__construct($data);
    }

    /**
     *
     * 获取角色列表
     * @return array
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/25
     */
    public function getRolelist(){
        $limit = $this->param['limit'];
        $list = db('admin_role')->paginate($limit);
        $arr = [];
        foreach($list as $k=>$v){
            $arr[$k] = $v;
        }
        return ['data'=>$arr,'count'=>$list->total()];
    }

    public function add(){
        $data = $this->param;
        $validate = new \app\admin\validate\Adminrole();
        if(!$validate->check($data)) {
            $this->msg = $validate->getError();return false;
        }
       if(db('admin_role')->insert($data)){
           $this->msg = '添加成功';return true;
       }
       $this->msg ='添加失败';return false;
    }

    public function edit(){
        $data = $this->param;
        $validate = new \app\admin\validate\Adminrole();
        if(!$validate->scene('edit')->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        $arr = [];
        $arr['role_name'] = $data['role_name'];
        $arr['description'] = $data['description'];
        if(db('admin_role')->where('id','=',$data['id'])->update($arr)){
            $this->msg = '修改成功';return true;
        }
        $this->msg ='修改失败';return false;
    }

    public function del(){
        $data = $this->param;
        $data['id'] = intval($data['id']);
        if(db('admin_role')->where('id','=',$data['id'])->delete()){
            $this->msg = '删除成功';return true;
        }
        $this->msg ='删除失败';return false;
    }


    /**
     * 通过id获取数据
     * @param int $id
     * @return mixed
     * @author yang
     * Date: 2022/5/30
     */
    public function getRoleInfoById($id=0){
        return db('admin_role')->where('id','=',$id)->field('id,role_name,description')->find();
    }


    /**
     * 获取权限目录树
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/30
     */
    public function getTree(){
        $data = $this->param;
//        $privList = $this->getPrivByRoleId($data['id']);
//        $privList = $privList->toArray();
//        $privList = array_column($privList,'menu_id');

        return $this->get_tree(0);
    }

    private function get_tree($parentid=0){
        $data = [];
        $children_list = $this->get_children($parentid);
        if ($children_list){
            foreach ($children_list as $k=>$v){
               $arr = [
                    'title'=>$v['name'],
                    'id'=>$v['id'],
                    'field'=>'name'.$v['id'],
//                    "checked"=>in_array($v['id'],$privList)?true:false,
                    'spread'=>true,
                    'children'=>$this->get_tree($v['id'])
                ];
                $data[]=$arr;
            }


        }else{
            return 0;
        }
        return $data;
    }

    private function get_children($parentid = 0){
        $list = Menu::where('parentid','=',$parentid)->select();
        return $list;
    }

    /**
     *
     * 根据角色id获取权限列表
     * @param int $role_id
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/30
     */
    public function getPrivByRoleId($role_id=0,$toarray=0){
        $privList = AdminRolePriv::where('role_id','=',$role_id)->select();
        if($toarray){
            $privList = $privList->toArray();
        }
        return $privList;
    }


    public function changepriv(){
        $data = $this->param;
        $id = intval($data['id']);$ids = isset($data['ids'])&&is_array($data['ids'])?implode(',',$data['ids']):[];
        if($id == 1){
            $this->msg = '超级管理员不能修改';return false;
        }
        $re = true;
        $role_priv = $this->getPrivByRoleId($id)->toArray();

        if($role_priv){
            $re = AdminRolePriv::where('role_id','=',$id)->delete();
        }

        if($ids && $re){
            $menu_list  = Menu::where('id','in',$ids)->select();

            $data  = [];
            foreach($menu_list as $k=>$v){
                $data [] = [
                    'role_id'=>$id,
                    'm'=>$v['m'],
                    'c'=>$v['c'],
                    'a'=>$v['a'],
                    'menu_id'=>$v['id']
                ];
            }
            $re = AdminRolePriv::insertAll($data);
        }
        if($re){
            //更新缓存
            delFileByDir(Env::get('root_path').'runtime'.'/');
            return true;
        }
        $this->msg = '修改失败';return false;
    }

}