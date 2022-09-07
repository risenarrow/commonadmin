<?php
namespace app\admin\controller;
use app\admin\model\Setting as SettingModel;
use think\App;
use think\facade\Config;
use think\facade\Env;


class Setting extends AdminBase
{

    public $set;
    function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->set = new SettingModel();
    }

    /*设置表单*/
    public function config()
    {

        $type_id = input('type_id',1,'intval');

        $this->set->setParam(['type_id'=>$type_id]);
        //获取数据
        $configList = $this->set->configList(0);
        //渲染模板
        $html = '';
        $form_type = [1=>'char',2=>'number',3=>'textarea',4=>'photo',5=>'radio',6=>'checkbox',7=>'editarea',8=>'file'];
        $view_path = 'public/form/';
        foreach($configList as $k=>$v){

            if($v['form_type'] == 5||$v['form_type'] == 6){
                    /*取出配置项，格式化为数组*/
                    $list = explode("\n",$v['config_item']);
                    foreach($list as $key=>$val){
                        $list[$key] = explode('|',$val);
                        /*获取默认值，使第三个元素变为1*/
                        if($v['form_type'] == 6){
                            $v['arr_value'] =  explode(",",$v['value']);

//                            if(isset($v['value'])){
//                                $checkVal = explode(",",$v['value']);
//                                $list[$key][2] = in_array($list[$key][0],$checkVal)?1:0;
//                            }
//                            else{
//                                $checkVal = explode(",",$v['default_val']);
//                                $list[$key][2] = in_array($list[$key][0],$checkVal)?1:0;
//                            }
                        }elseif($v['form_type'] == 5){
//                            if(isset($v['value'])){
//                                $list[$key][2] = $v['value']==$list[$key][0]?1:0;
//                            }
//                            else{
//                                $list[$key][2] = $v['default_val']==$list[$key][0]?1:0;
//                            }
                        }
                    }

                    $v['list_arr'] = $list;

            }
            $v['label'] = $v['title'];
            $v['placeholder'] = '请填写'.$v['title'];

            $html .= $this->fetch($view_path.$form_type[$v['form_type']], $v)->getContent();
        }


        $this->assign([
            'configType'=>Config::get('config_type'),
            'type_id'=>$type_id,
            'configList'=>$configList,
            'html'=>$html
        ]);
        return $this->fetch();
    }

    /*保存设置*/
    public function settingEdit(){
        if($this->request->isAjax()){
            $data = input();
            $this->set->setParam($data);
            if( $this->set->settingEdit())
                $this->success('保存成功',url('admin/setting/config',array('type_id'=>$data['type_id'])));
            else
                $this->error('保存失败,'.$this->set->getMsg());
        }
    }


    public function index()
    {
        $type_id = input('type_id',0,'intval');

        if($this->request->isAjax()){
            //获取参数传到model
            $this->set->setParam(
                [
                    'type_id'=>$type_id,
                    'limit'=>input('limit',10,'intval')
                ]
            );
            //获取列表
            $configList =$this->set->configList(1);
            $this->echoJson(0,$configList['count'],$configList['data']);
        }

        $this->assign([
            'configType'=>config('config_type'),
            'type_id'=>$type_id,
            'type'=> config('form_type'),
        ]);

        return $this->fetch();
    }

    /**
     * 前台添加操作函数
     * @return int|mixed
     * @author yang
     * Date: 2022/5/23
     */
    public function add(){
        $id = input('id',0,'intval');
        if($this->request->isAjax()){
            $data = input();
            //传递参数给model
            $this->set->setParam([
                'id'=>$id,
                'data'=>$data
            ]);
            //添加数据
            if($this->set->add()){
                $this->success('添加成功');
            }else{
                $this->error($this->set->getMsg());
            }
            return 1;
        }
        $configTypes = Config::get('config_type');
        $form_type = Config::get('form_type');
        $data = SettingModel::find($id);
        $this->assign([
            'configTypes'=>$configTypes,
            'form_type'=>$form_type,
            'data'=>$data
        ]);
        return $this->fetch();
    }

    /**
     * 前台编辑操作函数
     * @return int|mixed
     * @author yang
     * Date: 2022/5/23
     */
    public function edit(){
        $id = input('id',0,'intval');
        if($this->request->isAjax()){
            $data = input();
            //传递参数给model
            $this->set->setParam([
                'id'=>$id,
                'data'=>$data
            ]);
            //修改数据
            if($this->set->edit()){
                $this->success('编辑成功');
            }else{
                $this->error($this->set->getMsg());
            }
            return 1;
        }
        $configTypes = Config::get('config_type');
        $form_type = Config::get('form_type');
        $data = SettingModel::find($id);
        $this->assign([
            'configTypes'=>$configTypes,
            'form_type'=>$form_type,
            'data'=>$data
        ]);
        return $this->fetch();
    }

    public function del(){
        $id=input('ids');
        $this->set->setParam([
            'id'=>$id
        ]);
        if($this->set->del()){

            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}
