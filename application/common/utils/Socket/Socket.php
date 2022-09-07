<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/28
 * Time: 9:04
 */

namespace app\common\utils\Socket;
set_time_limit(0);
ini_set('default_socket_timeout', -1);
use think\Exception;
use app\common\utils\Yutils;
use Event;
use EventBase;
class Socket
{
    private $param = [] ;
    private $socket = NULL;
    private $clients = [];
    private $events = [];
    private $write = [];
    private $except = NUll;



    //连接事件回调
    public $onConnect = NULL;

    //断线事件回调
    public $onClose = NULL;

    //接收消息事件回调
    public $onMessage = NULL;


    public $pidfile ;
    const uid	= 80;
    const gid	= 80;
    public function __construct($pidfilename=__CLASS__)
    {
        if (!Yutils::is_cli()) {
            throw new Exception('invalid path');
        }

        $this->pidfile = '/var/run/'.$pidfilename.'.pid';

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

        // 将$listen_socket设置为非阻塞IO
        socket_set_nonblock( $this->socket );

        //创建客户链接数组  把监听socket放进去
        $this->clients[(int)$this->socket] = $this->socket;
        $this->write = [];
        echo 'socket监听成功....';
        //记录这次socket



        //运用io多路复用方式 epoll模式*******************************************************

        $event_base = new EventBase();
        $method_name = $event_base->getMethod();
        if ( 'epoll' != $method_name ) {
            exit( "not epoll" );
        }


        // 在$listen_socket上添加一个 读事件
        // 为啥是读事件？
        // 因为$listen_socket上发生事件就是：客户端建立连接
        // 所以，应该是读事件
        $o_event = new Event( $event_base, $this->socket, Event::READ | Event::PERSIST,[$this,'accept_callback'], $event_base );
        $o_event->add();
        //$a_event_array[] = $o_event;
        $event_base->loop();






//        运用io多路复用方式  select模式
//        $flag = true;
//        //让服务器无限获取客户端传过来的信息
//        do {
//
//            //避免socket_select修改$clients的值
//            $read = $this->clients;
//            if(socket_select($read,$this->write, $this->except,0) < 1){
//                continue;
//            }
//
//            //处理连接过来的socket
//           $this->doSocket($this->socket,$read);
//
//        } while ($flag);



        socket_close($this->socket);
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




//    //处理连接后的socket
//    public  function doSocket(&$socket,&$read){
//
//        //第一次进来肯定是in_array的，因为此时有监听socket和客户端socket
//        if (in_array($socket, $read)) {
//            // accept the client, and add him to the $clients array
//            $newsock = socket_accept($socket);
//            $this->clients[(int)$newsock] = $newsock;
////
////            $this->sendMsg("你好 :)-",$newsock);
////            socket_getpeername($newsock, $ip);
////            echo "New client connected: {$ip}\n";
//
//            if ($this->onConnect) {
//                //触发连接事件的回调,并将当前连接传递给回掉函数
//                call_user_func($this->onConnect, $newsock);
//            }
//
//            // remove the listening socket from the clients-with-data array
//            unset($read[(int)$socket]);
//        }
//
//        // loop through all the clients that have data to read from
//        foreach ($read as $read_sock) {
//            // read until newline or 1024 bytes
//            // socket_read while show errors when the client is disconnected, so silence the error messages
//
//            $data= array();
//            //$data = @socket_read($read_sock,2048);
//            $buffer = @socket_recv($read_sock, $data, 2048,MSG_DONTWAIT);
//
//            // check if the client is disconnected 如果获取不到数据证明该连接断线
//            if ($data === false) {
//
//                //尝试触发onClose回调
//                if ($this->onClose) {
//                    call_user_func($this->onClose, $read_sock);
//                }
//                // remove client for $clients array
//                unset($this->clients[(int)$read_sock]);
////                echo "client disconnected.\n";
////                // continue to the next client to read from, if any
//                continue;
//            }
//
//            // trim off the trailing/beginning white spaces
//           // $data = trim($data);
//            // check if there is any data after trimming off the spaces
//            //处理客户发过来的数据
//
//            if (!empty($data)) {
//                //表示一个正常的连接，已经读取到消息，交给回掉函数处理
//                if ($this->onMessage) {
//                    call_user_func($this->onMessage, $buffer,$data,$read_sock, $this->clients);
//                }
//
////                // send this to all the clients in the $clients array (except the first one, which is a listening socket)
////               //需要发送的socket,除了它自己
////                foreach ($this->clients as $send_sock) {
////
////                    // if its the listening sock or the client that we got the message from, go to the next one in the list
////                    if ($send_sock == $socket || $send_sock == $read_sock)
////                        continue;
////
////                    // write the message to the client -- add a newline character to the end of the message
////                    socket_write($send_sock, $data."\n");
////
////                } // end of broadcast foreach
////
//                //echo $data."\r\n";
//            }
//
//        } // end of reading foreach
//    }



          /**
           * epoll模式 接收连接函数
           * @param $r_listen_socket
           * @param $i_event_flag
           * @param $o_event_base
           * @author yang
           * Date: 2022/7/26
           */
          function accept_callback($r_listen_socket, $i_event_flag, $o_event_base){

              // socket_accept接受连接，生成一个新的socket，一个客户端连接socket
              $r_connection_socket = @socket_accept( $r_listen_socket );
               if($r_connection_socket === false){
                  echo socket_strerror(socket_last_error()) . PHP_EOL;
              }else{

                  socket_set_nonblock($r_connection_socket);
                  $this->clients[(int)$r_connection_socket]    = $r_connection_socket;

                  if ($this->onConnect) {
                      //触发连接事件的回调,并将当前连接传递给回掉函数
                      call_user_func($this->onConnect, $r_connection_socket);
                  }


                  // 在这个客户端连接socket上添加 读事件
                  // 也就说 要从客户端连接上读取消息
                  $o_read_event = new Event( $o_event_base, $r_connection_socket, Event::READ | Event::PERSIST,[$this,'read_callback'], $o_event_base );
                  $o_read_event->add();
                  $this->events[ intval( $r_connection_socket ) ]['read'] = $o_read_event;
              }

          }

          /**
           * epoll模式 接收数据函数
           * @param $r_connection_socket
           * @param $i_event_flag
           * @param $o_event_base
           * @author yang
           * Date: 2022/7/26
           */
        function read_callback( $r_connection_socket, $i_event_flag, $o_event_base ) {
              $buffer = @socket_recv($r_connection_socket, $data, 2048,MSG_DONTWAIT);

              // 在这个客户端连接socket上添加 读事件
              // 当这个客户端连接socket一旦满足可写条件，我们就可以向socket中写数据了

             if($buffer === false){

                 $reevent = $this->events[intval($r_connection_socket)]['read'];
                 $reevent->del();
                 unset( $this->events[ intval( $r_connection_socket ) ]['read'] );
                 if(isset( $this->events[intval($r_connection_socket)]['write'])){
                     $this->events[intval($r_connection_socket)]['write']->del();
                     unset($this->events[intval($r_connection_socket)]['write']);
                 }
                 if($r_connection_socket){
                     @socket_shutdown($r_connection_socket);
                     @socket_close($r_connection_socket);
                 }
                 return ;
             }
            //表示一个正常的连接，已经读取到消息，交给回掉函数处理
            if ($this->onMessage) {
                call_user_func($this->onMessage, $buffer,$data,$r_connection_socket, $this->clients);
            }
              $o_write_event = new Event( $o_event_base, $r_connection_socket, Event::WRITE | Event::PERSIST, [$this,'write_callback'] , array(
                  'buffer' => $buffer,
                  'data'=>$data
              ) );
              $o_write_event->add();
              $this->events[ intval( $r_connection_socket ) ]['write'] = $o_write_event;
          }

          /**
           * epoll模式 写入数据连接函数
           * @param $r_connection_socket
           * @param $i_event_flag
           * @param $a_data
           * @author yang
           * Date: 2022/7/26
           */
          function write_callback( $r_connection_socket, $i_event_flag, $a_data ) {

              $data = $a_data['data'];
              $buffer = $a_data['buffer'];

              $o_event = $this->events[ intval( $r_connection_socket ) ]['write'];
              $o_event->del();
              unset( $this->events[ intval( $r_connection_socket ) ]['write'] );
          }








}