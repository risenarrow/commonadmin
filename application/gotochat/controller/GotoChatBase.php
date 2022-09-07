<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/10
 * Time: 14:24
 */

namespace app\gotochat\controller;


use app\common\controller\FrontBase;
use app\gotochat\model\ChatModel;
use app\gotochat\model\UserModel;
use think\App;
use think\Exception;

class GotoChatBase extends FrontBase
{
    public $remsg;
    public $ws;
    public $frame;
    public function __construct(App $app = null,$msg=[],$ws=null,$frame=null)
    {
        parent::__construct($app);
        $this->remsg = $msg;
        $this->ws = $ws;
        $this->frame = $frame;
        if(!UserModel::checkLogin($msg['header']['user_id'],$msg['header']['authtoken'])){
            $ws->push($frame->fd,$this->echoJson("请先登录",1001,0));
            return;
        }else{
//        var_dump(self::$redis->get("user:fd:{$this->remsg['header']['user_id']}"));
//        var_dump($this->frame->fd);return;
            //关联用户的fd

            self::$redis->set("user:fd:{$msg['header']['user_id']}",$frame->fd,1800);
        }
    }

    public function echoJson($msg = '', $code = 0, $count = 0, $data = [], $header = [])
    {
        $result=  [
            'header'=>array_merge($this->remsg['header'],$header),
            'body'=>[]
        ];
        $result['body'] = [
            'code'=>$code,
            'count'=>$count,
            'data'=>$data,
            'msg'=>$msg
        ];
        return json_encode($result);
    }

//  $senddata = [
//              'from_user_id'=>$header['user_id'],
//              'to_user_id'=>$body['to_user_id'],
//              'group_id'=>$body['group_id'],
//              'chat_type'=>$body['chat_type'],
//              'msg'=>$body['msg'],
//              'msg_type'=>$body['msg_type'],
//              'add_time'=>time(),
//              'avatar'=>$friend_info['avatar'],
//              'nickname'=>$friend_info['nickname'],
//          ];
    public function sendChatMsg($from,$to,$group_id,$type,$num=15,$sendData=[]){

        if(empty($sendData)){
            //消息类型为普通聊天
            if($type == '0'){

                $sendData = ChatModel::getChatMsg($from,$to,$group_id,$type,$num);        //获取用户的消息列表

                $fd = $this->getFd($to);                     //获取用户对应的fd

                if($fd === 0){
                    return -1;
                }


                foreach ($sendData as $key=>$val){
                    /*未改keyname规则前*/
//                    $dataarr = json_decode($val,true);
//                    if($from == $dataarr['from_user_id'] && $to == $dataarr['to_user_id']){
//                        $dataarr['from_user_id'] = $from;
//                        $dataarr['to_user_id']= $to;
//
//                        $re = $this->ws->push($fd,$this->echoJson("",0,0,$dataarr,['pathinfo'=>'pullMsg']));   //发送消息给用户
//                        if($re){
//                             ChatModel::removeSingleChatMsg($from,$to,$group_id,$type,$key);
//                        }else{
//                            return -2;
//                        }
//                    }
                    /*改了keyname规则后*/
                    $dataarr = json_decode($val,true);
                    if($to == $dataarr['to_user_id']){
                        $re = $this->ws->push($fd,$this->echoJson("",0,0,$dataarr,['pathinfo'=>'pullMsg']));   //发送消息给用户
                        if($re){
                            ChatModel::removeSingleChatMsg($from,$to,$group_id,$type,$key);
                        }else{
                            return -2;
                        }
                    }

                }
                return true;
            }
        }
    }


    public function getFd($user_id=0){
        $re = self::$redis->get("user:fd:".$user_id);
        if($this->ws->exist($re)){
            return $re;
        }
        return 0;
    }

}