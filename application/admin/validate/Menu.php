<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/17
 * Time: 15:09
 */


namespace app\admin\validate;

use think\Validate;

class Menu extends Validate
{
    protected $rule = [
        'name'  =>  'require|unique:menu',
        'm'  =>  'require|regex:^[a-zA-Z]+[a-zA-Z0-9_]*',
        'c'  =>  'require|regex:^[a-zA-Z]+[a-zA-Z0-9_]*',
        'a'  =>  'require|regex:^[a-zA-Z]+[a-zA-Z0-9_]*',
    ];

    protected $message = [
        'name.unique' => '菜单已存在',
        'name.require' => '菜单名称不能为空',
        'm.regex'=>'模块名必须为英文字母、数字、_，且首位为英文字母',
        'c.regex'=>'控制器名必须为英文字母、数字、_，且首位为英文字母',
        'a.regex'=>'操作名必须为英文字母、数字、_，且首位为英文字母'
    ];

    // edit 验证场景定义
    public function sceneEdit()
    {
        return $this->only(['name','m','c','a'])
            ->remove('name', 'unique');
    }
}