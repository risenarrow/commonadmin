<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2019/11/14
 * Time: 21:42
 */

namespace app\admin\controller;
use app\common\utils\Yutils;
use app\common\controller\Base;
use app\admin\model\User as UserModel;

class Publics extends Base
{

    public function login(){
        
        $admin_id = session('admin_id');
        if($admin_id){
            $this->success('你已登录',admin_url(self::$ADMIN_INDEX_URL));
        }

        if($this->request->isAjax()){
            $username = $this->request->param('username','');
            $password = $this->request->param('password','');
            $verifycode = $this->request->param('verifycode','');

            $return = UserModel::login($username,$password,$verifycode);
            if($return['status'] == 1){
                $this->success('登录成功！');
            }else{
                $this->error($return['msg']);
            }
        }
        return $this->fetch();
    }

    public function logout(){
        $re = UserModel::logout();
        if($re){
            $this->redirect(self::$ADMIN_LOGIN_URL);exit;
        }else{
            $this->error('退出失败！');
        }
    }
}