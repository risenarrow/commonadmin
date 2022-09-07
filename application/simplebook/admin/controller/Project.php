<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/8
 * Time: 20:24
 */

namespace app\simplebook\admin\controller;
use app\admin\controller\AdminBase;
use think\App;

class Project extends AdminBase
{
    function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->set = new \app\simplebook\admin\model\Project();
    }

    public function index(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            $list = $this->set->getProjectList();
            $this->echoJson(0,$list['count'],$list['data'],'获取成功');
        }
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
        $data = $this->set->getProjectInfoById($this->request->param('id'));
        $this->assign([
            'data'=>$data
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