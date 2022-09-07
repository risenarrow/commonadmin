<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/5/31
 * Time: 18:23
 */

namespace app\test\admin\controller;
use app\admin\controller\AdminBase;
use app\admin\utils\Rsa;


class Testadmin extends AdminBase
{
    public function index(){
        $str = Rsa::publicEncrypt('{user_name:aaaa,password:123456}','K:\others\z帐号\rsa_public_key.pem');
          var_dump($str)."\n\n\n\n";
          $str1 = Rsa::privDecrypt($str,'K:\others\z帐号\rsa_private_key.pem');
          var_dump($str1);
       //return $this->fetch();
    }

}