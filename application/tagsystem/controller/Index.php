<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/5
 * Time: 11:59
 */

namespace app\tagsystem\controller;
use app\common\controller\FrontBase;
use app\tagsystem\model\Tag;
use think\App;

class Index extends FrontBase
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->set = new Tag();
    }

    public function index(){
        $this->set->setParam($this->request->param());
        $list = $this->set->getTagList();
        $this->echoJson('',0,$list['count'],$list['data']);
    }

    public function add(){
        $this->set->setParam($this->request->param());
        $id = $this->set->add();
        if($id!==false){
            $this->echoJson($this->set->getMsg(),0,0,['id'=>$id]);
        }
        $this->echoJson($this->set->getMsg(),1);
    }
    public function del(){
        $this->set->setParam($this->request->param());
        if($this->set->del()){
            $this->echoJson($this->set->getMsg(),0);
        }
        $this->echoJson($this->set->getMsg(),1);
    }

    public function istop(){
        $this->set->setParam($this->request->param());
        if($this->set->istop()){
            $this->echoJson($this->set->getMsg(),0);
        }
        $this->echoJson($this->set->getMsg(),1);
    }

    public function catlist(){
        $this->set->setParam($this->request->param());
        $list = $this->set->getCatList();
        $this->echoJson('',0,$list['count'],$list['data']);
    }
    public function chselectcat(){
        $this->set->setParam($this->request->param());
        if($this->set->chselectcat()){
            $this->echoJson($this->set->getMsg(),0);
        }
        $this->echoJson($this->set->getMsg(),1);
    }
}