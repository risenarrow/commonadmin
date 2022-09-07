<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/13
 * Time: 14:44
 */

namespace app\simplebook\model;
use app\common\model\FrontModel;
use think\Db;
use think\Exception;

class Project extends  FrontModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SIMPLEBOOK_PROJECT__';

    public function getList(){
        $limit = $this->param['limit'];
        $limit = 100;
        $where = [];
        $list = self::where($where);

        if(isset($this->param['order']) && in_array($this->param['order'],['asc','desc'])
            &&isset($this->param['field'])&& in_array($this->param['field'],['addtime'])){
            $list = $list->order($this->param['field'].' '. $this->param['order']);
        }
        $list = $list->field('project_title,addtime,id')->paginate($limit)->each(function($item,$key){
            $item->addtime = date('Y-m-d H:i:s',$item->addtime);
        });
        $arr = [];
        foreach($list as $k=>$v){
            $arr[$k] = $v;
        }
        return ['data'=>$arr,'count'=>$list->total()];
    }
}