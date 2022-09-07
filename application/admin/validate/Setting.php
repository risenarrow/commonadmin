<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/17
 * Time: 15:09
 */


namespace app\admin\validate;

use think\Validate;

class Setting extends Validate
{
    protected $rule = [
        'name'  =>  'require|regex:^[a-zA-Z]+[a-zA-Z0-9_]*|unique:config',
        'title' => 'require',
        'type_id'=>'require',
        'form_type'=>'require'
    ];

    protected $message = [
        'name.unique' => '配置名称已存在',
        'name.require' => '配置名称不能为空',
        'name.regex' => '配置名称格式为英文，下划线 ，数字',
        'title.require'=>'配置标题不能为空',
        'type_id.require'=>'配置类型不能为空',
        'form_type.require'=>'字符类型不能为空'
    ];

    // edit 验证场景定义
    public function sceneEdit()
    {

        return $this->only(['name','title','type_id','form_type'])
            ->remove('name', 'unique');
    }

    public function setCheck($data){

    }
}