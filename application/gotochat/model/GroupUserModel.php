<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/22
 * Time: 12:12
 */

namespace app\gotochat\model;


use think\Exception;

class GroupUserModel extends GotoChatBaseModel
{
    protected  $pk = 'id';
    protected $table = '__GOTOCHAT_GROUP_USER__';

}