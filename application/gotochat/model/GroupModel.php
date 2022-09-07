<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/22
 * Time: 12:12
 */

namespace app\gotochat\model;


use app\common\utils\File;
use app\common\utils\Yutils;
use think\Exception;

class GroupModel extends GotoChatBaseModel
{
    protected  $pk = 'id';
    protected $table = '__GOTOCHAT_GROUP__';

    public function createGroup(){
        $data = $this->param;
        $body = $data['body'];
        $header = $data['header'];
        $user_id = $header['user_id'];
        $friend_ids = $body["friend_ids"];
        $friend_ids = explode(",",$friend_ids);
        if(!$friend_ids){
            return false;
        }
        $userinfo = UserModel::getUserinfoById($user_id);
        $friends = FriendsModel::where('friend_id','in',$friend_ids)->select()->toArray();
        if(count($friend_ids) != count($friends)){
            $this->msg = "非朋友关系不能加入群聊";
            return false;
        }
        //取出前9个群聊成员的username
        $group_name = $userinfo['nickname'].",";
        //取出前全部群聊成员的username
        $group_user_name = $userinfo['nickname']."|";
        $group_user_id = $userinfo['id']."|";
        //保存用户头像
        $avatars[] = $userinfo['avatar'];
        foreach($friends as $k=>$v){
            if($k < 9){
                $group_name .= $v['friend_remark'].",";
                $avatars[] = UserModel::where('id','=',$v['friend_id'])->value('avatar');
            }
            $group_user_name .= $v['friend_remark']."|";
            $group_user_id .= $v['friend_id']."|";
        }
        $group_name = rtrim($group_name,",");
        $group_user_name = rtrim($group_user_name,"|");
        $group_user_id = rtrim($group_user_id,"|");
        $creat_time = time();
        $status = 1;

        //插入数据
        $insert_data = [
            'create_user_id'=>$header['user_id'],
            'group_name'=>$group_name,
            'group_user_name'=>$group_user_name,
            'group_user_id'=>$group_user_id,
            'creat_time'=>$creat_time,
            'status'=>$status
        ];
        self::startTrans();
        try{
            //插入群聊表
            $group_id = self::insertGetId($insert_data);
            if($group_id){
                //插入群聊用户表
                $insert_data2 = [];
                $insert_data2[] = [
                    'user_id'=>$userinfo['id'],
                    'group_id'=>$group_id,
                    'nickname'=>$userinfo['nickname'],
                    'role'=>1
                ];
              foreach ($friends as $k=>$v){
                  $insert_data2[] = [
                      'user_id'=>$v['friend_id'],
                      'group_id'=>$group_id,
                      'nickname'=>$v['friend_nickname'],
                      'role'=>0
                  ];
              }
              $res = GroupUserModel::insertAll($insert_data2);
              if($res){
                  self::commit();
                  /**************更新头像*******************/
                  try{
                      $path = $this->createGroupAvatar($avatars,$group_id);
                      self::where('id','=',$group_id)->update(['group_avator'=>$path]);
                  }catch (Exception $e){
                  }
                  $insert_data['group_avator'] =$path;
                  $insert_data['group_id'] = $group_id;
                  $this->msg = "添加群聊成功";return $insert_data;
              }else{
                  self::rollback();
                  $this->msg = "添加群聊失败";return false;
              }
            }else{
                $this->msg = '添加群聊失败'; return false;
            }
        }catch (Exception $e){
            $this->msg = $e->getMessage();
            self::rollback();
            return false;
        }

    }


    public function createGroupAvatar($friends_avatar=[],$group_id=0){
        $rootpath = "./static/gotochat";
        //默认头像
        $default_avatar = $rootpath."/avatar.png";
        //头像背景图
        $avatar_back = $rootpath."/avatar_back.png";
        $newPath = preg_replace('/([^\/]+)\.png$/',md5($group_id."_".time()).".png",$avatar_back);
        //把背景复制到新目录
        File::copyFile($avatar_back,$newPath);

        try {
            foreach ($friends_avatar as $k => $v) {
                if ($k < 9) {
                    $avatar = $v ? $v : $default_avatar;

                    preg_match('/([^\/]+)\.png$/', $avatar, $arr);
                    $thumbPath = $rootpath . "/" . $arr[1] . "_thumb.png";
                    if (!file_exists($rootpath . "/" . $arr[1] . "_thumb.png")) {
                        $thumbPath = preg_replace('/([^\/]+)\.png$/', $arr[1] . "_thumb.png", $avatar);
                        $image_thumb = \think\Image::open($avatar);
                        // 按照原图的比例生成一个最大为42*42的缩略图并保存为thumb
                        $image_thumb->thumb(42, 42, \think\Image::THUMB_CENTER)->save($thumbPath);
                    }
                    //在新目录背景图下加水印
                    $image = \think\Image::open($newPath);
                    $image->water($thumbPath, $k + 1)->save($newPath);

                } else {
                    break;
                }
            }
        }catch (Exception $e){
            var_dump($e->getMessage());
        }
        $newPath = mb_substr($newPath,1,mb_strlen($newPath)-1);
        return $newPath;

    }

}