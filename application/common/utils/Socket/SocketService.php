<?php
/**
 *
 *
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/7/22
 * Time: 13:59
 */

namespace app\common\utils\Socket;
use app\common\utils\Socket\interfaceClass\SocketRev;

class SocketService
{
    private $socket_host = "";
    private $socket_port = "";

    private $socketpack = NULL;
    private $rev = NULL;

    public function __construct($socket_host="",$socket_port="",SocketRev $rev)
    {
        $this->socket_host = $socket_host;
        $this->socket_port = $socket_port;
        $this->rev = $rev;
    }




    public function run(){
        $socket = new Socket();
        $socket->setParam([
            'socket_host'=>$this->socket_host,
            'socket_port'=>$this->socket_port
        ]);
        $arr[0] = isset($_SERVER['argv'][2])?$_SERVER['argv'][2]:0;
        $arr[1] =isset($_SERVER['argv'][3])?$_SERVER['argv'][3]:0;

        $socket->onClose = function($conn){

        };
        $socket->onConnect = function($conn){
            $this->rev->firstConnect($conn);
        };

        $socket->onMessage = function ($buffer,$data,$conn, $clients){
            // TODO: Implement firstConnect() method.
            if(!$this->socketpack){
                $this->socketpack= new SocketPack();
            }
            //进行解包操作
            if($buffer){
                $this->socketpack->unpack($buffer,$data,$conn,$clients,[$this, 'beforeRequestMsg']);
            }

        };
        $socket->main($arr);
    }


    /**
     * 解包后，格式化请求数据
     * @param $buffer
     * @param $data
     * @author yang
     * Date: 2022/7/6
     */
    public function beforeRequestMsg($out_header,$out_body,$out_packHeader,&$read_sock,&$clients){

        if (!$out_header) {
            return;
        }
        //请求数据处理
        $this->rev->setSocket($read_sock,$clients,[
            'header'=>$out_header,
            'body'=>$out_body,
            'packHeader'=>$out_packHeader,
        ]);
        if(!empty($out_body) && $out_header['pack_len'] > 0){
            return 0;
        }else{
            return 1;
        }

    }




}