<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/3
 * Time: 8:19
 */

namespace app\plugin\model;


use think\Model;
use think\Db;

class Plugin extends Model
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__ADMIN_PLUGIN__';

    public static function getInfo($name = ''){
        return self::where('plugin_name',"=",$name)->find();
    }

    public static function getSetting($name=""){
        $setting = Db::name('admin_plugin_setting')->where('plugin_name',"=",$name)->find();
        $setting = unserialize($setting['setting']);
        return $setting;
    }
}