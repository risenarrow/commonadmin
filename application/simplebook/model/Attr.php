<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/14
 * Time: 16:41
 */

namespace app\simplebook\model;
use app\common\model\FrontModel;
use think\Exception;


class Attr extends FrontModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SIMPLEBOOK_ATTR__';


}