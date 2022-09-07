<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/17
 * Time: 15:09
 */


namespace app\admin\validate;

use think\Validate;
class Module extends Validate
{
    protected $rule = [
        'module_name'  =>  'require|unique:module|regex:^[a-zA-Z]+[a-zA-Z0-9_]*',
        'module_title'=>'require',
        'author'=>'require'
    ];

    protected $message = [
        'module_name.unique' => '模块已存在',
        'module_name.require' => '模块标识不能为空',
        'module_name.regex'=>'模块名必须为英文字母、数字、_，且首位为英文字母',
        'module_title.require'=>'模块标题不能为空',
        'author.require'=>'作者不能为空'
    ];

    // 状态改变 验证场景定义
    public function sceneStatus()
    {

        return $this->only(['module_name'])
            ->remove('module_name', 'unique');
    }

}