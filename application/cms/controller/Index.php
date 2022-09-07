<?php
namespace app\cms\controller;

use app\common\controller\FrontBase;
use Firebase\JWT\JWT;


class Index extends FrontBase
{
 public function index(){

     return $this->fetch();
    }
}