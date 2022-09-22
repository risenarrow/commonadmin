<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/16
 * Time: 14:36
 */

namespace app\gotochat\model;
use app\common\model\FrontModel;
use app\common\utils\Yutils;
use app\gotochat\validate\User;
use think\Db;
use think\Exception;

class FriendsModel extends GotoChatBaseModel
{
    protected $pk = 'id';
    protected $table = "__GOTOCHAT_FRIENDS__";

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function getReqList(){
        $data = $this->param;
        $data = [
            'user_id'=>$data['header']['user_id'],
            'token'=>$data['header']['authtoken'],
            'limit'=>$data['body']['limit'],
            'page'=>$data['body']['page']
        ];

        //检查是否过期,在添加操作也会检查 5天过期

        $current_time = time();
        FriendReq::where([
            ['friend_id','=',$data['user_id']],
            ['addtime','<',$current_time-60*60*24*5],
            ['status','=',0]
        ])->update(['status'=>2]);



       $list = FriendReq::where('user_id','=',$data['user_id'])
            ->whereOr('friend_id','=',$data['user_id'])
           ->order('addtime', 'desc')
            ->paginate(['list_rows'=>$data['limit'],'page'=>$data['page']])->each(function($item,$key){
               $item->addtime = date('Y-m-d H:i:s',$item->addtime);
               $item->friend_nickname =  UserModel::where('id','=',$item->friend_id)->value('nickname');
               $item->user_nickname = UserModel::where('id','=',$item->user_id)->value('nickname');
           });
        $list = $list->toArray();


       return ['total'=>$list['total'],'list'=>$list['data']];
    }

    public function reqfriend(){

        $data = $this->param;

        $data = [
            'user_id'=>$data['header']['user_id'],
            'token'=>$data['header']['authtoken'],
            'remark'=>$data['body']['remark'],
            'req_remark'=>$data['body']['req_remark'],
            'friend_id'=>$data['body']['friend_id'],
            'friend_auth'=>$data['body']['friend_auth'],
            'comefrom'=>$data['body']['comefrom']
        ];
        if(!UserModel::checkLogin($data['user_id'],$data['token'])){
            $this->msg="请先登录";
            return false;
        }

        $validate =  new \app\gotochat\validate\Friends();
        if(!$validate->check($data)){
            $this->msg = $validate->getError();return false;
        }



        $firend = UserModel::getUserinfoById($data['friend_id']);
        $insert = [
            'user_id'=>$data['user_id'],
            'friend_id'=>$data['friend_id'],
            'addtime'=>time(),
            'remark'=>$data['remark'],
            'origin'=>$data['comefrom'],
            'req_remark'=>$data['req_remark'],
            'friend_auth'=>$data['friend_auth'],
            'friend_avatar'=>$firend['avatar']
        ];
        try{
            //如果已有请求
            $info = FriendReq::where([['user_id','=',$data['user_id']],['friend_id','=',$data['friend_id']]])->find();
            if(!empty($info) ){
                if( $info['status'] != 2){
                    $this->msg="已有申请，请勿重新申请";
                    return false;
                }else{
                    //更新请求
                    $insert['status'] = 0;
                    $re = FriendReq::where('id','=',$info['id'])->update($insert);
                }
            }else{
                $re = FriendReq::insert($insert);
            }
        }catch (Exception $e){
            $this->msg = $e->getMessage();
            return false;
        }

        if(!$re){
            $this->msg = "添加失败";
            return false;
        }
        $this->msg = "添加成功";
        return true;
    }

