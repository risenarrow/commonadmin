<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/28
 * Time: 9:04
 */

namespace app\common\utils;
set_time_limit(0);
ini_set('default_socket_timeout', -1);
use app\common\utils\interfaceClass\SocketRev;

use think\Exception;
use think\facade\Env;

class Socket
{
    private $param = [] ;
    private $socket = NULL;
    private $clients = [];
    private $write = [];
    private $rev = NULL;
    private $except = NUll;

    private $socketpack = NULL;


    const pidfile = __CLASS__;
    const uid	= 80;
    const gid	= 80;
    public function __construct(SocketRev $rev)
    {
        if (!Yutils::is_cli()) {
            throw new Exception('invalid path');
        }
        $this->rev = $rev;
        $this->pidfile = '/var/run/'.self::pidfile.'.pid';

    }


/*****************************************************************************创建守护程序******************************************************/
    /**
     * 用来创造守护程序
     * @return int
     * @author yang
     * Date: 2022/7/7
     */
    private function daemon(){
        if (file_exists($this->pidfile)) {
            echo "The file $this->pidfile exists.\n";
            exit();
        }

        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            // we are the parent
            //pcntl_wait($status); //Protect against Zombie children
            exit($pid);
        } else {
            // we are the child
            file_put_contents($this->pidfile, getmypid());
            posix_setuid(self::uid);
            posix_setgid(self::gid);
            return(getmypid());
        }
    }
    //设置一系列参数 host 或 port
    public function setParam($param = []){
        $this->param = $param;
    }

    /**
     *
     * 开始运行socket
     * @param string $host
     * @param int $port
     * @return bool
     * @throws Exception
     * @author yang
     * Date: 2022/7/7
     */
    public  function  start($host='',$port=-1){
        //启动守护进程
        $pid = $this->daemon();
        if(empty($host) || $port == -1){
            if(!isset($this->param['socket_host']) || !isset($this->param['socket_port'])){
                return false;
            }
        }else{
            $this->param['socket_host'] = $host;$this->param['socket_port'] = $port;
        }
        $socket_kill_path = Env::get('ROOT_PATH').'/application/common/utils/socket_kill.txt';

        //创建socket套接流
        $this->socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        echo 'socket套接流创建成功....';
        if(socket_bind($this->socket,$this->param['socket_host'],$this->param['socket_port']) === false){
            throw new Exception('server bind fail:' . socket_strerror(socket_last_error()));
        }
        echo 'socket绑定成功....';
        //监听套接流
        if (socket_listen($this->socket, 100) === false) {
            throw new Exception('server listen fail:' . socket_strerror(socket_last_error()));
        }
        //创建客户链接数组  把监听socket放进去
        $this->clients = [$this->socket];
        $this->write = [];
        echo 'socket监听成功....';
        //记录这次socket
        $file = fopen($socket_kill_path,"r+");
        fwrite($file,1);
        $flag = true;
        //让服务器无限获取客户端传过来的信息
        do {

            $flag = file_get_contents($socket_kill_path) == 1?true:false;

            //运用io多路复用方式
            //避免socket_select修改$clients的值
            $read = $this->clients;
            if(socket_select($read,$this->write, $this->except,0) < 1){
                continue;
            }

            //处理连接过来的socket
           $this->doSocket($this->socket,$read);

        } while ($flag);
        socket_close($this->socket);
        fclose($file);
        echo 'socket已关闭....';
    }

    /**
     * 用来kill掉进程
     * @author yang
     * Date: 2022/7/7
     */
    private function stop(){

        if (file_exists($this->pidfile)) {
            $pid = file_get_contents($this->pidfile);
            posix_kill($pid, 9);
            unlink($this->pidfile);
        }
    }

    /**
     * 用来显示命令
     * @param $proc
     * @author yang
     * Date: 2022/7/7
     */
    private function help($proc){
        printf("%s start | stop | help \n", $proc);
    }


    /**
     * 主函数
     * @param $argv
     * @throws Exception
     * @author yang
     * Date: 2022/7/7
     */
    public function main($argv)
    {
        if (count($argv) < 2) {
            printf("please input help parameter\n");
            exit();
        }
        if ($argv[1] === 'stop') {
            $this->stop();
        } else if ($argv[1] === 'start') {
            $this->start();
        } else {
            $this->help($argv[0]);
        }
    }

    /*****************************************************************************创建守护程序结束******************************************************/




    //处理连接后的socket
    public  function doSocket(&$socket,&$read){

        //第一次进来肯定是in_array的，因为此时有监听socket和客户端socket
        if (in_array($socket, $read)) {
            // accept the client, and add him to the $clients array
            $this->clients[] = $newsock = socket_accept($socket);

//            $this->sendMsg("你好 :)-",$newsock);
//            socket_getpeername($newsock, $ip);
//            echo "New client connected: {$ip}\n";
              $this->rev->firstConnect($newsock);
            // remove the listening socket from the clients-with-data array
            $key = array_search($socket, $read);
            unset($read[$key]);
        }

        // loop through all the clients that have data to read from
        foreach ($read as $read_sock) {
            // read until newline or 1024 bytes
            // socket_read while show errors when the client is disconnected, so silence the error messages

            $data= array();
            //$data = @socket_read($read_sock,2048);
            $buffer = @socket_recv($read_sock, $data, 2048,MSG_DONTWAIT);

            // check if the client is disconnected 如果获取不到数据证明该连接断线
            if ($data === false) {
                // remove client for $clients array
                $key = array_search($read_sock, $this->clients);
                unset($this->clients[$key]);
//                echo "client disconnected.\n";
//                // continue to the next client to read from, if any
                continue;
            }

            // trim off the trailing/beginning white spaces
           // $data = trim($data);

            // check if there is any data after trimming off the spaces
            //处理客户发过来的数据

            if (!empty($data)) {
                //交给实现了SocketRev接口的类处理
                $this->setSocket($buffer,$data,$read_sock,$this->clients);
//                // send this to all the clients in the $clients array (except the first one, which is a listening socket)
//               //需要发送的socket,除了它自己
//                foreach ($this->clients as $send_sock) {
//
//                    // if its the listening sock or the client that we got the message from, go to the next one in the list
//                    if ($send_sock == $socket || $send_sock == $read_sock)
//                        continue;
//
//                    // write the message to the client -- add a newline character to the end of the message
//                    socket_write($send_sock, $data."\n");
//
//                } // end of broadcast foreach

                //echo $data."\r\n";
            }

        } // end of reading foreach
    }


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
        if(!$this->socketpack){
            $this->socketpack= new SocketPack();
        }
        //进行解包操作
        $this->socketpack->unpack($buffer,$data,$socket,$clients,[$this, 'beforeRequestMsg']);
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