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

class Wangpan  extends FrontModel implements SocketRev
{

    private  $socket;
    private  $clients;

    private  $data = [];
    private  $temp_data = [];
    private $last_data = [];
    private  $packCount;
    private  $packHeader;
    private  $header;
    private  $body;



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
            $this->packCount  = $this->getPackCount($data);
            if(count($data) < $this->packCount+4) {
                $this->temp_data = array_merge($this->temp_data,$data);
                $temp_len = count($this->temp_data );
                if($temp_len>$this->packCount+4){
                    $this->last_data = array_slice($this->temp_data,$this->packCount+4-1,$temp_len-);
                }
            }
            if(count() > $this->packCount + 4){

            }

            if(count($this->temp_data) == $this->packCount+4){
                $this->packCount = 0;

                $this->socket = $socket;
                $this->clients = $clients;
                $this->data = $this->temp_data;
                $this->temp_data = [];
                //解析$data

                $this->header = $this->getHeader($buffer, $data);


                if (!$this->header) {
                    return;
                }
                $this->body = $this->header['pack_len'] > 0 ? $this->getBody($data) : null;

                //接受消息后的业务逻辑
                $this->requestMsg();
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
        $this->send('你好！',"text");
    }

    /**
     * 发送消息到客户端 统一分配函数
     * @param string $msg
     * @param string $type
     * @param array $param
     * @author yang
     * Date: 2022/7/1
     */
    public function send($msg='',$type='text',$param=[]){
        switch ($type){
            case 'text':
                $this->sendText($msg,$param);
                break;
            case 'file':
                $this->sendFile($msg,$param);
        }
    }

    /**
     * 发送文本消息到客户端
     * @param $msg
     * @author yang
     * Date: 2022/7/1
     */
    public function sendText($msg,$param=[]){
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

        socket_write($this->socket,$json, strlen($json));
    }

    public function sendFile($msg,$param){
    }


    /**
     * 处理请求 统一分配函数
     * @author yang
     * Date: 2022/7/1
     */
    public function requestMsg(){
        switch ($this->header['request_type']){
            case 'upload_file':
                $this->request_upload_file();
                break;
        }
    }

    /**
     * 处理上传文件
     * @author yang
     * Date: 2022/7/1
     */
    private function request_upload_file(){
        $param = $this->header;
        $user_id = $param['user_id'];
        $filename = $param['filename'];
        $file_root_path =config('upload_file_path');

        if(!empty($this->body) && $param['pack_len'] > 0){
            $user_file_path = $file_root_path."/".$user_id;
            if(!is_dir($user_file_path)){
                File::createDir($user_file_path);
            }

            $file = fopen($file_root_path."/".$user_id.'/'.$filename,'a+');
            fwrite($file,call_user_func_array("pack",array_merge(["C*"],$this->body)));
            fclose($file);
        }else{
            $this->send('上传完毕','text',['code'=>1]);
        }
    }

    /**
     * 获取包头
     * @param $bytes
     * @return int
     * @author yang
     * Date: 2022/7/2
     */
    public function getPackCount($bytes){
        $count = 0;
        for($i=1;$i< 5 ; $i++){
            $count += $bytes[$i];
        }
        return $count;
    }

    /**
     * 获取协议头
     * @param $bytes
     * @return int
     * @author yang
     * Date: 2022/7/2
     */
    public function getPackH($bytes){

        $count = 0;
        for($i=5;$i< 9 ; $i++){
            $count += $bytes[$i];
        }

        $this->packHeader = $count;
        return $count;
    }


    public function getHeader($buffer,$data){

        $header_count = $this->getPackH($data);

        if(!$header_count){
            return false;
        }

        $str = Yutils::bytesToStr($data,9,$header_count+9);

        return json_decode($str,true);;
    }

    public function getBody($data){
        $bodylen = $this->header['pack_len']+$this->packHeader+9;
        $body = array_slice($data,$this->packHeader+8,$bodylen);
        Yutils::Log(var_export($this->header, true) . "----------------------------\r\n" . "\r\n" . "\r\n" . "\r\n");
      return  $body;
    }
}