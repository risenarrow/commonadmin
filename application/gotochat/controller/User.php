<?php
namespace app\gotochat\controller;

use app\common\utils\Yutils;
use app\gotochat\model\FriendsModel;
use app\gotochat\model\UserModel;
use think\App;
class User extends GotoChatBase
{

    public function __construct($msg=[],$ws,$frame)
    {
        parent::__construct(null,$msg,$ws,$frame);
    }

    public function index($msg,$ws,$frame){
        $user = new UserModel();
        return $this->fetch();
    }

    /**
     * 好友请求列表
     * @author yang
     * Date: 2022/8/13
     */
    public function findfriend($msg,$ws,$frame){

        if(!UserModel::checkLogin($msg['header']['user_id'],$msg['header']['authtoken'])){
            $ws->push($frame->fd,$this->echoJson("请先登录",1,0));
            return;
        }
        $user = new UserModel();
        $user->setParam($msg);
        $re = $user->findfriend();
        if($re !== false){
            $ws->push($frame->fd,$this->echoJson($user->getMsg(),0,0,$re,$msg['header']));  return ;
        }
        $ws->push($frame->fd,$this->echoJson($user->getMsg(),1,0,[],$msg['header']));  return ;
    }


    /**
     *
     * 查找用户
     * @param $msg
     * @param $ws
     * @param $frame
     * @author yang
     * Date: 2022/8/26
     */
    public function finduser($msg,$ws,$frame){
        $user = new UserModel();
        $user->setParam($msg);
        $re = $user->finduser();
        if($re !== false){
            $ws->push($frame->fd,$this->echoJson($user->getMsg(),0,0,$re,$msg['header']));  return ;
        }
        $ws->push($frame->fd,$this->echoJson($user->getMsg(),1,0,[],$msg['header']));  return ;
    }

    /**
     * 申请添加好友
     * @param $msg
     * @param $ws
     * @param $frame
     * @author yang
     * Date: 2022/8/26
     */
    public function reqfriend($msg,$ws,$frame){

        $set = new FriendsModel();
        $set->setParam($msg);
        $re = $set->reqfriend();

        if($re != false){
            $ws->push($frame->fd,$this->echoJson($set->getMsg(),0,0,$re));  return ;
        }
        $ws->push($frame->fd,$this->echoJson($set->getMsg(),1,0,[]));  return ;

    }

    /**
     * 请求列表
     * @param $msg
     * @param $ws
     * @param $frame
     * @author yang
     * Date: 2022/8/26
     */
    public function reqfriendlist($msg,$ws,$frame){
        $set = new FriendsModel($msg);
        $set->setParam($msg);

        $re = $set->getReqList();
        if($re){
            $ws->push($frame->fd,$this->echoJson($set->getMsg(),0,$re['total'],$re['list']));  return ;
        }
        $ws->push($frame->fd,$this->echoJson($set->getMsg(),1,0,[]));  return ;
    }



}