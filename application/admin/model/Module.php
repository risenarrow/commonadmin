<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/5/31
 * Time: 16:08
 */

namespace app\admin\model;
use app\admin\model\PublicModel;
use think\Db;
use think\facade\Env;
use app\admin\model\User as UserModel;
use app\common\utils\Yutils;
use app\common\utils\File;
use think\facade\Response;

class Module extends PublicModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__MODULE__';
    private $path ='';


    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->path = Env::get('app_path');
    }



    /**
     *
     * 获取模块列表
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/31
     */
    public function getModuleList(){
        //读取已安装模块数据库
        $list = $this->field('author,des,module_name,module_title,status')->select();
        $list = $list->toArray();
        $list = array_combine(array_column($list,'module_name'),$list);
        //读取未安装模块
        $dir_list = @scandir( $this->path);

        foreach ($dir_list as $k=>$v){
            if($v == '.' || $v == '..'){
                unset($dir_list[$k]);
            }else{
                $install_path = $this->path.DIRECTORY_SEPARATOR.$v.DIRECTORY_SEPARATOR.'install';
                $install_config_path = $install_path.DIRECTORY_SEPARATOR.'config.php';
                if(@file_exists($install_path)){
                    if(in_array($v,array_keys($list))){
                        $list[$v]['install'] = 1;
                    }else{
                        $list[$v]['install'] = 0;
                    }
                    if(@file_exists($install_config_path)){
                        $config = include  $install_config_path;
                        $list[$v]['module_title'] = $config['module_title'];
                        $list[$v]['author'] = $config['author'];
                        $list[$v]['module_name'] = $config['module_name'];
                        $list[$v]['des'] = $config['des'];
                    }


                }
            }
        }
        return $list;
    }




    /**
     *
     * 改变模块状态
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author yang
     * Date: 2022/5/31
     */
    public function changeStatus(){
        $data = $this->param;
        $validate = new \app\admin\validate\Module();
        if(!$validate->scene('status')->check($data)){
            $this->msg = $validate->getError();
            return false;
        }
        $info = self::getModuleInfoByModuleName($data['module_name']);
        if(!$info){
            $this->msg = '模块不存在或没安装';
            return false;
        }
        if($this->where('module_name','=',$data['module_name'])
            ->update(['status'=>$info['status'] == 1?0:1])){
            $this->msg = '修改成功';
            return true;
        }
        $this->msg = '修改失败';
        return false;
    }


    /**
     *
     * 根据模块标识获取模块信息
     * @param string $module_name
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/31
     */
    public static function  getModuleInfoByModuleName($module_name=''){
        $info = self::where('module_name','=',$module_name)->find();
        return $info;
    }


    /**
     *
     * 获取模块当前状态
     * @param string $module_name
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/5/31
     */
    public static function  checkModuleStatus($module_name=''){
        $info = self::getModuleInfoByModuleName($module_name);
        if($info){
            return $info['status'];
        }
        return false;
    }


    /**
     *
     * 模块安装
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/6/1
     */

    public function install(){
        $data= $this->param;
        $validate = new \app\admin\validate\Module();
//        if(!$validate->scene('status')->check($data)){
//            $this->msg = $validate->getError();return false;
//        }
//        $info = self::where('module_name','=',$data['module_name'])->find();
//        if($info){
//            $this->msg = '模块已安装！';
//            return false;
//        }
        //安装文件路径
        $install_path = $this->path.DIRECTORY_SEPARATOR.$data['module_name'].DIRECTORY_SEPARATOR.'install';
        //安装配置路径
        $install_config_path =   $install_path.DIRECTORY_SEPARATOR.'config.php';
        if(!@file_exists($install_config_path)){
            $this->msg = '配置文件不存在';return false;
        }

            $config = include $install_config_path;
            $insert = [
                'module_title'=>$config['module_title'],
                'module_name'=>$config['module_name'],
                'author'=>$config['author'],
                'des'=>$config['des'],
                'add_time'=>time()
            ];

            //检验模块信息正确性
            if(!$validate->check($insert)){
                $this->msg = $validate->getError();return false;
            }

            //安装静态资源路径
            $static_path = $install_path.DIRECTORY_SEPARATOR.$config['static'];
            //安装菜单路径
            $menu_path = $install_path.DIRECTORY_SEPARATOR.'menu.php';
            //sql文件安装路径
            $sql_path = $install_path.DIRECTORY_SEPARATOR.'install.sql';
            if(@file_exists($static_path)){                 //复制静态资源到public下的static并改名为模块标识
                try{
                    Yutils::copyFile($static_path,Env::get('ROOT_PATH').'/public/static/'.$data['module_name'],1);
                }catch (\Exception $e){
                    $this->msg=$e->getMessage();
                    return false;
                }
            }

            $menu = [];

            if(@file_exists($menu_path)){
                $menu = include  $menu_path;                        //获取菜单
            }
            if(@file_exists($sql_path)){
                $sql = file_get_contents($sql_path);
                $sql = str_replace("\r","",$sql);

                preg_match_all("/CREATE TABLE[\s\S]+?;\n/",$sql,$matches);  //创建表的sqL语句
                preg_match_all("/INSERT INTO[\s\S]+?;/",$sql,$matches1);     //插入表数据
            }
            Db::startTrans();
            try{
                $re1 = self::insert($insert);
                if(!empty($menu)){
                    $this->creatMenu($menu);        //创建菜单
                }
                if(isset($matches[0])){
                    foreach($matches[0] as $k=>$v){
                        $flag = Db::execute($v);        //创建表
                    }
                }
                if(isset($matches1[0])){
                    foreach($matches1[0] as $k=>$v){
                        $flag1 = Db::execute($v);        //插入表数据
                    }
                }
                if($re1){
                    Db::commit();
                    UserModel::admin_log(session('admin_id'),2,session('admin_name'),'安装'.$data['module_name'].'模块');
                    $this->msg = '安装成功!';return true;
                }else{
                    Db::rollback();
                   $this->msg = '安装失败!';return false;
                }
            }catch (\Exception $e){
                Db::rollback();
                $this->msg = $e->getMessage();

                return false;
            }
    }


    /**
     * 根据menu.php来创建后台菜单
     * @param array $menu
     * @param int $parentid
     * @author yang
     * Date: 2022/6/1
     */
    private function creatMenu($menu = [],$parentid=0){
        foreach($menu as $k=>$v){
            $insert = [
                'parentid'=>$parentid,
                'name'=>$v['name'],
                'm'=>$v['m'],
                'c'=>$v['c'],
                'a'=>$v['a'],
                'show'=>$v['show'],
            ];
            $id = Menu::insert($insert,false,true);
            if(!empty($v['sub'])&&$id){
                $this->creatMenu($v['sub'],$id);
            }
        }
    }

    public function uninstall(){
        $data = $this->param;
        $validate = new \app\admin\validate\Module();
        if(!$validate->scene('status')->check($data)){
            $this->msg = $validate->getError();return false;
        }
        $info = self::where('module_name','=',$data['module_name'])->find();
        if(!$info){
            $this->msg = '模块未安装！';
            return false;
        }

        //安装文件路径
        $install_path = $this->path.DIRECTORY_SEPARATOR.$data['module_name'].DIRECTORY_SEPARATOR.'install';
        //sql文件安装路径
        $sql_path = $install_path.DIRECTORY_SEPARATOR.'install.sql';

        if(@file_exists($sql_path)){
            $sql = file_get_contents($sql_path);
            $sql = str_replace("\r","",$sql);
            preg_match_all("/CREATE TABLE\s+`(.*?)`/",$sql,$matches);  //通过sqL语句，获取要删除的表
        }
        Db::startTrans();
        try{
            $re1 = Menu::where('m','=',$data['module_name'])->delete();              //删菜单
            $re2 = Module::where('module_name','=',$data['module_name'])->delete();       //删除模块记录
            $re3 = AdminRolePriv::where('m','=',$data['module_name'])->delete();
            if(isset($matches[1])){
                foreach($matches[1] as $k=>$v){
                    Db::execute('DROP TABLE IF EXISTS `'.$v.'`');                   //删除该模块下的表
                }
            }

            if($re2){
                Db::commit();
                UserModel::admin_log(session('admin_id'),2,session('admin_name'),'卸载'.$data['module_name'].'模块');
                $this->msg = '卸载成功';return true;
            }else{
                Db::rollback();
                $this->msg = '卸载失败';return false;
            }
        }catch (\Exception $e){
            Db::rollback();
            $this->msg = $e->getMessage();
        }


    }


    /**
     *
     * 获取当前模块状态
     * @param string $curModule
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/6/5
     */
    public static function getModuleStatus($curModule = ''){
        //白名单
        $white = ['admin'];
        if(in_array($curModule,$white)){
            return true;
        }
        $status = self::checkModuleStatus($curModule);
        if(!$status){
            return false;
        }
        return true;
    }

    /**
     *
     * 添加模块
     * @return bool
     * @author yang
     * Date: 2022/6/8
     */

    public function  add(){
        $data = $this->param;
        $validate = new \app\admin\validate\Module();

        if(!$validate->check($data)){
            $this->msg = $validate->getError(); return false;
        }

        $app_path = Env::get('app_path');
        $module_path = $app_path.DIRECTORY_SEPARATOR.$data['module_name'];
        //要创建的目录
        $path = [
            'admin/controller',
            'admin/model',
            'admin/validate',
            'admin/view/index',
            'config',
            'controller',
            'install/static',
            'model',
            'view/index',
        ];
        //要创建的文件
        $File = [
            'admin/controller/Index.php',
            'admin/view/index/index.html',
            'controller/Index.php',
            'install/config.php',
            'install/menu.php',
            'install/install.sql',
            'view/index/index.html'
        ];
        //要写入的内容
        $content= [
            'admin/controller/Index.php' =>"<?php\nnamespace app\\".$data['module_name']."\\admin\\controller;\n\nuse app\\admin\\controller\\AdminBase;\n\nclass Index extends AdminBase\n{\n public function index(){ \n return \$this->fetch(); \n}\n}",
            'admin/view/index/index.html'=>"{extend name='admin@public/layout' /}",
            'controller/Index.php'=>"<?php\nnamespace app\\".$data['module_name']."\\controller;\n\nuse app\\common\\controller\\FrontBase;\n\nclass Index extends FrontBase\n{\n public function index(){ \n return \$this->fetch(); \n} \n}",
            'install/config.php'=>"<?php return ".var_export([
                    'module_title'=>$data['module_title'],
                    'module_name'=>$data['module_name'],
                    'author'=>$data['author'],
                    'des'=>$data['des'],
                    'static'=>'static',
                    'sql'=>'install.sql',
                    'menu'=>'menu.php'
                ],true)."; ?>",
            'install/menu.php'=>"<?php return ".var_export([
                    'top'=>[
                        'name'=>$data['module_title'],
                        'm'=>$data['module_name'],
                        'c'=>'Index',
                        'a'=>'index',
                        'show'=>1
                    ]
                ],true)."; ?>",
            'view/index/index.html'=>"<div stype='font-size:50px'>hello world</div>"
        ];


        $insert = [
            'module_title'=>$data['module_title'],
            'module_name'=>$data['module_name'],
            'author'=>$data['author'],
            'des'=>$data['des'],
            'add_time'=>time()
        ];

        try {


                //生成目录
                File::createDir($data['module_name'],$app_path);
                foreach ($path as $key=>$value){
                    File::createDir($value,$module_path);
                }
                //生成文件及插入内容
                foreach ($File as $key=>$value) {

                    if (isset($content[$value])) {
                        File::createFile($value, $module_path . DIRECTORY_SEPARATOR, $content[$value]);
                    }else{
                        File::createFile($value, $module_path . DIRECTORY_SEPARATOR);
                    }


                }
            $this->msg = '添加成功！';return true;

        }catch (\Exception $e){

            //删除刚刚生成的文件及文件夹
            File::deldir($module_path);
            $this->msg = $e->getMessage();return false;
        }
    }



}