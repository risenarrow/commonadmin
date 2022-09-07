<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/1
 * Time: 12:53
 */

namespace app\test\controller;
use app\common\controller\FrontBase;


class Index extends FrontBase
{
    public function index(){
        return $this->fetch();
    }
}