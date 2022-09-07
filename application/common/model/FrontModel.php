<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/6
 * Time: 19:36
 */

namespace app\common\model;
use app\common\utils\Redis\RedisService;
use think\model;

class FrontModel extends model
{
//获取前台传来的数据
    protected   $param = array();

    //返回的信息
    protected $msg = '';

    protected static $redis ;

    public function __construct($data = [])
    {
        parent::__construct($data);
        self::$redis = RedisService::getInstance();
    }

    /**
     * 获取前台传来的数据的函数
     * @param $param
     * @author yang
     * Date: 2022/5/19
     */
    public  function setParam($param=array()){
        $this->param = $param;
    }

    /**
     * 函数需要输出
     * @return string
     * @author yang
     * Date: 2022/5/22
     */
    public function getMsg(){
        return $this->msg;
    }
}