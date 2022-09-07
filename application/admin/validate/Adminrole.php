<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/17
 * Time: 15:09
 */


namespace app\admin\validate;

use think\Validate;
class Adminrole extends Validate
{
    protected $rule = [
        'role_name'  =>  'require|unique:admin_role',
    ];

    protected $message = [
        'role_name.unique' => '角色已存在',
        'role_name.require' => '角色不能为空',
    ];

    // edit 验证场景定义
    public function sceneEdit()
    {

        return $this->only(['role_name'])
            ->remove('admin_name', 'unique');
    }
}