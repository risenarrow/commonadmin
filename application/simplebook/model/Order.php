<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/14
 * Time: 15:12
 */

namespace app\simplebook\model;
use app\common\model\FrontModel;
use think\Db;
use think\Exception;

class Order extends FrontModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SIMPLEBOOK_ORDER__';

    private $pay_id = 0;

    private $order_id = 0;


    public function bookorder(){
        $data= $this->param;
        $cart = new Cart();
        $user_id = 1;
        $user_name = 'yang';
        //获取购物车列表
        $cart->setParam(['user_id'=>$user_id]);
        $cartlist = $cart->getListOrder();
        //获取购物车中的项目列表
        $project_list = $cart->getProjectList($cartlist);

        //下单前检查
        $arr = ['user_id'=>$user_id,'menu_list'=>$cartlist,'project_list'=>$project_list,'cart_list'=>$cartlist,'attr_list'=>$cartlist];
        $validata = new \app\simplebook\validate\Order();
        if(!$validata->scene('Order')->check($arr)){
            $this->msg = $validata->getError();return false;
        }
        //计算订单价格
        $amount = 0.00;
        foreach ($cartlist as $k=>$v){
            $amount += $v['amount'];
        }

        //计算每个项目的订单总价
        $project_amount = [];
        foreach ($project_list as $k=>$v){
            $am = 0.00;
            foreach ($cartlist as $key=>$val){
                if($val['project_id'] == $v['id']){
                    $am += $val['amount'];
                }
            }
            $project_amount[$v['id']] = $am;
        }

        //插入订单信息
        Db::startTrans();
        try{
            //先删除购物车
            $cartlist_id = array_column($cartlist,'id');
            $re = Cart::where('id','in',$cartlist_id)->delete();
            if(!$re){
                Db::rollback();
                $this->msg = '下单失败';
                return false;
            }
            $orders = [];
            //组装插入订单的数据
            $insertD = [
                'order_status'=>0,
                'pay_status'=>0,
                'order_remark'=>$data['remark'],
                'user_id'=>$user_id,
                'user_name'=>$user_name,
                'addtime'=>time()
            ];
            //根据项目拆分订单 开始
            foreach ($project_list as $k=>$v){
                $insertD['project_id'] = $v['id'];
                $insertD['project_name'] = $v['project_title'];
                $insertD['order_amount'] = $project_amount[$v['id']];
                $insertD['order_number'] = date('ymdhis').rand(000000,999999);

                //插入订单表
                $insertId = self::insertGetId($insertD);

                //根据订单id插入订单商品表
                if($insertId >0){
                    $goodsInsertD = [
                        'order_id'=>$insertId,
                    ];
                    foreach ($cartlist as $key=>$val){
                        if($v['id'] == $val['project_id']){
                            $goodsInsertD['menu_id'] = $val['menu_id'];
                            $goodsInsertD['menu_title'] = $val['menu_title'];
                            $goodsInsertD['amount'] = $val['amount'];
                            $goodsInsertD['attr_list'] = json_encode($val['attr_list']);
                            $goodsInsertD['menu_price'] = $val['price'];
                            $goodsInsertD['attr_price'] = $val['attr_price'];
                            $goodsInsertD['num'] = $val['num'];

                            $re = OrderGoods::insert($goodsInsertD);

                            if(!$re){
                                Db::rollback();
                                $this->msg = '下单失败';return false;
                            }
                        }
                    }
                    $orders[] = $insertId;
                }
            }
            //根据项目拆分订单 结束


            //插入支付订单表
            $orderpay = new OrderPay();
            $orderpay-> setParam(['pay_order_id'=>json_encode($orders), 'pay_amount'=>$amount, 'user_id'=>$user_id,]);
            $this->pay_id = $orderpay->payOrder();
            Db::commit();
            $this->msg = '下单成功';
            return true;
        }catch (Exception $e){
            $this->msg = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    public function getPayId(){
        return $this->pay_id;
    }

    
    public function getList(){
        $data = $this->param;
        $user_id = 1;
        $status = intval($data['order_status']);
        $limit = 10;
        $where = [];
        $where[] = ['user_id','=',$user_id];
        if($status != -2){
            $where[] = ['order_status','=',$status];
        }
       $list = self::where($where)->order('addtime asc')->field('order_number,id,order_status,order_remark,order_amount,addtime')->paginate($limit)->each(function($value,$key){
            $value->addtime = date('Y-m-d H:i:s',$value->addtime);
        });
        $orderlist = $list->items();
        foreach($orderlist as $k=>$v){

            $orderlist[$k]['goods_list'] = OrderGoods::where('order_id','=',$v['id'])
                ->field('order_id,menu_id,menu_title,amount,attr_list,menu_price,attr_price,num')
                ->select()->each(function($value,$key){
                    $value->attr_list = json_decode($value->attr_list,true);
                })->toArray();
        }
        return $orderlist;
    }

    public function changeOrderStatus(){
        $data = $this->param;
        $user_id = 1;
        $newStatus = intval($data['newStatus']);
        $oldStatus = intval($data['oldStatus']);
        if(in_array($newStatus,[-1,0,1,2,3])){
            $re = self::where([
                ['user_id','=',$user_id],
                ['order_status','=',$oldStatus],
                ['id','=',$data['id']]
            ])->update(['order_status'=>$newStatus]);
            if($re){
                $this->msg = '修改成功';
                return true;
            }
        }
        $this->msg = "修改订单状态失败";
        return false;
    }

}