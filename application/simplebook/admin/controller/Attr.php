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

class Attr extends AdminBase
{
    function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->set = new \app\simplebook\admin\model\Attr();
    }

    public function index(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            $list = $this->set->getList();
            $this->success(0,1,$list,'获取成功');
        }
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
    }





}