<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/5
 * Time: 15:06
 */

namespace app\tagsystem\admin\controller;
use app\admin\controller\AdminBase;
use app\tagsystem\admin\model\Tag;
use think\App;

class Index extends AdminBase
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->set = new Tag();
    }

    public function index(){
        return $this->fetch();
    }

    /**
     * 获取标签列表
     * @return mixed
     * @author yang
     * Date: 2022/6/6
     */
    public function taglist(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            $list = $this->set->getTagList();
            $this->echoJson(0,$list['count'],$list['data'],'获取成功');
        }
        return $this->fetch();
    }


    /**
     * 编辑标签
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
        $data = $this->set->getTagInfoById($this->request->param('id'));
        $this->assign([
            'data'=>$data
        ]);
        return $this->fetch();
    }


    /**
     * 添加标签
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

    public function istop(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->istop()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }
    }

   //添加标签分类
    public function addcat(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->addcat()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }
        return $this->fetch();
    }

    public function editcat(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->editcat()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }

        $this->assign([
            'data'=>$this->set->getCatDataById($this->request->param('id'))
        ]);
        return $this->fetch();
    }

    public function delcat(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->delcat()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }
    }

    public function catlist(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            $list = $this->set->getCatList();
            $this->echoJson(0,$list['count'],$list['data'],'获取成功');
        }
        return $this->fetch();
    }
}