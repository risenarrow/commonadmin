<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/16
 * Time: 16:47
 */

namespace app\gotochat\model;


use app\common\model\FrontModel;

class Area extends FrontModel
{
    protected $pk = "areaId";
    protected $table = "__AREA__";
    public  function getProvince(){
        $data = $this->param;
        if(!isset($data['country_id'])){
            $this->msg = "输入有误";
            return false;
        }
        return self::where('parentId','=',$data['country_id'])->select()->toArray();
    }

    public function getCity(){
        $data = $this->param;
        if(!isset($data['province_id'])){
            $this->msg = "输入有误";
            return false;
        }
        return self::where('parentId','=',$data['province_id'])->select()->toArray();
    }
}