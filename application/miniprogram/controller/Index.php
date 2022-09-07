<?php
namespace app\miniprogram\controller;

use app\common\controller\FrontBase;
use app\common\utils\Redis\RedisService;

class Index extends FrontBase
{
 public function index(){

     return $this->fetch();
    }
}