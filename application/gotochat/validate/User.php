<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/14
 * Time: 14:17
 */

namespace app\gotochat\validate;

use app\common\utils\Redis\RedisService;
use think\validate;

class User extends validate
{
    protected $rule = [
        'username'  =>  'require|regex:^[a-zA-Z0-9]+[a-zA-Z0-9_@\.]*|unique:gotochat_user',
        'password'=>'require|min:6',
        'repassword'=>'require|confirm:password',
        'emailcode'=>'require|checkEmailcode',
        'city_id'=>'require',
        'province_id'=>'require',
        'nickname'=>"regex:^[A-Za-z0-9\u{4e00}-\u{9fa5}\._@%&\*\(\)]{1,20}$"
    ];

    protected $message = [
        'username.require' => '账号不能为空',
        'username.regex'=>'账号格式不正确',
        'password.require' => '密码不能为空',
        'repassword.confirm'=>'重新输入密码与原密码不一致',
        'repassword.require'=>'重新输入密码不能为空',
        'emailcode.require'=>'邮箱验证码不能为空',
        'username.email'=>'邮箱格式不正确',
        'password.min'=>'密码长度不能少于6位',
        'email.unique'=>'用户已存在',
        'province_id.require'=>'省份不存在',
        'city_id.require'=>'城市不存在',
        'nickname.regex'=>'昵称格式应为数字、字母、汉字和符号._@%&*()，长度不能超过20，'
    ];

    public function sceneLogin(){
        return $this->only(['username','password'])
            ->remove('username', 'unique');
    }
    public function sceneRegister(){
        return $this->append('username','email')
            ->remove('username','regex')
            ->append('email','unique:gotochat_user');
    }


    protected function checkEmailcode($value,$rules,$data){
        $redis = RedisService::getInstance();
        $emailcode = $redis->get("emailcode:".$data['username']);
        if(!$emailcode){
            return "邮件验证码已过期";
        }
        if($value != $emailcode){
            return "邮件验证码不正确";
        }
        return true;
    }



}