    public function getInfo(){
        $data = $this->param;
        $user_id = intval($data['header']['user_id']);
        $info = FriendReq::getInfoById($data['body']['reqid']);
        if(empty($info) ||($info['user_id'] != $user_id &&$info['friend_id'] != $user_id)){
            $this->msg = "请求不存在";
            return false;
        }
        $search_id = self::isMyReq($user_id,$info)?$info['friend_id']:$info['user_id'];
        $userinfo = UserModel::getUserinfoById($search_id);
        if(!$userinfo){
            $this->msg = "用户不存在";return false;
        }
        //检查是否过期,在添加操作也会检查 5天过期
        if($info['status'] == 0 && time()-$info['addtime'] > 60*60*24*5){
            FriendReq::where('id','=',$info['id'])->update(['status'=>2]);
            $info['status'] = 2;
        }

        $retrun = [
            'user_id'=>$info['user_id'],
            'friend_id'=>$info['friend_id'],
            'nickname'=>$userinfo['nickname'],
            'req_remark'=>$info['req_remark'],
            'region'=>$userinfo['region'],
            'qianming'=>$userinfo['qianming'],
            'sex'=>$userinfo['sex'],
            'comefrom'=>(self::isMyReq($user_id,$info)?"":"对方")."通过搜索".($info['origin'] == 'em'?"邮箱":"账号")."添加",
            'status'=>$info['status']
        ];
        return $retrun;
    }

    /**
     * 处理朋友申请操作
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/8/19
     */
    public function addfriend(){
        $data = $this->param;
        $body = $data['body'];
        $header = $data['header'];
        $friend_auth =  $body['friend_auth'];
        //获取请求信息
        $reqinfo = FriendReq::where('id','=',$body['reqid'])->find();
        if($reqinfo['status'] == 1 ){
            $this->msg = "你已添加朋友";return false;
        }
        if($reqinfo['status'] == 2){
            $this->msg = "此申请已过期";return false;
        }
        $addtime = time();
        $friend_info = UserModel::getUserinfoById($reqinfo['user_id']);
        $user_info = UserModel::getUserinfoById($header['user_id']);
        $insert1 = [
            'user_id'=>$header['user_id'],
            'friend_id'=>$friend_info['id'],
            'friend_nickname'=>$friend_info['nickname'],
            'addtime'=>$addtime,
            'friend_remark'=>$friend_info['nickname'],
            'origin'=>'对方通过'.($reqinfo['origin'] == 'em'?"邮箱":"账号")."添加",
            'first_letter'=>Yutils::getFirstLetter($friend_info['nickname']),
            'friend_auth'=>$friend_auth
        ];
        $insert2 = [
            'user_id'=>$friend_info['id'],
            'friend_id'=>$header['user_id'],
            'friend_nickname'=>$user_info['nickname'],
            'addtime'=>$addtime,
            'friend_remark'=>$reqinfo['remark'],
            'origin'=>'通过'.($reqinfo['origin'] == 'em'?"邮箱":"账号")."添加",
            'first_letter'=>Yutils::getFirstLetter($reqinfo['remark']?$reqinfo['remark']:$user_info['friend_nickname']),
            'friend_auth'=>$reqinfo['friend_auth']
        ];
        Db::startTrans();
        try{
            //更新请求
            $re = FriendReq::where('id','=',$reqinfo['id'])->update(['status'=>1]);
            if($re){
                $re = self::insert($insert1);
               if($re){
                   $re = self::insert($insert2);
                   if($re){
                       Db::commit();
                       //发送消息
                       $senddata = [
                           'from_user_id'=>$friend_info['id'],
                           'to_user_id'=>$header['user_id'],
                           'group_id'=>0,
                           'chat_type'=>0,
                           'msg'=>$reqinfo['req_remark'],
                           'msg_type'=>0,
                           'add_time'=>time(),
                           'avatar'=>$friend_info['avatar'],
                           'nickname'=>$friend_info['nickname'],
                       ];
                       //ChatModel::setChatMsg($friend_info['id'],$header['user_id'],0,0,$senddata);
                       ChatModel::setChatMsg(0,$header['user_id'],0,0,$senddata);
                       $this->msg = "添加成功";
                       return ['user_id'=>$header['user_id'],'friend_id'=>$friend_info['id']];
                   }
               }
            }
            Db::rollback();
            $this->msg = "处理失败，请稍后再试";
            return false;
        }catch (Exception $e){
            Db::rollback();
            $this->msg = $e->getMessage();
            return false;
        }

    }

