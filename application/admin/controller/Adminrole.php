<?php
/**
 *
 * 管理員控制器
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/5/24
 * Time: 20:36
 */

namespace app\admin\controller;

use app\admin\controller\AdminBase;
use think\App;
use app\admin\model\Adminrole as AdminroleModel;

class Adminrole extends AdminBase
{
    private $set = '';
    function __construct(App $app = null)
    {
        parent::__construct($app);
        //实例化管理员模型
        $this->set = new AdminroleModel;
    }

    /**
     * 管理员列表
     * @return mixed
     * @author yang
     * Date: 2022/5/25
     */
    public function index(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            $data = $this->set->getRolelist();
            $this->echoJson(0,$data['count'],$data['data'],'获取成功');
        }
        return $this->fetch();
    }

    /**
     *
     * 添加管理员方法
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/25
     */
    public function add(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->add()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }
        return $this->fetch();
    }


    /**
     *
     * 编辑管理员方法
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/25
     */
    public function edit(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->edit()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }

        $id = input('id',0,'intval');
        //获取指定id
        $data = $this->set->getRoleInfoById($id);
        $this->assign([
            'data'=>$data
        ]);
        return $this->fetch();
    }

    public function del(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->del()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }
    }


    /**
     *
     * 设置角色权限的页面
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/30
     */
    public function setpriv(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            $id = $this->request->param('id/d');
            //获取权限列表
            $priv_list = $this->set->getPrivByRoleId($id,1);
            //获取权限目录树
            $tree = $this->set->getTree();
            $this->echoJson(0,1,['priv_list'=>array_column($priv_list,'menu_id'),'tree'=>$tree],'获取成功');
        }
        $id = $this->request->param('id');
        //获取角色信息
        $data = $this->set->getRoleInfoById($id);
        $this->assign([
            'data'=>$data,
            'id'=>$id
        ]);
        return $this->fetch();
    }

    public function changepriv(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->changepriv()){
                $this->success('修改成功！');
            }
            $this->error($this->set->getMsg());
        }
    }

}