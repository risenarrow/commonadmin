<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/28
 * Time: 10:16
 */

namespace app\utilsbox\model;

use app\common\model\FrontModel;
use app\common\utils\File;
use app\common\utils\interfaceClass\SocketRev;
use app\common\utils\Socket;
use app\common\utils\Yutils;
use app\simplebook\model\Project;
use think\Exception;
use  think\facade\Env;
use think\process\Utils;

class Wangpan  extends FrontModel implements SocketRev
{

    private  $socket;
    private  $clients;

    private  $data = [];
    private  $temp_data = [];         //缓存包数据
    private  $key = 0;              //socketID
    private $recive_count = 0;      //接收数据大小
    private  $packHeader;           //包体头部大小
    private  $header;               //头部数据
    private  $body;                 //身体数据
    private  static  $totol_len = 2048;
    private  $buffer;


    /**
     * @param $data         接收的数据   类型为byte数组
     * @param $socket       发送消息的socket
     * @param $clients      连接服务器的socket
     * @author yang
     * Date: 2022/6/30
     */
    //有客户端发消息给服务器
    public function setSocket($buffer,$data,&$socket,&$clients){
        // TODO: Implement firstConnect() method.

        $data = unpack("C*", $data);
        $data = array_merge([],$data);
        $this->socket = $socket;
        $this->clients = $clients;
        $this->buffer = $buffer;
        $this->key = array_search($socket,$this->clients);
        if(!$this->key){
            return ;
        }else{
            $this->temp_data[$this->key] = isset($this->temp_data[$this->key])? $this->temp_data[$this->key] :[];
        }


        //获取包验证是否有头包
        $packH = $this->getPackH($this->temp_data[$this->key]);
        $header = $this->getHeader($buffer, $this->temp_data[$this->key], $packH);
        if(empty($header)){
            $this->temp_data[$this->key]  = array_merge($this->temp_data[$this->key],$data) ;
            $packH = $this->getPackH($this->temp_data[$this->key]);
            $header = $this->getHeader($buffer, $this->temp_data[$this->key], $packH);
            //清空$data
            $data = [];
        }
        if(!empty($header)){
            $flag = true;
            //循环获取上一次请求未处理完数据
            while($flag) {
                //检查是否够8位，不够则和$data结合
                $temp_count = count($this->temp_data[$this->key]);
                if($temp_count < 8 && $temp_count > 0){
                    if(!empty($data)) {
                        $this->temp_data[$this->key] = array_merge($this->temp_data[$this->key], $data);
                        $data = [];
                        continue;
                    }else{
                        break;
                    }
                }
                //不是一个正确的包
                $packagecount = $this->dealPack($this->temp_data[$this->key]);
                if($packagecount === false){
                    break;
                }
                $temp_count = count($this->temp_data[$this->key]);
                //如果不足够一个包，和$data结合
                //empty($this->data)表明$this->temp_data是从$data赋值过来的
                if ($temp_count < $packagecount) {
                    if(!empty($data)){
                        $tmp = array_merge($this->temp_data[$this->key],$data);
                        //清空$data;
                        $data = [];
                        $packagecount = $this->dealPack($tmp);
                        $temp_count =  count($tmp);
                        //结合后的$tmp还是不够一个包
                        $this->temp_data[$this->key] = $tmp;
                        if($temp_count < $packagecount){
                            break;
                        }else{
                            //够一个包继续执行
                            $this->temp_data[$this->key] = $tmp;
                        }
                    }else{
                        //$data为空，跳出循环
                        break;
                    }
                }
                //足够一个包，则输出这个包
                $tmp = array_slice($this->temp_data[$this->key],0,$packagecount);
                $this->temp_data[$this->key]  = array_slice( $this->temp_data[$this->key], $packagecount, $temp_count-$packagecount);
                $res =  $this->beforeRequestMsg($buffer, $tmp);
                //处理结束
                if($res == 1){
                    break;
                }
            }
        }
    }

