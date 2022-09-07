<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/17
 * Time: 15:05
 */

namespace app\gotochat\model;


use app\common\model\FrontModel;

class FriendReq extends FrontModel
{
    protected $pk = 'id';
    protected $table = "__GOTOCHAT_FRI_REQ__";
    public static  function getInfoById($id=0){
        return self::where('id','=',$id)->find()->toArray();
    }

}