<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/6
 * Time: 19:31
 */

namespace app\tagsystem\model;
use app\common\model\FrontModel;

class Tag extends FrontModel
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
        $data = $this->param;
        $where = [];
        if(isset($data['cat_id']) && $data['cat_id'] != 0){
            $where[] = ['cat_id','=',$data['cat_id']];
        }
        $list = self::where($where)->order("istop desc,addtime desc")->field("istop,id,title,ico,link,addtime,cat_id")->select()->each(function($item,$key){
            $item->addtime = date('Y-m-d H:i:s',$item->addtime);
        });
        $arr = [];
        foreach($list as $k=>$v){
            $arr[$k] = $v;
        }
        return ['data'=>$arr,'count'=>1];
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
        $arr['ico'] = $data['ico'];
        $arr['link'] = $data['link'];
        $arr['des'] = $data['des'];
        $arr['user_id'] = $user_data['user_id'];
        $arr['user_name'] = $user_data['user_name'];
        $arr['cat_id'] = $data['cat_id'];
        $arr['addtime'] = time();
        $arr['updatetime'] = time();
        if($id = self::insertGetId($arr)){
            $this->msg = '添加成功';return $id;
        }
        $this->msg ='添加失败';return false;
    }

    public function del(){
        $data = $this->param;
        $validate =  new \app\tagsystem\admin\validate\Tag();
        if(!$validate->scene('Del')->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        if(self::where('id','=',$data['id'])->delete()){
            $this->msg = '删除成功';return true;
        }
        $this->msg ='删除失败';return false;
    }


    public function istop(){
        $data = $this->param;
        $validate =  new \app\tagsystem\admin\validate\Tag();
        if(!$validate->scene('Del')->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        $istop = self::where('id','=',$data['id'])->value('istop');
        $val = 1;
        if($istop == 1){
            $val = 0;
        }
        if(self::where('id','=',$data['id'])->update(['istop'=>$val])){
            $this->msg = '修改成功';
            return true;
        }
        $this->msg = '修改失败';return false;
    }

    public function chselectcat(){
        $data = $this->param;
        if(self::where('id','=',$data['id'])->update(['cat_id'=>$data['cat_id']])){
            $this->msg = '修改成功';
            return true;
        }
        $this->msg = '修改失败';return false;
    }


    public function getCatList(){
        $data = $this->param;
        $where = [];
        $list = db('tagsystem_cat')->where($where)->select();
        $arr = [];
        foreach($list as $k=>$v){
            $arr[$k] = $v;
        }
        return ['data'=>$arr,'count'=>1];
    }


    public function getUserInfoByUserName($user_name=''){
        return ['user_id'=>1,'user_name'=>$user_name];
    }

}