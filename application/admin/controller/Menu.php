<?php
namespace app\admin\controller;
use app\admin\controller\AdminBase;
use app\admin\model\Menu as MenuModel;
use think\Validate;
use think\facade\Env;


class Menu extends AdminBase
{
    /*菜单列表*/
    public function index()
    {
         $menulist = MenuModel::list_menu();
         $this->assign('menulist',$menulist);
        return $this->fetch();
    }


    public function add(){
        $parentid = input('parentid',0,'intval');

        if(request()->isAjax()){
            $data = $this->request->param('data');
            $result = $this->validate($data,'Menu');
            if(true !== $result){
                $this->error($result);
            }

            if(MenuModel::create($data)){
                delFileByDir(Env::get('root_path').'runtime'.'/');
                $this->success('添加成功',url('admin/Menu/index'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $list_menu = MenuModel::list_menu();
            $this->assign('list_menu',$list_menu);
            $this->assign('parentid',$parentid);
            return $this->fetch();
        }
    }

    public function edit(){
        $id = input('id',0,'intval');
        $parentid = input('parentid',0,'intval');
        if(request()->isAjax()){
            $data = $this->request->param('data');
            $result = $this->validate($data,'Menu.edit');
            if(true !== $result){
                $this->error($result);
            }

            if($data['id']){
                if(MenuModel::where(array('id'=>$data['id']))->update($data)){
                    delFileByDir(Env::get('root_path').'runtime'.'/');
                    $this->success('修改成功',url('admin/Menu/index'));
                }else{
                    $this->error('修改失败');
                }
            }else{
                $this->error('修改失败');
            }
        }else{
            $data = MenuModel::get($id);
            $list_menu =   $list_menu = MenuModel::list_menu();

            $this->assign('data',$data);
            $this->assign('list_menu',$list_menu);
            $this->assign('parentid',$parentid);
            return $this->fetch();
        }
    }

    public function del(){
        $id=input('id');
        $menuinfo =MenuModel::where('parentid','=',$id)->find();
        if(!empty($menuinfo)){
            $this->error('改菜单下还有子菜单，请先删除子菜单');
        }
        if(MenuModel::where('id','=',$id)->delete()){
            delFileByDir(Env::get('root_path').'runtime'.'/');
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }


}
