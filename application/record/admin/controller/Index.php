<?php
namespace app\record\admin\controller;

use app\admin\controller\AdminBase;

class Index extends AdminBase
{
 public function index(){ 
 return $this->fetch(); 
}
}