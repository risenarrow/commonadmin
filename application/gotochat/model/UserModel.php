<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/10
 * Time: 14:26
 */

namespace app\gotochat\model;


use app\common\model\FrontModel;
use app\common\utils\Redis\RedisService;
use app\common\utils\Yutils;
use app\gotochat\validate\User as UserValidate;
use think\Exception;
use think\facade\Config;
use think\Validate;


class UserModel extends FrontModel
{
    protected $pk = 'id';
    protected $table="__GOTOCHAT_USER__";
    public static $redis;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        self::$redis = RedisService::getInstance();

    }

    /**
     * 检查登录
     * @param array $data
     * @return array|bool|\PDOStatement|string|\think\Model|null
     * @author yang
     * Date: 2022/8/10
     */
    public static function checkLogin($user_id="",$token=""){
        self::$redis = RedisService::getInstance();
        $user_id = intval($user_id);
        //检查token是否存在
        $save_token = self::$redis->get('user_id:'.$user_id);
        //存在且和传过来的token相等
        if($save_token && $save_token == $token){
            $re = self::$redis->set('user_id:'.$user_id,$save_token,1800);

            return true;
        }else{
            return false;
        }
    }


    /**
     * 前台登录操作
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author yang
     * Date: 2022/8/11
     */
    public function login(){
        $data = $this->param;
        $data = [
            'user_id'=>isset($data['header']['userid'])?$data['header']['userid']:0,
            'authtoken'=>isset($data['header']['authtoken'])?$data['header']['authtoken']:"",
            'username'=>$data['data']['username'],
            'password'=>$data['data']['password']
        ];
        //验证登录
        if(self::checkLogin($data['user_id'],$data['authtoken'])){
            $this->msg = "你已登录，请先退出后操作";
            return false;
        }

        //验证用户
        $validate = new UserValidate();
        if(!$validate->scene("Login")->check($data)){
            $this->msg = $validate->getError();return false;
        }
        $userinfo = self::getUserinfoByUsername($data['username']);
        if(empty($userinfo)){
            $userinfo = self::getUserinfoByEmail($data['username']);
        }
        if(empty($userinfo)){
            $this->msg = "用户不存在";
            return false;
        }
        if($userinfo['password']!=Yutils::encrypt($data['password'],$userinfo['salt'])){
            $this->msg = "账号或密码错误";return false;
        }
        if($userinfo['status'] ===0){
            $this->msg = "用户已禁用";return false;
        }
        //登录后操作
        $data =  $this->afterLogin($userinfo);
        $this->msg = "登录成功";
        return $data;
    }


    /**
     * 前台注册操作
     * @author yang
     * Date: 2022/8/11
     */
    public function register(){
        $data = $this->param;
        $data = [
            'user_id'=>isset($data['header']['userid'])?$data['header']['userid']:0,
            'authtoken'=>isset($data['header']['authtoken'])?$data['header']['authtoken']:"",
            'username'=>$data['data']['email'],
            'password'=>$data['data']['password'],
            'repassword'=>$data['data']['repassword'],
            'emailcode'=>$data['data']['emailcode'],
            'email'=>$data['data']['email'],
            'province_id'=>$data['data']['province_id'],
            'city_id'=>$data['data']['city_id'],
            'sex'=>intval($data['data']['sex']),
            'nickname'=>isset($data['data']['nickname']) && $data['data']['nickname']?$data['data']['nickname']:"用户_".substr(time(),5,strlen(time())-5).rand(1111,9999)
        ];

        //验证登录
        if(self::checkLogin($data['user_id'],$data['authtoken'])){
            $this->msg="你已登录，请先退出后操作";
            return false;
        }
        //验证用户
        $validate = new UserValidate();
        if(!$validate->scene("Register")->check($data)){
            $this->msg = $validate->getError();return false;
        }

        $province_name = Area::where('areaId','=',$data['province_id'])->value('areaName');
        $city_name = Area::where('areaId','=',$data['city_id'])->value('areaName');
        $salt = Yutils::getSalt();
        $password = Yutils::encrypt($data['password'],$salt);
        $register_time = time();
        $region = $province_name.",".$city_name;
        try{
            //验证通过写入数据库
            $insert = [
                'username'=>"",
                'nickname'=>$data['nickname'],
                'password'=>$password,
                'salt'=>$salt,
                'email'=>$data['username'],
                'register_time'=>$register_time,
                'status'=>1,
                'province_id'=>$data['province_id'],
                'city_id'=>$data['city_id'],
                'region'=>$region,
                'sex'=>$data['sex'],
                'first_letter'=>Yutils::getFirstLetter($data['nickname'])
            ];
            $user_id = self::insertGetId($insert);
            if($user_id){
                self::where("id",'=',$user_id)->update(['username'=>$user_id.substr($register_time,5,strlen($register_time)-5)]);
                $user_info = self::where('id','=',$user_id)->find();
               if( $re = $this->afterLogin($user_info)){
                   $this->msg = "注册成功";
                   self::$redis->del("emailcode:".$user_info['email']);
                   return $re;
               }
            }
            $this->msg = "注册失败";
            return false;
        }catch (Exception $e){
            $this->msg = $e->getMessage();
            return false;
        }

    }

    /**
     * 发送邮箱验证码
     * @author yang
     * Date: 2022/8/11
     */
    public function sendcode(){
        $data = $this->param;
        $smtp = Yutils::plugin("smtp");
        $email = $data['email'];
        $rule = ['email'=>'email|require'];
        $msg = ['email.email'=>'邮箱不正确','email.require'=>'请填写邮箱'];
        $validate = \think\facade\Validate::make($rule,$msg);
        if(!$validate->check(['email'=>$email])){
            $this->msg=$validate->getError();
            return false;
        }
        if(self::$redis->exists('emailcode:'.$email)){
            $this->msg = "请倒计时结束后发送";
            return false;
        }
        $code = rand(111111,999999);
        //获取消息模板
        $messageTpl = Config::get("messageTpl");
       $re =  $smtp->sendmail($email,"GotoChat验证码",str_replace("{{content}}",$code,$messageTpl['register']));
       if($re){
           self::$redis->set("emailcode:".$email,$code,120);
           $this->msg = "发送成功";
           return true;
       }
       $this->msg = "发送失败";
       return false;
    }

    /**
     * 查找朋友
     * @author yang
     * Date: 2022/8/13
     */
    public function findfriend(){
        $data = $this->param;
        $body = $data['body'];
        if(!isset($body['username']) || empty($body['username'])){
            $this->msg = "用户不存在";
            return false;
        }
        //根据账号或邮箱查找
        $info = self::getUserinfoByUserEm($body['username'])->toArray();
        if(empty($info) || $info['status'] != 1){
            $this->msg = "用户不存在";
            return false;
        }
        return [
            'user_id'=>$info['id'],
            'comefrom'=>$info['username'] == $body['username']?'zh':'em'
        ];
    }

    /**
     * 查找朋友
     * @author yang
     * Date: 2022/8/13
     */
    public function finduser(){
        $data = $this->param;
        $header = $data['header'];
        $body = $data['body'];
        if(!self::checkLogin($header['user_id'],$header['authtoken'])){
            $this->msg = "请先登录";
            return false;
        }
        if(!isset($body['user_id']) || empty($body['user_id'])){
            $this->msg = "用户不存在";
            return false;
        }
        //不是好友
        $isfriend = 0;
        //根据账号或邮箱查找
        $info = self::getUserinfoById($body['user_id']);
        //查找好友关系
        $friendinfo =  FriendsModel::getFriendInfoById($body['user_id'],$header['user_id']);
        if($friendinfo){
            //是好友
            $isfriend = 1;
        }

        //显示数据
        $redata = [
            'nickname'=>$info['nickname'],
            'username'=>$info['username'],
            'region'=>$info['region'],
            'isfriend'=>$isfriend,
            'qianming'=>$info['qianming'],
            'sex'=>$info['sex'],
            'friend_id'=>$info['id'],
            'friend_remark'=>''
        ];
        if($isfriend){
            $redata['friend_remark'] = $friendinfo['friend_remark'];
        }

        if(empty($info) || $info['status'] != 1){
            $this->msg = "用户不存在";
            return false;
        }
        return $redata;
    }


    /**
     * 用户成功登录后一系列操作
     * @param array $userinfo
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author yang
     * Date: 2022/8/11
     */
    private  function afterLogin($userinfo = []){
        //更新登录时间
        $userid = $userinfo['id'];
        self::where([['id','=',$userid]])->update(['last_login_time'=>time()]);
        //账号密码正确，返回token , token格式 md5(user_id + 时间戳 + 随机6位数字)
        $token = md5($userid.time().Yutils::getRandomString(6));
        self::$redis->set('user_id:'.$userid,$token,1800);
        return ['user_id'=>$userid,'token'=>$token];
    }

    public static function getUserinfoById($user_id=0){
        return self::where([["id","=",$user_id]])->find();
    }
    public static function getUserinfoByUsername($username=""){
        return self::where([["username","=",$username]])->find();
    }
    public static function getUserinfoByEmail($email=""){
        return self::where([["email","=",$email]])->find();
    }
    public static function getUserinfoByUserEm($username=""){
        return self::where("username","=",$username)
            ->whereOr('email',"=",$username)
            ->find();
    }

}