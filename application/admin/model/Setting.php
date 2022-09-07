<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/5/19
 * Time: 8:08
 */
namespace app\admin\model;
use think\Exception;
use think\facade\Config;
use think\facade\Cache;
use app\admin\validate\Setting as SettingValidate;
use think\Db;

class Setting extends PublicModel {
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__CONFIG__';


    function __construct($data = [])
    {
        parent::__construct($data);

    }



    /**
     * 获取配置列表
     * @param int $ispage
     * @author yang
     * Date: 2022/5/19
     */
    public  function configList($ispage=0){

       if($ispage == 1){
           $list = $this->getConfigListPage();
       }else{
           $list = $this->getConfigList();
       }
       return $list;
    }


    /**
     *
     * 通过分页式获取
     * @return \think\Paginator
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/23
     */
    private  function getConfigListPage(){
        $where = array();
        $this->param['type_id'] && $where[] = ['type_id','=',$this->param['type_id']];
        $types = Config::get('config_type');
        $form_type = Config::get('form_type');
        $list = db('Config')->where($where)->paginate($this->param['limit']);
        $arr = [];
        foreach ($list->items() as $key=>$value){
            $arr[$key] = $value;
            $arr[$key]['type_id'] = $types[$value['type_id']];
            $arr[$key]['form_type'] = $form_type[$value['form_type']];
        }

       return ['data'=>$arr,'count'=>$list->total()];
    }

    /**
     * 不通过分页式获取
     * @author yang
     * Date: 2022/5/23
     */
    private  function getConfigList(){
        $type_id = $this->param['type_id'];
        $where = array();
        $type_id && $where[] = ['type_id','=',$type_id];

        $tempList =db('Config')->where($where)->order('sort asc')->select();
        return $tempList;
    }

    /**
     * 添加配置
     * @author yang
     * Date: 2022/5/22
     */
    public  function add(){
        $data = $this->param['data'];
        if(isset($data['default_val'])){
            $data['value'] = $data['default_val'];
        }
        //验证请求的数据
        $validata = new SettingValidate;
        if(!$validata->check($data)){
            $this->msg = $validata->getError();
            return false;
        }
        if(self::insert($data)) {
            /*更新缓存*/
            $this->updateCache();
            return true;
        }
        return false;
    }


    /**
     * 配置编辑
     *
     * @return bool
     * @author yang
     * Date: 2022/5/22
     */
    public function edit(){
        $data = $this->param['data'];
        $id = $this->param['id'];
        if(isset($data['default_val'])){
            $data['value'] = $data['default_val'];
        }
        $validata = new SettingValidate;
        if(!$validata->scene('edit')->check($data)){
            $this->msg = $validata->getError();
            return false;
        }

        if(db('Config')->where(array('id'=>$id))->update($data)){
            /*更新缓存*/
            $this->updateCache();
            return true;
        }
        return false;
    }

    /**
     * 删除。批量删除和单个删除适用
     * @author yang
     * Date: 2022/5/23
     */
    public function del(){
        $id = $this->param['id'];
        $where[] = ['id','in',$id];
        $re = db('Config')->where($where)->delete();
        if($re){
            /*更新缓存*/
            $this->updateCache();
        }
        return $re;
    }

    /**
     *
     * 網站配置編輯
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/24
     */
    public function settingEdit(){
        $data = $this->param;

        $form_type_arr = [];
        //把每一个配置名称对应相应的表单类型
        $configlist = db('Config')->where('type_id','=',$data['type_id'])->select();
        foreach($configlist as $k=>$v){
            $form_type_arr[$v['name']] = $v['form_type'];
        }
        //开始循环更新
        try{
            foreach($data as $k=>$v){
                if(isset($form_type_arr[$k])){
                    //处理checkbox
                    if($form_type_arr[$k] == 6){
                        $v = implode(',',$v);
                    }

                    db('Config')->where('name','=',$k)->update(['value'=>$v]);
                }
            }
        }catch (Exception $e){
            $this->msg = $e->getMessage();
            return false;
        }
        $this->updateCache();
        return true;
    }


    /**
     * 更新缓存
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/23
     */
    public function updateCache(){
        $sys_config = Db::name('Config')->select();
        $tempname = array_column($sys_config,'name');
        $sys_config =  array_combine($tempname,$sys_config);
        Cache::set('sys_config',$sys_config);
        return $sys_config;
    }
}