<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/15
 * Time: 8:22
 */

namespace app\simplebook\model;

use app\common\model\FrontModel;
class Cart extends FrontModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SIMPLEBOOK_CART__';


    public function getList(){
        $data = $this->param;
        $user_id = 1;
        $list = self::where('user_id','=',$user_id)
            ->field('id,menu_id,menu_title,attr_list,price,num,attr_price')
            ->select()->each(function($value,$key){
                $value->attr_list = json_decode($value->attr_list,true);
                $value->amount = $value->price+$value->attr_price;
            })->toArray();
        return $list;
    }


    public function addCart(){
        $data = $this->param;
        $data['user_id']=1;
        $validate = new \app\simplebook\validate\Order();

        if(!$validate->scene('Cart')->check($data)){
            $this->msg = $validate->getError();
            return false;
        }
        //获取菜单信息
        $projectmenu = new Projectmenu();
        $menu_info = $projectmenu->getInfoById($data['menu_id']);

        //获取属性信息

        $attr_list = json_decode($data['attr_list'],true);

        $attr_price = 0.00;
        foreach ($attr_list as $k=>$v){
            $attr_price += $v['attr_price'];
        }

        //组装数据
        $arr=[];
        $arr['user_id'] = $data['user_id'];
        $arr['user_name'] = 'yang';
        $arr['menu_id'] = $data['menu_id'];
        $arr['menu_title'] = $menu_info['menu_title'];
        $arr['project_id'] = $menu_info['project_id'];
        $arr['attr_list'] = $data['attr_list'];
        $arr['num'] = $data['num'];
        $arr['price'] = $menu_info['price'];
        $arr['attr_price'] = $attr_price;
        $arr['addtime'] = time();
        //检查是否存在购物车中
        $where = [
            ['user_id','=',$data['user_id']],
            ['menu_id','=',$data['menu_id']],
            ['attr_list','=',$data['attr_list']]
        ];
        $count = self::where($where)->count();
        //如果存在则更新购物车
        if($count > 0){

            $re =  self::where($where)->setInc('num',$arr['num']);
        }else{
            $re = self::insert($arr);
        }
        if($re){
            $this->msg="添加购物车成功";
            return true;
        }else{
            $this->msg = '添加购物车失败';
            return false;
        }
    }

    public function delcart(){
        $data = $this->param;
        $user_id = 1;
        if($data['id'] = intval($data['id'])){
            if(self::where([
                ['user_id','=',$user_id],
                ['id','=',$data['id']]
            ])->delete()){
                $this->msg="删除成功";
                return true;
            }
            $this->msg='删除失败';
            return false;
        }
        $this->msg='删除失败';
        return false;
    }



    public function getListOrder(){
        $data = $this->param;
        $list = self::where('user_id','=',$data['user_id'])
            ->select()->each(function($value,$key){
                $value->attr_list = json_decode($value->attr_list,true);
                $value->amount = ($value->price+$value->attr_price)*$value->num;
            })->toArray();
        return $list;
    }

    public function getProjectList($cartlist=[]){
        $list = [];
        if($cartlist){
            //获取购物车的项目id
            $project_id = array_column($cartlist,'project_id');
            $list = Project::where('id','in',$project_id)->field('id,project_title')->select()->toArray();
            return $list;
        }
       return $list;
    }

    public function changeCart(){
        $data = $this->param;
        $user_id = 1;
        $num = intval($data['num']);
        if($num <= 0 && $num > 99){
            $this->msg= '数量不正确';return false;
        }

        $re = self::where([
            ['user_id','=',$user_id],
            ['id','=',$data['id']]
        ])->update(['num'=>$data['num']]);

        if($re){
            $this->msg = '修改成功';return $data['num'];
        }
        $this->msg = '修改失败';
        return false;
    }
}