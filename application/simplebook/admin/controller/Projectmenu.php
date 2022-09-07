<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/9
 * Time: 9:00
 */

namespace app\simplebook\admin\controller;

use think\App;
use app\admin\controller\AdminBase;

class Projectmenu extends AdminBase
{
    function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->set = new \app\simplebook\admin\model\Projectmenu();
    }

    public function index(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            $list = $this->set->getList();
            $this->echoJson(0,$list['count'],$list['data'],'获取成功');
        }
        $project_list = $this->set->getProjectList();
        $this->assign([
            'project_list'=>$project_list
        ]);
        return $this->fetch();
    }

    /**
     * 编辑
     * @return mixed
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/6/6
     */

    public function edit(){
        $this->set->setParam($this->request->param());
        if($this->request->isAjax()){
            if($this->set->edit()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }
        $data = $this->set->getInfoById($this->request->param('id'));
        //获取项目列表
        $list = $this->set->getProjectList();

        $this->assign([
            'data'=>$data,
            'project_list'=>$list
        ]);
        return $this->fetch();
    }


    /**
     * 添加
     * @return mixed
     * @author yang
     * Date: 2022/6/6
     */
    public function add(){
        $this->set->setParam($this->request->param());
        if($this->request->isAjax()){
            if($this->set->add()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }
        //获取项目列表
        $list = $this->set->getProjectList();
        $this->assign([
            'project_list'=>$list
        ]);
        return $this->fetch();
    }


    public function del(){
        $this->set->setParam($this->request->param());
        if($this->request->isAjax()){
            if($this->set->del()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }
    }
}