<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/9
 * Time: 10:15
 */

namespace app\simplebook\admin\model;

use think\Db;
use app\admin\model\PublicModel;
use think\Exception;
use app\simplebook\admin\model\Projectmenu;

class Attrcat extends PublicModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SIMPLEBOOK_ATTRCAT__';


    /**
     * 根据菜单删除属性分类
     * @param int $menu_id
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author yang
     * Date: 2022/6/9
     */
    public function delAttrcatByMenuId($menu_id=0){
        if($menu_id){
            //获取该meun_id下的所有属性分类
            $attrcat_list = self::where('menu_id','=',$menu_id)->column('id');
            if(self::where('menu_id','=',$menu_id)->delete()){
                //删除所有分类的所有属性
                Attr::delAttrByAttrCatIdArray($attrcat_list);
            }
        }
        return false;
    }


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
        if(isset($data['menu_id']) && intval($data['menu_id']) > 0){
            $where[] = ['menu_id','=',intval($data['menu_id'])];
        }else  if(isset($data['project_id']) && intval($data['project_id']) > 0){
            $menu_id = \app\simplebook\admin\model\Projectmenu::where('project_id','=',$data['project_id'])->column('id');
            $where[] = ['menu_id','in',$menu_id];
        }
        $list = self::where($where);

        if(isset($this->param['order']) && in_array($this->param['order'],['asc','desc'])
            &&isset($this->param['field'])&& in_array($this->param['field'],['addtime'])){
            $list = $list->order($this->param['field'].' '. $this->param['order']);
        }
        $list = $list->paginate($limit)->each(function($value,$key){
            $value['menu_title'] = Projectmenu::where('id','=',$value['menu_id'])->value('menu_title');
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
        $validate =  new \app\simplebook\admin\validate\Attrcat();
        if(!$validate->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        $arr = [];
        $arr['attrcat_title'] = $data['attrcat_title'];
        $arr['menu_id'] = $data['menu_id'];
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
        $validate = new \app\simplebook\admin\validate\Attrcat();
        if(!$validate->scene('edit')->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        $arr = [];
        $arr['attrcat_title'] = $data['attrcat_title'];
        $arr['menu_id'] = $data['menu_id'];
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
        $attr = new Attr();
        Db::startTrans();
        try{
            if(self::where('id','=',$data['id'])->delete()){
                $attr->delAttrByAttrCatId($data['id']);
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
      $attr =  self::where('id','=',$id)->find();
      $attr['project_id'] = Projectmenu::where('id','=',$attr['menu_id'])->value('project_id');
      return $attr;
    }


    /**
     * 获取项目列表
     * @return mixed
     * @author yang
     * Date: 2022/6/9
     */
    public function getProjectList(){
        return Project::select();
    }

    public function getMenuList($project_id=0){
        return Projectmenu::where('project_id','=',$project_id)->select();
    }

}