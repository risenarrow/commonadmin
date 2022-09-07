<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/10
 * Time: 14:50
 */

namespace app\gotochat\controller;


use app\common\controller\FrontBase;
use app\common\utils\Yutils;
use app\gotochat\model\Area;
use app\gotochat\model\UserModel;
use think\App;
use app\common\utils\Upload;
use think\Env;
use app\gotochat\model\GroupModel;


class Index  extends FrontBase
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->set = new UserModel();
    }

    public function checklogin(){
        $re = UserModel::checkLogin($this->request->header('userid'),$this->request->header('authtoken'));
        if($re !==  false){
            $this->echoJson("你已登录",1,0);
        }
        $this->echoJson("",0);
    }

    public function login(){
        $this->set->setParam(['data'=>$this->request->param(),'header'=>$this->request->header()]);
        $re = $this->set->login();
        if($re !==  false){
            $this->echoJson($this->set->getMsg(),0,0,$re);
        }
        $this->echoJson($this->set->getMsg(),1);
    }
    public function register(){
        $this->set->setParam(['data'=>$this->request->param(),'header'=>$this->request->header()]);
        $re = $this->set->register();
        if($re !==  false){
            $this->echoJson($this->set->getMsg(),0,0,$re);
        }
        $this->echoJson($this->set->getMsg(),1);
    }

    public function sendcode(){
        $this->set->setParam($this->request->param());
        $re = $this->set->sendcode();
        if($re !==  false){
            $this->echoJson($this->set->getMsg(),0,0);
        }
        $this->echoJson($this->set->getMsg(),1);
    }

    public function getProvince(){
        $area = new Area();
        $area->setParam($this->request->param());
        if($re = $area->getProvince()){
            $this->echoJson($area->getMsg(),0,0,$re);
        }
        $this->echoJson($area->getMsg(),1,0);
    }


    public function getCity(){
        $area = new Area();
        $area->setParam($this->request->param());
        if($re = $area->getCity()){
            $this->echoJson($area->getMsg(),0,0,$re);
        }
        $this->echoJson($area->getMsg(),1,0);
    }

    public function uploadmsgfile(){
        $files = $this->request->file();
        $data = $this->request->param();
        $upload = new Upload();
        if($data['msg_type'] ==3){
            $upload->setDirName("gotochat/yuyin");
        }elseif($data['msg_type'] ==1){
            $upload->setDirName("gotochat/image");
        }elseif($data['msg_type'] ==2){
            $upload->setDirName("gotochat/video");
        }

        if($files){
            if($upload->upload($files)){
                /*如果是视频，截取封面图*/
                Yutils::getVideoCover("./".$upload->getPath(),"./".preg_replace("/\.([\s\S]+)/",'.jpg',$upload->getPath()));
                $this->echoJson("上传成功",0,0,['src'=>$upload->getPath()]);
            }else{
                $this->echoJson($upload->getMsg(),1,0,[]);
            }
        }else{
            $upload->uploadMulti($files);
        }
    }



}