    public function getavatar(){
        $data = $this->param;
        $body = $data['body'];
        $header = $data['header'];
        $arr = $body['ids'];

        $user_id =[];/*用户头像*/
        $group_id =[];/*聊天组头像*/
        foreach($arr as $k=>$v){
            if($v['chat_type'] ==1){
                $group_id[] = $v['id'];
            }else{
                $user_id[] = $v['id'];
            }
        }
        $users = [];$groups=[];
        if(!empty($user_id)){
            $users =  UserModel::where([['id','in',$user_id]])->field('id,avatar')->select()->toArray();
            foreach($users as $k=>$v){
                $users[$k]['chat_type'] = 0;
            }
        }
        if (!empty($groups)){
            $groups = GroupModel::where([['id','in',$group_id]])->select()->toArray();
            foreach($groups as $k=>$v){
                $groups[$k]['chat_type'] = 1;
            }
        }

        $arr = array_merge($users,$groups);
        $this->msg = "获取成功";
        return $arr;
    }

    /**
     * 发送消息
     * @author yang
     * Date: 2022/8/22
     */
    public   function  sendmsg(){
      $body = $this->param['body'];
      $header = $this->param['header'];

      //如果是普通聊天
      if(intval($body['chat_type']) === 0){
          //判断是否为朋友
          $friendship =self::where('user_id','=',$header['user_id'])
              ->where('friend_id','=',$body['to_user_id'])
              ->find();
          if(empty($friendship)){
              $this->msg = "对方不是你的好友";
              return false;
          }
          $friend_info = UserModel::getUserinfoById($body['to_user_id']);
          $userinfo = UserModel::getUserinfoById($header['user_id']);

          //对方朋友关系
          $tofriendship = self::where('user_id','=',$body['to_user_id'])
              ->where('friend_id','=',$header['user_id'])
              ->find();
          //发送消息
          $senddata = [
              'from_user_id'=>$header['user_id'],
              'to_user_id'=>$body['to_user_id'],
              'group_id'=>$body['group_id'],
              'chat_type'=>$body['chat_type'],
              'msg'=>$body['msg'],
              'msg_id'=>$body['msg_id'],
              'msg_type'=>$body['msg_type'],
              'add_time'=>time(),
              'avatar'=>$userinfo['avatar'],     //发送消息者头像
              'nickname'=>$tofriendship['friend_remark'],
              'group_avatar'=>'',
              'group_name'=>''
          ];
//          $senddata['chat_id'] = ChatModel::setChatMsg($header['user_id'],$body['to_user_id'],$body['group_id'],$body['chat_type'],$senddata);
          $senddata['chat_id'] = ChatModel::setChatMsg(0,$body['to_user_id'],0,0,$senddata);
          //前端由于不明原因，所以在这里加入朋友的nickname和avatar
          $senddata['avatar'] =$friend_info['avatar'];
          $senddata['nickname'] =$friendship['friend_remark'];
          return ['user_id'=>$header['user_id'],'friend_id'=>$friend_info['id'],'group_id'=>$body['group_id'],'chat_type'=>$body['chat_type'],'send_data'=>$senddata];
      }
      elseif(intval($body['chat_type']) === 1){//如果是群聊
          //判断是否属于该群
          $group_user = GroupUserModel::where([
              ['user_id','=',$header['user_id']],
              ['group_id','=',$body['group_id']]
          ])->find();
          if($group_user){
              //获取群组信息
              $group = GroupModel::where('id','=',$body['group_id'])->find();
              //获取该群全部用户
              $group_users = GroupUserModel::where('group_id','=',$body['group_id'])->select()->toArray();
              $group_users = array_combine(array_column($group_users,'user_id'),$group_users);
              $userinfo = UserModel::getUserinfoById($header['user_id']);
              //发送消息
              $senddata = [
                  'from_user_id'=>$header['user_id'],
                  'to_user_id'=>$body['to_user_id'],
                  'group_id'=>$body['group_id'],
                  'chat_type'=>$body['chat_type'],
                  'msg'=>$body['msg'],
                  'msg_id'=>$body['msg_id'],
                  'msg_type'=>$body['msg_type'],
                  'add_time'=>time(),
                  'avatar'=>$userinfo['avatar'],
                  'nickname'=>$group_user['nickname'],
                  'group_avatar'=>$group['group_avator'],
                  'group_name'=>$group['group_name']
              ];
              $senddata['chat_id'] = ChatModel::storeGroupChatMsg($header['user_id'],0,$body['group_id'],$body['chat_type'],$senddata);
              foreach ($group_users as $key=>$val){
                  if($val['user_id'] != $header['user_id']){
                      //把接收人存储
                      $senddata['to_user_id'] = $val['user_id'];
                      ChatModel::setChatMsg(0,$val['user_id'],0,0,$senddata);
                  }
              }
              //前端由于不明原因，所以在这里加入群组的nickname和avatar
              $senddata['group_avatar'] = $group['group_avator'];
              $senddata['group_name'] = $group['group_name'];
              $senddata['group_users'] = $group_users;
              return ['user_id'=>$header['user_id'],'friend_id'=>0,'group_id'=>$body['group_id'],'chat_type'=>$body['chat_type'],'send_data'=>$senddata];
          }else{
              $this->msg="你不属于该群聊";
              return false;
          }
      }
    }


