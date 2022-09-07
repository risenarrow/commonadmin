<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/9
 * Time: 10:00
 */

namespace app\simplebook\admin\model;


use app\admin\model\PublicModel;
use think\Db;
use think\Exception;

class Attr extends PublicModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SIMPLEBOOK_ATTR__';


    //获取该属性下的所有选项
    public function getList(){
        $data = $this->param;
        if(isset($data['attrcat_id'])){
            $attr_list = self::where('attr_cat_id','=',$data['attrcat_id'])->select()->toArray();
            return $attr_list;
        }
        return [];
    }

    public function add(){
        $data = $this->param;
        //检查属性选项
        foreach ($data['attr_title'] as $key=>$val){
            if(!$val){

                $this->msg = '属性选项格式不正确';
                return false;
            }
        }
        foreach($data['price_attr'] as $key=>$val){
            if(!is_float($val)&&!is_numeric($val)&&$val<0){

            $this->msg = '属性价格格式不正确';
            return false;
            }
        }
        //组合两个数组
        $newarr = [];
        foreach ($data['attr_title'] as $key=>$val){
            $newarr[] = ['attr_cat_id'=>$data['attrcat_id'],
                        'attr_title'=>$val,
                        'attr_price'=>$data['price_attr'][$key]
            ];
        }
        Db::startTrans();
        try{
            //先删除该属性下所有选项
            self::where('attr_cat_id','=',$data['attrcat_id'])->delete();
            //添加所有属性
            self::insertAll($newarr);
            Db::commit();
            $this->msg = '添加成功';return true;

            Db::rollback();
            $this->msg = '添加失败';
            return false;
        }catch (Exception $e){
            $this->msg = $e->getMessage();
            Db::rollback();
            return false;
        }
        $this->msg = '添加失败';return false;
    }



    public static function delAttrByAttrCatId($attrcat_id=0){
       //先删除
        return self::where('cat_id','=',$attrcat_id)->delete();
    }
    public static function delAttrByAttrCatIdArray($attrcat_id=array()){
        //先删除
       return self::where('cat_id','in',$attrcat_id)->delete();
    }
}