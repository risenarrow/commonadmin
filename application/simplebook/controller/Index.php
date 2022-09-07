<?php
namespace app\simplebook\controller;

use app\common\controller\FrontBase;
use app\simplebook\model\Cart;
use app\simplebook\model\Order;
use app\simplebook\model\Project;
use app\simplebook\model\Projectmenu;
use think\App;

class Index extends FrontBase
{

    function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->project = new Project();
        $this->projectmenu = new Projectmenu();
        $this->order = new Order();
        $this->cart = new Cart();
    }

    public function index(){
     $this->project->setParam($this->request->param());
     $list = $this->project->getList();
     $this->echoJson('获取成功',0,$list['count'],$list['data']);
    }

    public function menulist(){
        $this->projectmenu->setParam($this->request->param());
        $list = $this->projectmenu->getList();
        $this->echoJson('获取成功',0,$list['count'],$list['data']);
    }

    public function detail(){
        $this->projectmenu->setParam($this->request->param());
        $detail = $this->projectmenu->getDetail();
        if($detail !== false){
            $this->echoJson($this->projectmenu->getMsg(),0,1,$detail);
        }
        $this->echoJson($this->projectmenu->getMsg(),1,0);
    }


    /**
     * 添加购物车
     * @author yang
     * Date: 2022/6/16
     */
    public function addcart(){
        $this->cart->setParam($this->request->param());
        if($this->cart->addCart()){
            $this->echoJson($this->cart->getMsg(),0);
        }
        $this->echoJson($this->cart->getMsg(),1);
    }

    /**
     * 获取购物车列表
     * @author yang
     * Date: 2022/6/16
     */
    public function cartlist(){
        $this->cart->setParam($this->request->param());
        $this->echoJson($this->cart->getMsg(),0,1,$this->cart->getList());
    }

    /**
     * 删除购物车
     * @author yang
     * Date: 2022/6/16
     */
    public function delcart(){
        $this->cart->setParam($this->request->param());
        if($this->cart->delcart()){
            $this->echoJson($this->cart->getMsg(),0);
        }
        $this->echoJson($this->cart->getMsg(),1);
    }


    public function changecart(){
        $this->cart->setParam($this->request->param());
        $re = $this->cart->changecart();
        if( $re !== false){
            $this->echoJson($this->cart->getMsg(),0,1,['num'=>$re]);
        }
        $this->echoJson($this->cart->getMsg(),1);
    }

    /**
     * 下单
     * @author yang
     * Date: 2022/6/16
     */
    public function order(){
        $this->order->setParam($this->request->param());
        if($this->order->bookorder()){
            $this->echoJson($this->order->getMsg(),0,1,$this->order->getPayId());
        }
        $this->echoJson($this->order->getMsg(),1);
    }


    public function orderlist(){
        $this->order->setParam($this->request->param());
        $this->echoJson($this->order->getMsg(),0,1,$this->order->getList());
    }

    public function chorderstatus(){
        $this->order->setParam($this->request->param());
        if($this->order->changeOrderStatus()){
            $this->echoJson($this->order->getMsg(),0,1);
        }
        $this->echoJson($this->order->getMsg(),1);
    }
}