    /**
     * 获取整个包大小
     * @param $data
     * @return bool|void
     * @author yang
     * Date: 2022/7/2
     */
    public function dealPack(&$data){
        $packH = $this->getPackH($data);
        $header = $this->getHeader($this->buffer,$data,$packH);
        if(!$header){
            return false;
        }
        //头部包完整,获取头一个全部包大小
        $packagecount = $header['pack_len'] + 8 + $packH;

        return $packagecount;
    }


    /**
     * 解包后，格式化请求数据
     * @param $buffer
     * @param $data
     * @author yang
     * Date: 2022/7/6
     */
    public function beforeRequestMsg($buffer,$data){
        if(!empty($data)){
            $this->packHeader  = $this->getPackH($data);
            $this->header = $this->getHeader($buffer, $data,$this->packHeader);
            if (!$this->header) {
                return;
            }
            $this->body = $this->header['pack_len'] > 0 ? $this->getBody($data) : null;
            //请求数据处理
          return   $this->requestMsg();
        }
    }


    /**
     *
     * 第一次连接发送消息
     * @param $socket
     * @author yang
     * Date: 2022/7/1
     */
    public function firstConnect(&$socket)
    {
        // TODO: Implement firstConnect() method.
        $this->socket = $socket;
        $this->send($socket,'你好！',"text");
    }

    /**
     * 发送消息到客户端 统一分配函数
     * @param string $msg
     * @param string $type
     * @param array $param
     * @author yang
     * Date: 2022/7/1
     */
    public function send(&$socket,$msg='',$type='text',$param=[]){
        switch ($type){
            case 'text':
                $this->sendText($socket,$msg,$param);
                break;
            case 'file':
                $this->sendFile($socket,$msg,$param);
        }
    }

    /**
     * 发送文本消息到客户端
     * @param $msg
     * @author yang
     * Date: 2022/7/1
     */
    public function sendText(&$socket,$msg,$param=[]){
        $msg = Yutils::toEncodeUtf8(trim($msg));
        $code = 1;
        if(isset($param['code'])){
            $code = $param['code'];
        }
        $arr = [
            'respond_type'=>'text',
            'code'=>$code
        ];
        $arr['data'] = $msg;
        $json = json_encode($arr);
//        $json_buffer = Yutils::strToBytes($json,0,1024);
//        $strbuffer = Yutils::strToBytes($msg,1024,1024*4);
//        $buffer = $json_buffer+$strbuffer;

        socket_write($socket,$json, strlen($json));
    }

    public function sendFile(&$socket,$msg,$param){
    }





    /**
     * 处理请求 统一分配函数
     * @author yang
     * Date: 2022/7/1
     */
    public function requestMsg(){
        switch ($this->header['request_type']){
            case 'upload_file':
                $re =  $this->request_upload_file();
                break;
        }
        return $re;
    }

    public function getPackCount($bytes){

        $arr = [];
        for($i=0;$i< 4 ; $i++){
            $arr[]  = $bytes[$i];
        }
        $count = Yutils::bytesToInteger($arr);
        return $count;
    }


    public function getPackH($bytes){

        if(empty($bytes) || count($bytes) < 8){return 0;}
        $arr  = [];
        for($i=4;$i< 8 ; $i++){
            $arr[] = $bytes[$i];
        }
        $count = Yutils::bytesToInteger($arr);

        return $count;
    }


    public function getHeader($buffer,$data,$packHeader=0){
        if(!$packHeader||empty($data)){
            return false;
        }

        $str = Yutils::bytesToStr($data,8,$packHeader+8);

        return json_decode($str,true);;
    }

    public function getBody($data){
        $last = count($data) - $this->packHeader-4-4;
        return  array_slice($data,$this->packHeader+8,$last);
    }

    /**
     * 处理上传文件
     * @author yang
     * Date: 2022/7/1
     */
    private function request_upload_file(){
        $param = $this->header;
        $filename = $param['filename'];
        $path = $param['path'];
        $path = $this->getPath($path);;

        if(!empty($this->body) && $param['pack_len'] > 0){
            if(!($user_file_path = $this->getUserFilePath($param['user_id']))){
                $this->send($this->socket,$this->msg,'text',['code'=>1]);
                return 0;
            }
            if(!is_dir($user_file_path.$path)){
                File::createDir($user_file_path.$path);
            }

            $file = fopen($user_file_path.$path.'/'.$filename,'a+');
            fwrite($file,call_user_func_array("pack",array_merge(["C*"],$this->body)));
            fclose($file);
        }else{
            //接收完需要清楚这个socket
            unset($this->temp_data[$this->key]);
            $this->send($this->socket,'上传完毕','text',['code'=>1]);
            return 1;
        }
    }




