<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/5/25
 * Time: 9:05
 */

namespace app\admin\model;


use think\Model;

class PublicModel extends Model
{
    //获取前台传来的数据
    protected   $param = array();

    //返回的信息
    protected $msg = '';



    /**
     * 获取前台传来的数据的函数
     * @param $param
     * @author yang
     * Date: 2022/5/19
     */
    public  function setParam($param=array()){
        $this->param = $param;
    }

    /**
     * 函数需要输出
     * @return string
     * @author yang
     * Date: 2022/5/22
     */
    public function getMsg(){
        return $this->msg;
    }



}