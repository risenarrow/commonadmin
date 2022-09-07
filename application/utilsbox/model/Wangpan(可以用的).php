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
use  think\facade\Env;
class Wangpan  extends FrontModel implements SocketRev
{

    private  $socket;
    private  $clients;

    private  $data = [];               //缓存包数据
    private  $temp_data = [];
    private  $temp_count = 0;       //剩余未处理数据大小
    private $recive_count = 0;      //接收数据大小
    private  $packCount = 0;        //包体大小
    private  $packHeader;           //包体头部大小
    private  $header;               //头部数据
    private  $body;                 //身体数据
    private  static  $totol_len = 2048;


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
        $this->temp_data = $this->data;
    //    Yutils::Log(var_export($data,true)."\r\n"."\r\n",Env::get('ROOT_PATH').'/runtime/mylog/111.txt');

        //循环获取上一次请求未处理完数据
        $temp_count = count($this->data);
        while($temp_count >= self::$totol_len){
            //先把上次剩余的数据清空，操作temp_data就可以，当temp_count< self::$total_len,$this->data即为下一次处理包操作的$this->data;
            $this->data = [];
            $tmp = $this->temp_data;
            //处理这批数据，因为dealPack有截取数据长度的操作，因此知道$tmp长度即可计算剩余$this->temp_data
            $result = $this->dealPack( $tmp);
            $tcount = count($tmp);
            $temp_count -=  $tcount;
            $this->temp_data = array_slice($this->temp_data,$tcount,$temp_count);

            $result && $this->beforeRequestMsg($buffer,$tmp);
        }
        //解包这一次请求数据
        $result = $this->dealPack($data);
        $result && $this->beforeRequestMsg($buffer,$data);

        //判断最后一个包
        if($this->data){
            $packH = $this->getPackH($this->data);
            $header = $this->getHeader($buffer, $this->data,$packH);
            if(!empty($header)&&$this->header&& $this->header['pack_len'] != 0){

                $this->temp_data = $this->data;
                $flag = true;
                while($flag){

                    $this->data = [];
                    $packH = $this->getPackH($this->temp_data);
                    $header = $this->getHeader($buffer,$this->temp_data,$packH);
                    if(!$header){
                        $this->data = $this->temp_data;
                        break;
                    }
                    //获取头一个包大小
                    $packagecount = $header['pack_len']+8+$packH;

                    //如果不足够一个包，则返回$this->data
                    $temp_count = count($this->temp_data);
                    if($temp_count < $packagecount){
                        $this->data = $this->temp_data;
                        break;
                    }
                    //截取这个包
                    $tmp = array_slice($this->temp_data,0,$packagecount);
                    $this->temp_data = array_slice($this->temp_data,$packagecount,$temp_count-$packagecount);

                    //解包最后请求数据,也有可能使请求中途的数据包，不一定是最后的数据包
                    $result = $this->dealPack($tmp);
                    $result&&$this->beforeRequestMsg($buffer,$tmp);

                    if(empty($this->temp_data) || $this->header['pack_len'] === 0){
                        break;
                    }
                }
            }
        }

    }

    /**
     * 包处理函数
     * @param $data
     * @return bool|void
     * @author yang
     * Date: 2022/7/2
     */
    public function dealPack(&$data){
        $flag = false;   //标记是否新包操作
        $test = '';
        //解析$data
        if(!empty($this->data)){
            if(count($this->data) < 4){
                $this->data =array_merge($this->data,$data);
                if(count($this->data) >= 4){
                    $data = $this->data;
                    $this->packCount = $this->getPackCount($this->data);
                }

            }else{
               // Yutils::Log(var_export($this->data,true)."\r\n"."\r\n",Env::get('ROOT_PATH').'/runtime/mylog/222.txt');
                //剩余数据缓冲区大小
                $thisdata_count = count($this->data);
                $this->packCount = $this->getPackCount($this->data);
                if($thisdata_count > $this->packCount+4){
                    $data = array_merge($this->data,$data);
                    $this->data = [];
                }else{
                    $les = $this->packCount+4- $thisdata_count;
                    $now = count($data);
                    if($now < $les){
                        $test.='/$now<$len';
                        $this->data = $data= array_merge($this->data,$data);
                       // Yutils::Log(($this->packCount+4)."\r\n".var_export($data,true)."\r\n"."\r\n",Env::get('ROOT_PATH').'/runtime/mylog/555.txt');

                        $flag = false;
                    }else if($now == $les){
                        $test.='/$now == $les';
                        $data = array_merge($this->data,$data);
                       // Yutils::Log(($this->packCount+4)."\r\n".var_export($data,true)."\r\n"."\r\n",Env::get('ROOT_PATH').'/runtime/mylog/444.txt');

                        $this->data = [];
                        $flag = true;
                    }else{
                        $test.='/$now > $les';
                        //截取一部分
                        $tmpdata = array_slice($data, 0, $les);
                        $tdata = array_merge($this->data,$tmpdata);
                        $this->data = array_slice($data, $les, $now-$les);
                        $data = $tdata;
                      //  Yutils::Log("count(data):".count($this->data)."packcount:".($this->packCount+4)."lens:".$les."now:".$now."\r\n".var_export($tdata,true)."\r\n"."\r\n",Env::get('ROOT_PATH').'/runtime/mylog/333.txt');

                    }
                }
               // Yutils::Log("count(data):".$thisdata_count."packcount:".($this->packCount+4)."lens:".(isset($les)?$les:"null")."now:".(isset($now)?$now:"null")."\r\n",Env::get('ROOT_PATH').'/runtime/mylog/tmpdata.txt');

            }
           // Yutils::Log($test."\r\n"."\r\n",Env::get('ROOT_PATH').'/runtime/mylog/test.txt');
        }
        $count_data = count($data);

        //获取包头
        if(empty($this->data) || $this->packCount+4 == $count_data){

            $this->packCount = $this->getPackCount($data);
            //Yutils::Log(($this->packCount+4)."\r\n"."\r\n",Env::get('ROOT_PATH').'/runtime/mylog/final.txt');

            //Yutils::Log(var_export($data,true)."\r\n"."\r\n",Env::get('ROOT_PATH').'/runtime/mylog/final.txt');

            if($count_data  < $this->packCount+4){
                $this->data = $data;
                $flag = false;
            }else if( $count_data> $this->packCount+4 ){
                $newdata = array_slice($data, 0, $this->packCount+4 );
                $this->data = array_slice($data, $this->packCount+4 , $count_data-$this->packCount-4);
                $data = $newdata;
                $flag = true;

            }else{
                $flag = true;

            }
        }

        return $flag;
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
            $this->data = [];
            $this->send('上传完毕','text',['code'=>1]);
        }
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
//        if($this->header['pack_len'] > $last){
//            $this->temp_count = $this->header['pack_len'] - $last;
//            $this->recive_count +=$last;
//        }

        return  array_slice($data,$this->packHeader+8,$last);
    }
}