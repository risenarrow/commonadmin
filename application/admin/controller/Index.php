<?php

/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2019/11/14
 * Time: 21:42
 */

namespace app\admin\controller;
use app\admin\controller\AdminBase;
use app\common\utils\Upload;
use think\facade\Env;
class Index extends AdminBase
{
    public function index(){
        return $this->fetch();
    }
    public function clear(){
        delFileByDir(Env::get('root_path').'runtime'.'/');
        $this->success('清除缓存成功');
    }

    /**
     * 上传文件
     * @author yang
     * Date: 2022/5/24
     */
    public function upload(){
        $files = $this->request->file();

        $upload = new Upload();
       if(count($files)){
           if($upload->upload($files)){
               $this->echoJson(0,1,['src'=>$upload->getPath()]);
           }else{
               $this->echoJson(1,1,[],$upload->getMsg());
           }
       }else{
           $upload->uploadMulti($files);
       }

    }
}