<?php
namespace app\utilsbox\admin\controller;

use app\admin\controller\AdminBase;
use app\common\utils\Yutils;
use think\facade\Env;
class Wangpan extends AdminBase
{
     public function index(){
         return $this->fetch();
     }


     public function filelist(){

         return $this->fetch();
     }
}