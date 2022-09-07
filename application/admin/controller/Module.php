<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/5/31
 * Time: 16:08
 */

namespace app\admin\controller;
use app\admin\controller\AdminBase;
use think\App;

class Module extends AdminBase
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->set = new \app\admin\model\Module();
    }

    /**
     * 获取模块列表
     * @return mixed
     * @author yang
     * Date: 2022/5/31
     */
    public function index(){
        if($this->request->isAjax()){
            $list = $this->set->getModuleList();
            $this->echoJson(0,1,$list,'获取成功');
        }
        return $this->fetch();
    }


    /**
     * 改变模块状态，启用或禁用
     * @author yang
     * Date: 2022/5/31
     */
    public function status(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->changeStatus()){
                $this->success('修改成功！');
            }
            $this->error($this->set->getMsg());
        }
    }


    /**
     * 模块安装
     * @author yang
     * Date: 2022/5/31\
     */

    public function install(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->install()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }
    }

    /**
     * 卸载模块
     * @author yang
     * Date: 2022/6/1
     */

    public function uninstall(){
        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->uninstall()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }
    }

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


}