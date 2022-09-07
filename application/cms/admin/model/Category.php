<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/9
 * Time: 8:17
 */

namespace app\cms\admin\model;
use app\admin\model\PublicModel;


class Category extends PublicModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__CMS_CATEGORY__';

    /**
     *
     * 获取项目列表
     * @return array
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/6/9
     */
    public function getCategoryList(){
        $limit = $this->param['limit'];

        $where = [];
        $list = self::where($where);

        if(isset($this->param['order']) && in_array($this->param['order'],['asc','desc'])
            &&isset($this->param['field'])&& in_array($this->param['field'],['addtime'])){
            $list = $list->order($this->param['field'].' '. $this->param['order']);
        }
        $list = $list->paginate($limit)->each(function($item,$key){
            $item->addtime = date('Y-m-d H:i:s',$item->addtime);
        });
        $arr = [];
        foreach($list as $k=>$v){
            $arr[$k] = $v;
        }
        return ['data'=>$arr,'count'=>$list->total()];
    }

    public function add(){
        $data = $this->param;
        $validate =  new \app\simplebook\admin\validate\Project();
        if(!$validate->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        $arr = [];
        $arr['project_title'] = $data['project_title'];
        $arr['des'] = $data['des'];
        $arr['addtime'] = time();
        $arr['updatetime'] = time();
        if(self::insert($arr)){
            $this->msg = '添加成功';return true;
        }
        $this->msg ='添加失败';return false;
    }

    public function edit(){
        $data = $this->param;
        $validate = new \app\simplebook\admin\validate\Project();
        if(!$validate->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        $arr = [];
        $arr['project_title'] = $data['project_title'];
        $arr['des'] = $data['des'];
        $arr['updatetime'] = time();
        if(self::where('id','=',$data['id'])->update($arr)){
            $this->msg = '修改成功';return true;
        }
        $this->msg ='修改失败';return false;
    }

    public function del(){
        $data = $this->param;
        $data['id'] = intval($data['id']);
        if(self::where('id','=',$data['id'])->delete()){
            $this->msg = '删除成功';return true;
        }
        $this->msg ='删除失败';return false;
    }


    /**
     * 通过id获取数据
     * @param int $id
     * @return mixed
     * @author yang
     * Date: 2022/5/30
     */
    public function getProjectInfoById($id=0){
        return self::where('id','=',$id)->field('id,project_title,des')->find();
    }


}