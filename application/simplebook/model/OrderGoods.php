<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/16
 * Time: 15:11
 */

namespace app\simplebook\model;

use app\common\model\FrontModel;

class OrderGoods extends FrontModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SIMPLEBOOK_ORDER_GOODS__';
}