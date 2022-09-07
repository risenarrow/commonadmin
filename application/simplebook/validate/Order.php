<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/14
 * Time: 14:17
 */

namespace app\simplebook\validate;

use app\simplebook\model\Attr;
use think\validate;
use app\simplebook\model\Projectmenu;
use app\simplebook\model\Project;
use app\simplebook\model\Cart;
class Order extends validate
{
    protected $rule = [
        'user_id'  =>  'require|checkIsvalid',
        'menu_id'=>'require|checkMenuValid'
    ];

    protected $message = [
        'user_id.require' => '用户不能为空',
        'menu_id.require' => '菜单不能为空',
        'num.require'=>'数量不能为空',
        'num.number'=>'数量格式不正确',
        'num.min'=>'数量格式不正确'
    ];

    public function sceneCart(){
        return $this->only(['user_id','menu_id','attr_list','num'])
            ->append('num','require|number|min:1')
            ->append('attr_list', 'checkAttrValid');

    }


    public function sceneOrder(){
        return $this->only(['user_id','menu_list','project_list','attr_list'])
            ->append('cart_list', 'checkCartListValid')
            ->append('menu_list','checkMenuListValid')
            ->append('project_list', 'checkProjectListValid')
            ->append('attr_list', 'checkAttrListValid');
    }

    protected function checkIsvalid($value,$rules,$data){
        return true;
    }

    protected function checkMenuValid($value,$rules,$data){
        if(\app\simplebook\model\Projectmenu::where('id','=',$value)->count()){
            return true;
        }
        return '菜单不存在';
    }

    protected function checkAttrValid($value,$rules,$data){
        $arr = json_decode($value,true);

        if($arr === null){
            return '属性格式不正确';
        }
        if($arr){
            foreach($arr as $k=>$v){
                //查找是否有
                $count = Attr::where([
                    ['attr_cat_id','=',$v['attr_cat_id']],
                    ['attr_title','=',$v['attr_title']],
                    ['attr_price','=',$v['attr_price']],
                ])->count();
                if($count <= 0){
                    return '属性不存在';
                }
            }
        }
        return true;
    }


    protected function checkMenuListValid($cartlist,$rules,$data){
        $menu_list_id = array_column($cartlist,'menu_id');
        $_list = array_combine(array_column($cartlist,'menu_id'),$cartlist) ;
        $count = Projectmenu::where('id','in',$menu_list_id)->count();
        if($count != count($_list)){
            return "购物车中有商品不存在";
        }
        return true;
    }

    protected function checkCartListValid($cartlist,$rules,$data){
        if(!$cartlist){
            return "购物车为空";return false;
        }
        return true;
    }
    protected function checkProjectListValid($project_list,$rules,$data){
        $project_list_id = array_column($project_list,'id');
        $count = Project::where('id','in',$project_list_id)->count();
        if($count != count($project_list_id)){
            $this->msg = "购物车中有项目商品不存在";
        }
        return true;
    }

    protected function checkAttrListValid($cartlist,$rules,$data){
        foreach ($cartlist as $k=>$v){
            $re = $this->checkAttrValid(json_encode($v['attr_list']),$rules,$data);
            if($re !== true){
                return $re;
            }
        }
        return true;
    }
}