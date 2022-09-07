<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/12
 * Time: 23:28
 */

namespace app\extend\behavior;
use app\common\utils\Yutils;
use think\facade\App;
use think\facade\Config;
use think\facade\Env;
use think\facade\Request;
use think\facade\Route;
class ChangeConf
{
    /**
     * @author yang
     * Date: 2019/11/12
     */
    public function appInit($params){

        $pathInfo =  trim(Request::server('PATH_INFO'));

        if(defined('MODULE_MARK') && MODULE_MARK == 'admin'){
            Route::rule('','admin/index/index');
            $pathInfo = substr($pathInfo,1,strlen($pathInfo));
            $arrPath = explode('/', $pathInfo);
            if($arrPath[0] != 'admin' && $arrPath[0]){
                config('url_controller_layer','admin\controller');
                config('template.view_path',App::getModulePath() .$arrPath[0].DIRECTORY_SEPARATOR. 'admin'.DIRECTORY_SEPARATOR.'view' . DIRECTORY_SEPARATOR);
            }
        }
        if(defined('MODULE_MARK')&&MODULE_MARK == 'index'){
            $default_module = Yutils::getSysConfig("DEFAULT_MODULE");
            if(config('default_module') != $default_module){
                $file = include(Env::get('root_path')."/config/cover.php");
                $file['default_module'] = $default_module?$default_module:'index';
                file_put_contents(Env::get('root_path')."/config/cover.php","<?php return ".var_export($file,true).";?>");
            }
        }
    }

    public function moduleInit($params){


    }
}