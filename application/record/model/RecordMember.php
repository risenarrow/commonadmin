<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/4
 * Time: 9:03
 */

namespace app\record\model;
use app\common\model\FrontModel;
use think\Db;
use think\Exception;


class RecordMember extends FrontModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__RECORD_MEMBRE__';

    public  function getToken(){
        $data = $this->param;
        $key = "xxxxxxxxxxxxxxxxxxxxxxx";  //这里是自定义的一个随机字串，应该写在config文件中的，解密时也会用
        $token = [
            "iss"=>"",  //签发者 可以为空
            "aud"=>"", //面象的用户，可以为空
            "iat" => time(), //签发时间
            "nbf" => time()+1, //在什么时候jwt开始生效  （这里表示生成100秒后才生效）
            "exp" => time()+2592000, //token 过期时间
            "data" => ['uid'=>1,'mobile'=>123123123] //记录的uid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
        ];
        $jwt = JWT::encode($token,$key,"HS256"); //根据参数生成了 token
        return json([
            "token"=>'bearer '.$jwt
        ]);
    }
}