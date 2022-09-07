<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/7/19
 * Time: 11:47
 */

namespace app\common\utils;


class SocketPack
{

    private  $temp_data = [];         //缓存包数据
    private  $key = 0;              //socketID
    private  $packHeader;           //包体头部大小
    private  $header;               //头部数据
    private  $body;                 //身体数据
    private  static  $totol_len = 2048;
    private  $buffer;

    public static function pack($header='',$body='',&$clientBuffer,$sendCount,$totol_len=2048){
        //$msgtemp = substr($body,$sendCount,1600);
        $msgtemp = $body;
        //身体数据
        $_sendBuffer = Yutils::strToBytes($msgtemp,0,1600);

        //头部
        $header['pack_len'] = strlen($msgtemp);
        $json = json_encode($header);
        $jsbuffer = Yutils::strToBytes($json,0,440);

        //头部大小
        $header_count_buffer = Yutils::integerToBytes(strlen($json));

        //包大小
        $pack_count = strlen($json) + strlen($msgtemp)+4+4;
        $pack_count_buffer = Yutils::integerToBytes($pack_count);

        $temp = array_merge($pack_count_buffer,$header_count_buffer);
        $temp =  array_merge($temp,$jsbuffer);
        $temp =  array_merge($temp,$_sendBuffer);
        $clientBuffer =$temp;
        return strlen($msgtemp);
    }



    public  function unpack($buffer,$data,&$socket,&$clients,callable $callback){
        $data = unpack("C*", $data);
        $data = array_merge([],$data);
        $this->key = array_search($socket,$clients);
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

                //输出数据
                $out_packHeader = $this->getPackH($tmp);
                $out_header = $this->getHeader($buffer, $tmp,$out_packHeader);
                $out_body = $out_header['pack_len'] > 0 ? $this->getBody($tmp,$out_packHeader) : null;
                //调用回调函数
                $res =  call_user_func_array($callback,[$out_header, $out_body,$out_packHeader,&$socket,&$clients]);
                //处理结束
                if($res == 1){
                    //接收完需要清楚这个socket
                    unset($this->temp_data[$this->key]);
                    break 1;
                }elseif($res == -1){
                    break 1;
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
     *
     * 获取包的大小
     * @param $bytes
     * @return int
     * @author yang
     * Date: 2022/7/18
     */
    public function getPackCount($bytes){

        $arr = [];
        for($i=0;$i< 4 ; $i++){
            $arr[]  = $bytes[$i];
        }
        $count = Yutils::bytesToInteger($arr);
        return $count;
    }


    /**
     *
     * @param $bytes
     * @return int
     * @author yang
     * Date: 2022/7/18
     */
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

    public function getBody($data,$packHeader){
        $last = count($data) - $packHeader-4-4;
        return  array_slice($data,$packHeader+8,$last);
    }



}