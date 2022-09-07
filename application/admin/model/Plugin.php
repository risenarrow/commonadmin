<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/3
 * Time: 8:47
 */

namespace app\admin\model;
use think\facade\Env;
use think\Db;
use app\admin\model\User as UserModel;
class Plugin extends PublicModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__ADMIN_PLUGIN__';
    private $path ='';


    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->path = Env::get('app_path');
        $this->path = $this->path.DIRECTORY_SEPARATOR."plugin".DIRECTORY_SEPARATOR."package";
    }


    /**
     *
     * 获取插件列表
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/31
     */
    public function getPluginList(){
        //读取已安装模块数据库
        $list = $this->field('plugin_title,plugin_desc,plugin_name,status')->select();
        $list = $list->toArray();
        $list = array_combine(array_column($list,'plugin_name'),$list);
        //读取未安装模块
        $dir_list = @scandir( $this->path);

        foreach ($dir_list as $k=>$v){
            if($v == '.' || $v == '..'){
                unset($dir_list[$k]);
            }else{
                $install_path = $this->path.DIRECTORY_SEPARATOR.$v.DIRECTORY_SEPARATOR.'install.php';
                if(@file_exists($install_path)){
                    if(in_array($v,array_keys($list))){
                        $list[$v]['install'] = 1;
                    }else{
                        $list[$v]['install'] = 0;
                    }
                    $config = include  $install_path;
                    $list[$v]['plugin_title'] = $config['plugin_title'];
                    $list[$v]['plugin_name'] = $config['plugin_name'];
                    $list[$v]['plugin_desc'] = $config['plugin_desc'];
                }
            }
        }
        return $list;
    }

    /**
     *
     * 改变插件状态
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author yang
     * Date: 2022/5/31
     */
    public function changeStatus(){
        $data = $this->param;
        $validate = new \app\admin\validate\Plugin();
        if(!$validate->scene('status')->check($data)){
            $this->msg = $validate->getError();
            return false;
        }
        $info = self::getPluginInfoByPluginName($data['plugin_name']);
        if(!$info){
            $this->msg = '插件不存在或没安装';
            return false;
        }
        if($this->where('plugin_name','=',$data['plugin_name'])
            ->update(['status'=>$info['status'] == 1?0:1])){
            $this->msg = '修改成功';
            return true;
        }
        $this->msg = '修改失败';
        return false;
    }

    /**
     *
     * 根据插件标识获取模块信息
     * @param string $plugin_name
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/31
     */
    public static function  getPluginInfoByPluginName($plugin_name=''){
        $info = self::where('plugin_name','=',$plugin_name)->find()->toArray();
        return $info;
    }


    /**
     *
     * 获取插件当前状态
     * @param string $plugin_name
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/31
     */
    public static function  checkPluginStatus($plugin_name=''){
        $info = self::getPluginInfoByPluginName($plugin_name);
        if($info){
            return $info['status'];
        }
        return false;
    }



    /**
     *
     * 插件安装
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/6/1
     */

    public function install(){
        $data= $this->param;
        $validate = new \app\admin\validate\Plugin();

        //安装文件路径
        $install_path = $this->path.DIRECTORY_SEPARATOR.$data['plugin_name'].DIRECTORY_SEPARATOR.'install.php';
        //安装配置路径
        $install_config_path =   $this->path.DIRECTORY_SEPARATOR.$data['plugin_name'].DIRECTORY_SEPARATOR.'config.php';
        if(!@file_exists($install_config_path)){
            $this->msg = '配置文件不存在';return false;
        }

        $config = include $install_path;
        $insert = [
            'plugin_title'=>$config['plugin_title'],
            'plugin_name'=>$config['plugin_name'],
            'plugin_desc'=>$config['plugin_desc'],
            'status'=>1
        ];

        //检验模块信息正确性
        if(!$validate->check($insert)){
            $this->msg = $validate->getError();return false;
        }

        Db::startTrans();
        try{
            //添加记录并返回id
            $re1 = self::insertGetId($insert);
            $setting = include $install_config_path;
            $insertData = [];
            foreach ($setting as $key=>$v){
                if($v['type']=='radio'){
                    $insertData[$key] = (array_keys($v['value']))[0];
                }elseif($v['type'] == 'checkbox'){
                    $insertData[$key] = implode(",",array_keys($v['value']));
                }else{
                    $insertData[$key] = $v['value'];
                }

            }
            //插入配置表
            $insertData = serialize($insertData);
            Db::name("admin_plugin_setting")->insert(['plugin_id'=>$re1,'plugin_name'=>$config['plugin_name'],'setting'=>$insertData]);
            if($re1){
                //成功添加后，通知插件类
                $data = self::getPluginInfoByPluginName($config['plugin_name']);
                $classname = "app\\plugin\\package\\".$config['plugin_name']."\\".$config['plugin_name'];
               call_user_func([new $classname, 'afterInstall'], $data);
                Db::commit();
                UserModel::admin_log(session('admin_id'),2,session('admin_name'),'安装'.$data['plugin_name'].'插件');
                $this->msg = '安装成功!';return true;
            }else{
                Db::rollback();
                $this->msg = '安装失败!';return false;
            }
        }catch (\Exception $e){
            Db::rollback();
            $this->msg = $e->getMessage();
            return false;
        }
    }

    public function uninstall(){
        $data = $this->param;
        $validate = new \app\admin\validate\Plugin();
        if(!$validate->scene('status')->check($data)){
            $this->msg = $validate->getError();return false;
        }
        $info = self::where('plugin_name','=',$data['plugin_name'])->find();
        if(!$info){
            $this->msg = '插件未安装！';
            return false;
        }
        Db::startTrans();
        try{
            $data = self::getPluginInfoByPluginName($data['plugin_name']);
            $re2 = self::where('plugin_name','=',$data['plugin_name'])->delete();       //删除插件记录
            Db::name("admin_plugin_setting")->where('plugin_name','=',$data['plugin_name'])->delete();       //删除插件记录

            if($re2){
                //通知插件类卸载后操作
                $classname = "app\\plugin\\package\\".$data['plugin_name']."\\".$data['plugin_name'];
                call_user_func([new $classname, 'afterUninstall'], $data);
                call_user_func([new $classname, 'afterUninstall'], $data);
                Db::commit();
                UserModel::admin_log(session('admin_id'),2,session('admin_name'),'卸载'.$data['plugin_name'].'插件');
                $this->msg = '卸载成功';return true;
            }else{
                Db::rollback();
                $this->msg = '卸载失败';return false;
            }
        }catch (\Exception $e){
            Db::rollback();
            $this->msg = $e->getMessage();
        }


    }


    public function setting(){
        $data = $this->param;
        $plugin_name = $data['plugin_name'];
        unset($data['plugin_name']);
        $config = $this->getConf($plugin_name);
        foreach ($config as $key=>$v){
            if($v['type'] == 'checkbox'){
                $data[$key]  = isset($data[$key])?implode(",",$data[$key]):'';
            }

        }
        $setting = serialize($data);
        $re = Db::name("admin_plugin_setting")->where('plugin_name','=',$plugin_name)->update(['setting'=>$setting]);
        if($re){
            $this->msg = "修改成功";
            return true;
        }else{
            $this->msg="修改失败";
            return false;
        }
    }

    public function getSetting($name=''){
        $info = Db::name("admin_plugin_setting")->where('plugin_name','=',$name)->find();
        $setting = unserialize($info['setting']);
        $config = $this->getConf($name);
        foreach ($config as $key=>$v){
            if($v['type'] == 'checkbox' && is_string($setting[$key])){
                $setting[$key]  = strlen($setting[$key])>0?explode(",",$setting[$key]):[];
            }
            $setting[$key] = isset($setting[$key])?$setting[$key]:$v;
        }
        return $setting;
    }

    /**
     * 获取插件配置文件
     * @param string $name
     * @return mixed
     * @author yang
     * Date: 2022/8/3
     */
    public function getConf($name=''){
        $path = $this->path.DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR."config.php";
        $config = include $path;
        return $config;
    }

    /**
     *
     * 获取当前插件状态
     * @param string $curPlugin
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/6/5
     */
    public static function getPluginStatus($curPlugin = ''){

        $status = self::checkPluginStatus($curPlugin);
        if(!$status){
            return false;
        }
        return true;
    }


}