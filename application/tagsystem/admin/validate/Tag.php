<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/17
 * Time: 15:09
 */


namespace app\tagsystem\admin\validate;

use think\Validate;
class Tag extends Validate
{
    protected $rule = [
        'title'  =>  'require',
        'link'=>'require',
        'user_name'=>'require|checkUser'
    ];

    protected $message = [
        'title.require' => '标签名不能为空',
        'link.require' => '链接不能为空',
        'user_name.require'=>'用户不能为空'
    ];

    protected function sceneDel(){
        return $this->only(['user_name'])
            ->append('id', 'require|checkIsValid');
    }

    public function checkUser($value,$rules,$data){
        return true;
    }

    public function checkIsValid($value,$rules,$data){
        var_dump($data);die;
    }

}