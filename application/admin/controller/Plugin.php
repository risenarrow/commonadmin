<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/5/31
 * Time: 16:08
 */

namespace app\admin\controller;
use app\admin\controller\AdminBase;
use app\common\utils\Yutils;
use think\App;
use think\Exception;

class Plugin extends AdminBase
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->set = new \app\admin\model\Plugin();
    }

    /**
     * 获取模块列表
     * @return mixed
     * @author yang
     * Date: 2022/5/31
     */
    public function index(){
        if($this->request->isAjax()){
            $list = $this->set->getPluginList();
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

    /**
     * 设置
     * @author yang
     * Date: 2022/8/3
     */
    public function setting(){


        if($this->request->isAjax()){
            $this->set->setParam($this->request->param());
            if($this->set->setting()){
                $this->success($this->set->getMsg());
            }
            $this->error($this->set->getMsg());
        }
        $plugin_name = $this->request->param('plugin_name');
        $setting = $this->set->getSetting($plugin_name);
        $config = $this->set->getConf($plugin_name);

        $this->assign([
            'config'=>$config,
            'setting'=>$setting,
            'plugin_name'=>$plugin_name
        ]);
        return $this->fetch();
    }


}