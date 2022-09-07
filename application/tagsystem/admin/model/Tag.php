<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/5
 * Time: 17:03
 */

namespace app\tagsystem\admin\model;
use app\admin\model\Adminrole;
use app\admin\model\PublicModel;

class Tag extends PublicModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__TAGSYSTEM_LIST__';

    /**
     * 获取标签列表
     * @return array
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/6/6
     */
    public function getTagList(){
        $limit = $this->param['limit'];
        $data = $this->param;
        $where = [];
        if(isset($data['istop'])){
            $where[] = ['istop','=',$data['istop']];
        }


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
        $validate =  new \app\tagsystem\admin\validate\Tag();
        if(!$validate->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        //获取用户信息
        $user_data = $this->getUserInfoByUserName($data['user_name']);
        $arr = [];
        $arr['title'] = $data['title'];
        $arr['link'] = $data['link'];
        $arr['des'] = $data['des'];
        $arr['user_id'] = $user_data['user_id'];
        $arr['user_name'] = $user_data['user_name'];
        $arr['addtime'] = time();
        $arr['updatetime'] = time();
        if(self::insert($arr)){
            $this->msg = '添加成功';return true;
        }
        $this->msg ='添加失败';return false;
    }

    public function edit(){
        $data = $this->param;
        $validate = new \app\tagsystem\admin\validate\Tag();
        if(!$validate->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        //获取用户信息
        $user_data = $this->getUserInfoByUserName($data['user_name']);
        $arr = [];
        $arr['title'] = $data['title'];
        $arr['link'] = $data['link'];
        $arr['des'] = $data['des'];
        $arr['user_id'] = $user_data['user_id'];
        $arr['user_name'] = $user_data['user_name'];
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

    public function istop(){
        $data = $this->param;

        if(isset($data['istop']) && in_array($data['istop'],[1,0])){
            if( self::where('id','=',$data['id'])->update(['istop'=>$data['istop']])){
                $this->msg = '修改成功';return true;
            }
        }
        $this->msg = '修改失败';
        return false;
    }



    public function addcat(){
        $data = $this->param;
        $indata = [
            'cat_name'=>$data['cat_name']
        ];
        if(db('tagsystem_cat')->insert($indata)){
            $this->msg = '添加成功';return true;
        }
        $this->msg = '添加失败';
        return false;
    }

    public function editcat(){
        $data = $this->param;
        $indata = [
            'cat_name'=>$data['cat_name']
        ];
        if(db('tagsystem_cat')->where('id','=',$data['id'])->update($indata)){
            $this->msg = '修改成功';return true;
        }
        $this->msg = '修改失败';
        return false;
    }
    public function delcat(){
        $data = $this->param;
        if(db('tagsystem_cat')->where('id','=',$data['id'])->delete()){
            $this->msg = '删除成功';return true;
        }
        $this->msg = '删除失败';
        return false;
    }


    public function getCatDataById($id=0){
        $data = db('tagsystem_cat')->where('id','=',$id)->find();
        return $data;
    }

    public function getCatList(){
        $limit = $this->param['limit'];
        $data = $this->param;
        $where = [];
        $list =db('tagsystem_cat')->where($where);
        $list = $list->paginate($limit);
        $arr = [];
        foreach($list as $k=>$v){
            $arr[$k] = $v;
        }
        return ['data'=>$arr,'count'=>$list->total()];
    }


    /**
     * 通过id获取数据
     * @param int $id
     * @return mixed
     * @author yang
     * Date: 2022/5/30
     */
    public function getTagInfoById($id=0){
        return self::where('id','=',$id)->field('id,title,link,des,user_name')->find();
    }


    public function getUserInfoByUserName($user_name=''){
        return ['user_id'=>1,'user_name'=>$user_name];
    }
}