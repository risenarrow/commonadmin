<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/16
 * Time: 9:41
 */

namespace app\simplebook\model;
use app\common\model\FrontModel;

class OrderPay extends FrontModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SIMPLEBOOK_ORDER_PAY__';

    public function payOrder(){
        $pay_data = $this->param;
        return self::insertGetId($pay_data);
    }
}