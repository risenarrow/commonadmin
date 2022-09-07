<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/19
 * Time: 9:00
 */

namespace app\gotochat\model;


use app\common\utils\Yutils;

class ChatModel extends GotoChatBaseModel
{
    protected $pk = 'id';
    protected $table ='__GOTOCHAT_CHAT__';
    const machineId = 1;

    /**
     * 把聊天记录存储在redis,在控制器中读取 ，  copy缓存是用来插入到数据库的聊天记录中
     * 发送格式 $senddata = [
    'from_user_id'=>$friend_info['id'],
    'to_user_id'=>$header['user_id'],
    'group_id'=>0,
    'chat_type'=>0,
    'msg'=>$reqinfo['req_remark'],
    'msg_type'=>0,
    'add_time'=>time(),
    'avatar'=>$friend_info['avatar']
    ];
     * @param $from_user_id
     * @param $to_user_id       chat_type 为0时 $to_user_id 是 是用户id 为1时 group_id
     * @param $chat_type
     * @param array $data
     * @author yang
     * Date: 2022/8/19
     */
    public static function setChatMsg($from_user_id,$to_user_id,$group_id,$chat_type,$data=[]){
        if($chat_type ==1){
            $json = json_encode($data);
            $keyName = 'rec:' . self::getRecKeyName($from_user_id, $to_user_id,$group_id,$chat_type);
            $res =  self::$redis -> lPush($keyName, $json);        /*原件，发送完就得删除*/
            return $data['chat_id'];
        }else{
            $data['chat_id'] =  strval(Yutils::createUniqueId(self::machineId))  ;
            $json = json_encode($data);
            $keyName = 'rec:' . self::getRecKeyName($from_user_id, $to_user_id,$group_id,$chat_type);
            $keyNamecopy = "storage_mysql";
            $res1 = self::$redis -> lPush($keyNamecopy, $json);   /*副本，用来加入数据库*/
            $res =  self::$redis -> lPush($keyName, $json);        /*原件，发送完就得删除*/
            return $data['chat_id'];
        }

    }

    public static function storeGroupChatMsg($from_user_id,$to_user_id,$group_id,$chat_type,$data=[]){
        //获取chat_id;
        $data['chat_id'] =  strval(Yutils::createUniqueId(self::machineId))  ;
        $keyNamecopy = "storage_mysql";
        $res1 = self::$redis -> lPush($keyNamecopy, json_encode($data));   /*副本，用来加入数据库*/
        return $data['chat_id'];
    }

    /**
     * 获取聊天记录
     * @param $from
     * @param $to
     * @param $type
     * @param $num
     * @return array
     * @author yang
     * Date: 2022/8/19
     */
    public static function getChatMsg($from,$to,$group_id,$type,$num){
        $keyName = 'rec:' . self::getRecKeyName($from, $to,$group_id,$type);
        return self::$redis->lRange($keyName,0,$num);
    }



    public static function removeChatMsg($from,$to,$group_id,$type,$num){
        $keyName = 'rec:' . self::getRecKeyName($from, $to,$group_id,$type);
        $res = self::$redis->lTrim($keyName,$num,-1);
        return $res;
    }

    public static function removeSingleChatMsg($from,$to,$group_id,$type,$index){
        $keyName = 'rec:' . self::getRecKeyName($from, $to,$group_id,$type);
//        LSET list1 3 del
//        LREM list1 1 del
        self::$redis->lSet($keyName,$index,'del');
        $res = self::$redis->lRem($keyName,'del',1);
        return $res;
    }

    public static  function getRecKeyName($from, $to,$group_id,$chat_type){
        return ($from > $to) ? $to . '_' . $from."_".$group_id."_".$chat_type : $from . '_' . $to."_".$group_id."_".$chat_type;
    }

    public static function getkeys($pattern,$user_id=0){
        $str = "";
        if(!$pattern){
            $str = "*";
            return self::$redis->keys($str);
        }else if($pattern=="getmsg"){
//            $str = "rec:*_".$user_id."_*_"."[0-9]";
//            $arr = self::$redis->keys($str);
//            $str = "rec:".$user_id."_*_*_"."[0-9]";
//            $arr2 =  self::$redis->keys($str);
//            return array_merge($arr,$arr2);
            $str = "rec:"."0_".$user_id."_0_0";
            return self::$redis->keys($str);
        }
        return self::$redis->keys($pattern);
    }

}