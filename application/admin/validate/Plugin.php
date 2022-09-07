<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/17
 * Time: 15:09
 */


namespace app\admin\validate;

use think\Validate;
class Plugin extends Validate
{
    protected $rule = [
        'plugin_name'  =>  'require|unique:admin_plugin|regex:^[a-zA-Z]+[a-zA-Z0-9_]*',
        'plugin_title'=>'require'
    ];

    protected $message = [
        'plugin_name.unique' => '插件已存在',
        'plugin_name.require' => '插件标识不能为空',
        'plugin_name.regex'=>'插件标识必须为英文字母、数字、_，且首位为英文字母',
        'plugin_title.require'=>'插件标题不能为空'
    ];

    // 状态改变 验证场景定义
    public function sceneStatus()
    {

        return $this->only(['plugin_name'])
            ->remove('plugin_name', 'unique');
    }

}