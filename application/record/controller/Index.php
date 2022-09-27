<?php
namespace app\record\controller;

use app\common\controller\FrontBase;

use app\common\utils\Yutils;
use app\record\model\Record;
use app\record\model\RecordMember;
use think\App;
use app\common\utils\Upload;
use think\facade\Cache;
use think\Request;

class Index extends FrontBase
{
    function __construct(App $app = null)
    {

        parent::__construct($app);
        $this->set = new Record();
    }

     public function index(){
         $this->set->setParam($this->request->param());
         $list = $this->set->getRecordList();
         $this->echoJson('',0,$list['count'],$list['data']);
    }

    public function upload(){
        $files = $this->request->file();
        $upload = new Upload();
        $upload->setDirName("record");
        if(count($files)){
            if($upload->upload($files)){
                $this->echoJson("上传成功",0,0,['src'=>$upload->getPath()]);
            }else{
                $this->echoJson($upload->getMsg(),1,0,[]);
            }
        }else{
            $upload->uploadMulti($files);
        }
    }

    public function add(){
        $this->set->setParam($this->request->param());
        $id = $this->set->add();
        if($id!==false){
            $this->echoJson($this->set->getMsg(),0,0);
        }
        $this->echoJson($this->set->getMsg(),1);
    }

    public function del(){
        $this->set->setParam($this->request->param());
        $id = $this->set->del();
        if($id!==false){
            $this->echoJson($this->set->getMsg(),0,0);
        }
        $this->echoJson($this->set->getMsg(),1);
    }
}