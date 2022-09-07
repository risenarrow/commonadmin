<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/9
 * Time: 9:38
 */

namespace app\simplebook\admin\validate;

use think\Validate;
class Attrcat extends  Validate
{
    protected $rule = [
        'menu_id'  =>  'require|number|min:1',
        'attrcat_title'=>'require|checkIsExist'
    ];

    protected $message = [
        'menu_id.require' => '菜单不能为空',
        'menu_id.number'=>'菜单不能为空',
        'menu_id.min'=>'菜单不能为空',
        'attrcat_title.require'=>'菜单名称不能为空',
        'attrcat_title.unique'=>'菜单名称已存在',
    ];

    protected function sceneEdit(){
        return $this->only(['menu_id,attrcat_title'])
            ->remove('attrcat_title', 'unique');
    }

    protected function checkIsExist($value,$rules,$data){
       $re =  \app\simplebook\admin\model\Attrcat::where([
            ['menu_id','=',$data['menu_id']],
            ['attrcat_title','=',$value]
        ])->count();
       if($re){
           return '该菜单下的属性已存下';
       }
       return true;
    }

}