<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/4
 * Time: 14:10
 */

namespace app\record\model;


use app\common\model\FrontModel;
use app\common\utils\Yutils;

class Record extends FrontModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__RECORD_LOG__';


    public function getRecordList(){
        date_default_timezone_set("PRC");
        $data = $this->param;
        $limit = 10;


        $where = [
            ['user_id','=',$data['user_id']]
        ];
        if(isset($data['addday'])){
            $where[] = ['addday','=',strtotime($data['addday'])];
          
        }
        $list = self::where($where)->order("addtime desc")->field("content,id,title,pictures,addtime,addday")->paginate($limit)->each(function($item,$key){
            $item->addtime = date('Y-m-d H:i:s',$item->addtime);
            $item->addday = date('Y-m-d',$item->addday);
            $item->pictures = explode("|",$item->pictures );
        });
        $arr = [];
        foreach($list as $k=>$v){
            $arr[$k] = $v;
        }
        return ['data'=>$arr,'count'=>1];
    }

    public function add(){
        $data = $this->param;
        $arr = [];
        if(!$data['remark']){
            $this->msg = "请填写内容";
            return false;

        }
        $arr['content'] = $data['remark'];
        $arr['title'] = mb_substr($data['remark'],0,10);
        $arr['user_id'] = 1;
        $arr['addtime'] = time();

        $arr['addday'] =  mktime(0,0,0,date("m"),date("d"),date("Y"));
        $arr['pictures'] = $data['service_imaglist'];

        if($id = self::insertGetId($arr)){
            $this->msg = '添加成功';return $id;
        }
        $this->msg ='添加失败';return false;
    }

    public function del(){
        $data = $this->param;
        $where = [
            ['user_id','=',(int)$data['user_id']],
            ['id','=',(int)$data['id']]
        ];

        $re = self::where($where)->delete();
        if($re){
            $this->msg = "删除成功";return true;
        }else{
            $this->msg = "删除失败";return false;
        }
    }
}