    /**
     * 创建文件目录
     * @author yang
     * Date: 2022/7/9
     */
    public function createDir(){
        $data = $this->param;
        $path = $data['path'];
        $filename = $data['filename'];

        if(!($user_file_path = $this->getUserFilePath($data['user_id']))){
            return false;
        }
        try{
            $path = $this->getPath($path);;
            $dir = $user_file_path.$path."/".$filename;
            if(!is_dir($dir)){
                if(!File::createDir($dir)){
                    $this->msg = '文件夹创建失败';return false;
                }
                $this->msg = '文件夹创建成功';
                return true;
            }
            return true;
        }catch (Exception $e){
            $this->msg = "文件夹创建失败".$e->getMessage();
            return false;
        }

    }

    /**
     * 改变目录名
     * @author yang
     * Date: 2022/7/9
     */
    public function changeDir(){
        $data = $this->param;
        $path = $data['path'];
        $filename = $data['filename'];
        $oldfilename = $data['oldfilename'];
        if(!($user_file_path = $this->getUserFilePath($data['user_id']))){
            return false;
        }
        $path = $this->getPath($path);;
        $oldpath = $user_file_path.$path."/".$oldfilename;
        $newpath = $user_file_path.$path."/".$filename;

        if(is_dir($newpath) || file_exists($newpath)){
            $this->msg = '文件或文件夹已存在';
            return false;
        }
        try{

            if(!is_dir($oldpath) && !file_exists($oldpath)){
                $this->msg = '文件或文件夹不存在';return false;
            }else{
               if(rename($oldpath,$newpath)){
                   $this->msg = '';
                   return true;
               }
                $this->msg = '修改失败';
               return false;
            }
        }catch (\Exception $e){
            $this->msg = "文件夹创建失败".$e->getMessage();
            return false;
        }
    }


    /**
     * 获取文件列表
     * @return array|bool
     * @author yang
     * Date: 2022/7/15
     */
    public function getlist(){
        $data = $this->param;
        $path = isset($data['path'])?$data['path']:"/";

        if(!($user_file_path = $this->getUserFilePath($data['user_id']))){
            return false;
        }
        $path = $this->getPath($path);
        $aim = $user_file_path.$path;
        if(!is_dir($aim)){
            $this->msg = "目录不存在";return false;
        }
        $list = scandir($aim);
        $data = [];

        foreach ($list as  $key=>$li){

            if($li == '.' || $li == '..')
            {  unset($list[$key]);continue;}
            $aimfile  = $aim."/".$li;
            $type = '文件夹';
            if(is_file($aimfile)){
                $type = "文件";
            }
            $data[] = [
                'filename'=>$li,
                "datetime"=>date("Y-m-d H:i:s",filemtime($aimfile)),
                "type"=>$type,
                'size'=>floor(filesize($aimfile)/1024)." KB"
                ];
        }
        return $data;
    }

    public function delDir(){
        $data = $this->param;
        if(!($user_file_path = $this->getUserFilePath($data['user_id']))){
            return false;
        }
         $path = $this->getPath($data['path']);;
        $filenames = $data['paths'];
        $paths = explode(",",$filenames);

        foreach ($paths as $key=>$v){
            $p = $user_file_path.$path."/".$v;
            if(is_dir($p)){
                File::deldir($p);
            }
            if(file_exists($p)){
                File::delfile($p);
            }
        }
        $this->msg ="删除成功";
        return true;
    }

    public function getPath($path = ''){
        $path = "/".ltrim($path,'/');
        return $path;
    }
    public function getUserFilePath($user_id = 0){
        if(!$user_id){
            $this->msg  =  "请先登录";return false;
        }
        $file_root_path =config('upload_file_path');
        $user_file_path = $file_root_path."/".$user_id;
        return $user_file_path;
    }
}