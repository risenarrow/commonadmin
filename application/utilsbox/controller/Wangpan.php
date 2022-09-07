<?php
namespace app\utilsbox\controller;

use app\common\controller\FrontBase;
use app\common\utils\Socket;
use app\common\utils\Yutils;
use think\App;
use think\Exception;
use think\facade\Env;
use app\common\utils\Socket\SocketService;

class Wangpan extends FrontBase
{
    private $set;

     public function __construct(App $app = null)
     {
         parent::__construct($app);
         $this->set = new \app\utilsbox\model\Wangpan();
     }

    public function index(){
         $file = Env::get('ROOT_PATH').'/public/static/js/utils.js';
         return $this->fetch();
    }

    public function get_start_socket(){
        try{
            $this->wangpan = new \app\utilsbox\model\Wangpan();
            $socket  = new SocketService(config('socket_host'), config('socket_port'),$this->wangpan);
            $socket->run();
        }catch (Exception $e){
          echo $e->getMessage();
       }
    }

    /**
     * 客户端新建文件夹
     * @author yang
     * Date: 2022/7/15
     */
    public function createDir(){
        $this->set->setParam($this->request->param());
        if($this->set->createDir()){
            $this->echoJson($this->set->getMsg(),0);
        }else{
            $this->echoJson($this->set->getMsg(),1);
        }
    }

    /**
     * 客户端修改文件夹名称
     * @author yang
     * Date: 2022/7/15
     */
    public function changeDir(){
        $this->set->setParam($this->request->param());
        if($this->set->changeDir()){
            $this->echoJson($this->set->getMsg(),0);
        }else{
            $this->echoJson($this->set->getMsg(),1);
        }
    }

    /**
     * 获取列表
     * @author yang
     * Date: 2022/7/15
     */
    public function getlist(){
        $this->set->setParam($this->request->param());
        if(($data = $this->set->getlist())!== false){
            $this->echoJson($this->set->getMsg(),0,count($data),$data);
        }else{
            $this->echoJson($this->set->getMsg(),1);
        }
    }


    /**
     * 删除目录
     * @author yang
     * Date: 2022/7/16
     */
    public function delDir(){
        $this->set->setParam($this->request->param());
        if(($data = $this->set->delDir())!== false){
            $this->echoJson($this->set->getMsg(),0);
        }else{
            $this->echoJson($this->set->getMsg(),1);
        }
    }


    public function moveDir(){
        $this->set->setParam($this->request->param());
        if(($data=$this->set->moveDir())!== false){
            $this->echoJson($this->set->getMsg(),0);
        }else{
            $this->echoJson($this->set->getMsg(),1);
        }
    }

}