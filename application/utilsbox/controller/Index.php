<?php
namespace app\utilsbox\controller;

use app\common\controller\FrontBase;

class Index extends FrontBase
{
 public function index(){ 
 return $this->fetch(); 
} 
}