<?php
namespace app\cms\admin\controller;

use app\admin\controller\AdminBase;
use think\App;
class Index extends AdminBase
{
    function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->set = new \app\cms\admin\model\Article();
    }
    public function index(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            $list = $this->set->getArticleList();
            $this->echoJson(0,$list['count'],$list['data'],'获取成功');
        }
        return $this->fetch();
    }
}