    /**获取朋友列表
     * @author yang
     * Date: 2022/8/25
     */
    public function getfriends(){
        $data= $this->param;
        $body = $data['body'];
        $header = $data['header'];
        $user_id = $header['user_id'];
        return self::where('user_id','=',$user_id)->select()->toArray();
    }


    /**
     * 查找好又信息
     * @author yang
     * Date: 2022/8/26
     */
    public function searchfriend(){
        $data= $this->param;
        $body = $data['body'];
        $header = $data['header'];
        $user_id = $header['user_id'];

        $friend_info = UserModel::getUserinfoById($body['friend_id']);

        $re = self::where([
            ['friend_id','=',$body['friend_id']],
            ['user_id','=',$header['user_id']]
        ])->find();
        if($re){
            $re = $re->toArray();
            $re['qianming'] = $friend_info['qianming'];
        }

        return $re;
    }

    /**
     * 设置朋友备注
     * @author yang
     * Date: 2022/8/26
     */
    public function setfriendremark(){
        $data= $this->param;
        $body = $data['body'];
        $header = $data['header'];
        $user_id = $header['user_id'];
        $friend_ship = self::getFriendInfoById($body['friend_id'],$user_id);
        $friend_info = UserModel::getUserinfoById($body['friend_id']);
        if(!$friend_ship){
            $this->msg = "好友不存在";return false;
        }
        $update = [
            'friend_remark'=>$body['friend_remark']?$body['friend_remark']:$friend_info['nickname'],
            'friend_remark_desc'=>$body['friend_remark_desc']
        ];
       $re =  self::where([
            ['user_id','=',$header['user_id']],
            ['friend_id','=',$body['friend_id']]
        ])->update($update);

        if($re){
            $this->msg = "";
            return true;
        }
        $this->msg = "修改失败";
        return false;
    }

    public function setfriendauth(){
        $data= $this->param;
        $body = $data['body'];
        $header = $data['header'];
        $user_id = $header['user_id'];
        $friend_ship = self::getFriendInfoById($body['friend_id'],$user_id);
        if(!$friend_ship){
            $this->msg = "好友不存在";return false;
        }
        $update = [
            'friend_auth'=>$body['friend_auth']?$body['friend_auth']:1
        ];
        $re =  self::where([
            ['user_id','=',$header['user_id']],
            ['friend_id','=',$body['friend_id']]
        ])->update($update);

        if($re){
            $this->msg = "修改成功";
            return true;
        }
        $this->msg = "修改失败";
        return false;
    }

    public function getmsg(){
        $data= $this->param;
        $body = $data['body'];
        $header = $data['header'];
        $user_id = $header['user_id'];
        $keys = ChatModel::getkeys('getmsg',$user_id);

        return $keys;
    }


    public static function isMyReq($user_id,$info=[]){
        return $user_id == $info['user_id']?true:false;
    }
    public static function getFriendInfoById($friend_id=0,$user_id=0){
        return self::where([['user_id','=',$user_id],['friend_id','=',$friend_id]])->find();
    }
}