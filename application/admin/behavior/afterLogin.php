<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/12
 * Time: 23:28
 */

namespace app\admin\behavior;
use app\admin\model\User as UserModel;
class afterLogin
{
    /**
     * @author yang
     * Date: 2019/11/12
     */
    public function run($param){
        $data['lastlogin_ip'] = get_client_ip();
        $data['lastlogin_time'] = time();
        UserModel::where(['id'=>$param['id']])->update($data);
        session('admin_id',$param['id']);
        session('admin',$param);
        session('admin_name',$param['admin_name']);
        UserModel::admin_log($param['id'],1,$param['admin_name'],'登录');
    }
}