<?php
namespace app\admin\model;

use think\facade\Cache;
use think\Model;

class AdminRolePriv extends Model
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__ADMIN_ROLE_PRIV__';


    public static function getAuth($role_id){
        $admin_auth = Cache::get('admin_'.$role_id.'_auth');

        if($admin_auth){

            return $admin_auth;
        } else {
            $admin_auth = self::where('role_id', '=', $role_id)->select();
            $admin_auth = $admin_auth->toArray();
            Cache::set('admin_'.$role_id.'_auth',$admin_auth);
            return $admin_auth;
        }
    }

}