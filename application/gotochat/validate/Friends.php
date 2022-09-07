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

class Friends extends validate
{
    protected $rule = [
        'friend_id'=>'require|regex:^[0-9]$|checkIsFriend',
        'remark'=>'require',
        'req_remark'=>'require',
        'friend_auth'=>'require|regex:^[1-2]$',
        'comefrom'=>'require|regex:^[(em)(zh)]+$',
    ];

    protected $message = [
        'friend_id.regex'=>'朋友不存在',
        'friend_id.require' => '朋友不存在',
        'remark.require'=>'备注不能为空',
        'req_remark.require'=>'申请备注不能为空',
        'friend_auth.require'=>'朋友权限不能为空',
        'friend_auth.regex'=>'朋友权限不能为空',
        'comefrom.require'=>'来源不能为空'
    ];



    protected function checkIsFriend($value,$rules,$data){
        $re = \app\gotochat\model\FriendsModel::getFriendInfoById($value,$data['user_id']);
        if(empty($re)){
            return true;
        }
        return '他已经是你的朋友了';
    }



}