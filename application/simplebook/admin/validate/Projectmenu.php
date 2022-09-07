<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/9
 * Time: 9:38
 */

namespace app\simplebook\admin\validate;

use think\Validate;
class Projectmenu extends  Validate
{
    protected $rule = [
        'project_id'  =>  'require|number|min:1',
        'menu_title'=>'require|unique:simplebook_menu',
        'price'=>'float|min:0'
    ];

    protected $message = [
        'project_id.require' => '项目不能为空',
        'project_id.number'=>'项目不能为空',
        'project_id.min'=>'项目不能为空',
        'menu_title.require'=>'菜单名称不能为空',
        'menu_title.unique'=>'菜单名称已存在',
        'price.number' =>'价格格式不正确',
        'price.min'=>'价格格式不正确'
    ];

    protected function sceneEdit(){
        return $this->only(['project_id,price,menu_title'])
            ->remove('menu_title', 'unique');
    }
}