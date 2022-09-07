<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/18
 * Time: 9:58
 */

namespace app\gotochat\controller;
use app\gotochat\model\FriendsModel;
use app\gotochat\model\GroupModel;
use think\App;
use think\Exception;

class Friends extends GotoChatBase
{
    public $remsg;
    public $ws;
    public $frame;
    private $set;
    public function __construct( $msg,$ws,$frame)
    {
        parent::__construct(null, $msg,$ws,$frame);
        $this->remsg = $msg;
        $this->ws = $ws;
        $this->frame = $frame;
        $this->set =  new FriendsModel( $this->remsg);
    }

    public function reqinfo(){

        $this->set->setParam( $this->remsg);
        if($re = $this->set->getInfo()){
            $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),0,0,$re));  return ;
        }
        $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),1,0));  return ;
    }

    /**
     * 处理添加好友请求
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/8/19\
     */
    public function addfriend(){
//        var_dump($this->sendChatMsg(5,2,0)) ;
//       return;
        $this->set->setParam( $this->remsg);
        if($re = $this->set->addfriend()){
            //发送消息
            //$res = $this->sendChatMsg($re['friend_id'],$re['user_id'],0,0);
            $res = $this->sendChatMsg(0,$re['user_id'],0,0);
            if($res === true){
                $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),0,0,$re));  return ;
            }
            else{
                $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),2,0));  return ;
            }

        }
        $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),1,0));  return ;
    }

    /**
     * 获取好友头像
     * @author yang
     * Date: 2022/8/22
     */
    public function getavatar(){
        $this->set->setParam( $this->remsg);
        if($re = $this->set->getavatar()){
            $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),0,0,$re));  return ;
        }
        $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),1,0));  return ;
    }

    /**
     * 发送消息
     * @author yang
     * Date: 2022/8/26
     */
    public function sendmsg(){
        $this->set->setParam( $this->remsg);
        if($re = $this->set->sendmsg()){
            //发送消息
            //$res = $this->sendChatMsg($re['user_id'],$re['friend_id'],$re['group_id'],$re['chat_type']);
            if($re['chat_type'] == 1){
                $group_users = $re['send_data']['group_users'];
                foreach($group_users as $k=>$v){
                    if($v['user_id'] != $re['user_id'])
                            $res = $this->sendChatMsg(0,$v['user_id'],0,0);
                }
            }else{
                $res = $this->sendChatMsg(0,$re['friend_id'],0,0);
            }
            //发送消息回调不需要发自己的头像和昵称
            $returndata = [
                'add_time'=>$re['send_data']['add_time'],
                'chat_type'=>$re['send_data']['chat_type'],
                'from_user_id'=>$re['send_data']['from_user_id'],
                'group_id'=>$re['send_data']['group_id'],
                'msg'=>$re['send_data']['msg'],
                'msg_type'=>$re['send_data']['msg_type'],
                'to_user_id'=>$re['send_data']['to_user_id'],
                'chat_id'=>$re['send_data']['chat_id'],
                'msg_id'=>$re['send_data']['msg_id'],
                'avatar'=>$re['send_data']['avatar'],
                'nickname'=>$re['send_data']['nickname'],
                'group_avatar'=>$re['send_data']['group_avatar'],
                'group_name'=>$re['send_data']['group_name']
            ];
            $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),0,0,$returndata));
            return ;
        }
        $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),1,0));  return ;
    }

    /**
     * 获取好友列表
     * @author yang
     * Date: 2022/8/26
     */
    public function getfriends(){
        $this->set->setParam( $this->remsg);
        if($re = $this->set->getfriends()){
            $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),0,0,$re));
            return ;
        }
        $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),1,0));  return ;
    }

    /**
     * 查找好友信息
     * @author yang
     * Date: 2022/8/26
     */
    public function searchfriend(){
        $this->set->setParam($this->remsg);
        $re = $this->set->searchfriend();

        if($re){
            $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),0,0,$re));  return ;
        }
        $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),1,0,[]));  return ;
    }

    /**
     * 设置朋友备注
     * @author yang
     * Date: 2022/8/26
     */
    public function setfriendremark(){
        $this->set->setParam($this->remsg);
        $re = $this->set->setfriendremark();
        if($re){
            $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),0,0));  return ;
        }
        $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),1,0,[]));  return ;
    }

    /**
     * 设置朋友权限
     * @author yang
     * Date: 2022/8/26
     */
    public function setfriendauth(){
        $this->set->setParam($this->remsg);
        $re = $this->set->setfriendauth();
        if($re){
            $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),0,0));  return ;
        }
        $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),1,0,[]));  return ;
    }


    /**
     * 获取消息
     * @author yang
     * Date: 2022/8/27
     */
    public function getmsg(){
        $this->set->setParam($this->remsg);
        $re = $this->set->getmsg();

            if($re){
                foreach($re as $k=>$v){
                    $v = substr($v,4,strlen($v)-4);
                    $arr = explode("_",$v);
                    $res =  $this->sendChatMsg(0,$arr[1],0,0);
                    if($res !== true){
                        return;
                    }
//                    $v = substr($v,4,strlen($v)-4);
//
//                   $arr = explode("_",$v);
//
//                   if($arr[0] == $this->remsg['header']['user_id']){
//                       $res = $this->sendChatMsg($arr[1],$arr[0],$arr[2],$arr[3]);
//                       if($res !== true){
//                           return;
//                       }
//                   }else{
//                       $res = $this->sendChatMsg($arr[0], $arr[1], $arr[2], $arr[3]);
//
//                       if($res !== true){
//                           return;
//                       }
//                   }

                }
            }

        $this->ws->push($this->frame->fd,$this->echoJson($this->set->getMsg(),1,0,[]));  return ;
    }

    /**
     * 发起群聊
     * @author yang
     * Date: 2022/9/6
     */
    public function creategroup(){
        $group = new GroupModel();
        $group->setParam($this->remsg);
        $re = $group->creategroup();
        if($re !== false){
            $this->ws->push($this->frame->fd,$this->echoJson($group->getMsg(),0,0,$re));return;
        }else{
            $this->ws->push($this->frame->fd,$this->echoJson($group->getMsg(),1,0,[]));return;
        }
    }



}