<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/10
 * Time: 9:28
 */

namespace app\common\utils\Redis;


use think\Exception;
use think\facade\Config;

class RedisService
{
    private static $redis;
    private  static $instance;
    private function __construct()
    {
        $config = Config::get("redis.");
        try{
            self::$redis = new \Redis();
            self::$redis->connect($config['host'],$config['port']);
            self::$redis->auth($config['password']);
        }catch (Exception $e){
            die("redis连接不成功,".$e->getMessage());
        }
    }

    public static function getInstance(){
        //在创建对象时创建redis,返回的实例是redis而不是RedisService对象
        if(empty(self::$instance)){
            self::$instance =  new self();
        }
        return self::$redis;
    }

}