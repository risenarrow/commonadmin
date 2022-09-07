<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/17
 * Time: 15:09
 */


namespace app\admin\validate;

use think\facade\Log;
use think\Validate;
class User extends Validate
{
    protected $rule = [
        'admin_name'  =>  'require|regex:^[a-zA-Z]+[a-zA-Z0-9_]*|unique:admin',
        'password'=>'require',
        'repassword'=>'require|confirm:password',
        'role_id' => 'require|regex:^[0-9]+|checkRoleId',
    ];

    protected $message = [
        'admin_name.unique' => '管理员账号已存在',
        'admin_name.require' => '管理员账号不能为空',
        'admin_name.regex' => '配置名称格式为英文，下划线 ，数字',
        'role_id.require'=>'角色不能为空',
        'password.require'=>'密码不能为空',
        'repassword.require'=>'请重复输入密码',
        'repassword.confirm'=>'两次密码输入不匹配',
        'verifycode.require'=>'验证码不能为空',
    ];


    public function sceneLogin(){
        return $this->only(['admin_name,password','verifycode'])
            ->remove('admin_name', 'unique')
            ->append('verifycode','require|checkCaptcha');
    }

    // edit 验证场景定义
    public function sceneEdit()
    {

        return $this->only(['admin_name','password','repassword','role_id'])
            ->remove('admin_name', 'unique')
            ->append('role_id', 'checkIsAdmin');
    }

    protected function checkRoleId($value,$rules,$data){
        $re = db('admin_role')->where('id','=',$value)->find();
        if($re)
            return true;
        return '角色不存在';
    }

    protected function checkIsAdmin($value,$rules,$data){
        if($value === 1){
            return '超级管理员不可以修改';
        }
        return true;
    }

    protected function checkCaptcha($value,$rules,$data){

        if(!captcha_check($value)){
            return '验证码不正确';
        }
        return true;
    }


}