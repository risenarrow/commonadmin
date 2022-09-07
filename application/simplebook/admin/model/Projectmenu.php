<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/9
 * Time: 8:17
 */

namespace app\simplebook\admin\model;
use app\admin\model\PublicModel;
use think\Db;
use think\Exception;

class Projectmenu extends PublicModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SIMPLEBOOK_MENU__';

    /**
     *
     * 获取项目列表
     * @return array
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/6/9
     */
    public function getList(){
        $limit = $this->param['limit'];
        $data = $this->param;
        $where = [];
        if(isset($data['project_id']) && intval($data['project_id']) > 0){
            $where[] = ['project_id','=',intval($data['project_id'])];
        }
        $list = self::where($where);

        if(isset($this->param['order']) && in_array($this->param['order'],['asc','desc'])
            &&isset($this->param['field'])&& in_array($this->param['field'],['addtime'])){
            $list = $list->order($this->param['field'].' '. $this->param['order']);
        }
        $list = $list->paginate($limit)->each(function($value,$key){
            $value['project_title'] = Project::where('id','=',$value['project_id'])->value('project_title');
        });
        $arr = [];
        foreach($list as $k=>$v){
            $arr[$k] = $v;
        }
        return ['data'=>$arr,'count'=>$list->total()];
    }



    /**
     *
     * 添加
     * @return bool
     * @author yang
     * Date: 2022/6/9
     */
    public function add(){
        $data = $this->param;
        $validate =  new \app\simplebook\admin\validate\Projectmenu();
        if(!$validate->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        $arr = [];
        $arr['menu_title'] = $data['menu_title'];
        $arr['project_id'] = $data['project_id'];
        $arr['price'] = $data['price'];
        if(self::insert($arr)){
            $this->msg = '添加成功';return true;
        }
        $this->msg ='添加失败';return false;
    }

    /**
     * 编辑
     * @return bool
     * @throws Exception
     * @throws \think\exception\PDOException
     * @author yang
     * Date: 2022/6/9
     */
    public function edit(){
        $data = $this->param;
        $validate = new \app\simplebook\admin\validate\Projectmenu();
        if(!$validate->scene('edit')->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        $arr = [];
        $arr['menu_title'] = $data['menu_title'];
        $arr['project_id'] = $data['project_id'];
        $arr['price'] = $data['price'];
        if(self::where('id','=',$data['id'])->update($arr)){
            $this->msg = '修改成功';return true;
        }
        $this->msg ='修改失败';return false;
    }

    /**
     * 删除
     * @return bool
     * @author yang
     * Date: 2022/6/9
     */
    public function del(){
        $data = $this->param;
        $data['id'] = intval($data['id']);
        //删除该菜单下的属性分类
        $attrcat = new Attrcat();
        Db::startTrans();
        try{
            if(self::where('id','=',$data['id'])->delete()){
                $attrcat->delAttrcatByMenuId($data['id']);
                $this->msg = '删除成功';
                Db::commit();
                return true;
            }else{
                Db::rollback();   $this->msg ='删除失败';return false;
            }
        }catch (Exception $e){
            Db::rollback();
            $this->msg= $e->getMessage();return false;
        }


    }


    /**
     * 通过id获取数据
     * @param int $id
     * @return mixed
     * @author yang
     * Date: 2022/5/30
     */
    public function getInfoById($id=0){
        return self::where('id','=',$id)->find();
    }


    public function getProjectList(){
        return Project::select();
    }


}