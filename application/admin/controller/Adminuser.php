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
use app\admin\model\User as UserModel;

class Adminuser extends AdminBase
{
    private $set = '';
    function __construct(App $app = null)
    {
        parent::__construct($app);
        //实例化管理员模型
        $this->set = new UserModel;
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
            $data = $this->set->getAdminuserList();
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
        $this->assign([
            'role_list'=>$this->set->getRoleList()
        ]);
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
        $data = db('admin')->where('id','=',$id)->field('id,role_id,admin_name')->find();
        $this->assign([
            'data'=>$data,
            'role_list'=>$this->set->getRoleList()
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

}