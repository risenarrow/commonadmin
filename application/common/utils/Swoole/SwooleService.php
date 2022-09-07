<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/11
 * Time: 15:18
 */

namespace app\common\utils\Swoole;

use app\common\utils\Yutils;
use think\Exception;

class SwooleService
{
    private $socket_host = "";
    private $socket_port = "";


    public $onRecive;
    public $onClose;

   public function __construct($socket_host="",$socket_port="")
   {
       if (!Yutils::is_cli()) {
           throw new Exception('invalid path');
       }
       $this->socket_host = $socket_host;
       $this->socket_port = $socket_port;
   }

    public function run(){
       $ws = new \Swoole\WebSocket\Server($this->socket_host,$this->socket_port);
        $ws->set([
            'worker_num'      => 1,
            'task_worker_num' => 3,
            'task_use_object' => true,
            'daemonize'=>false,
//    'task_object' => true, // v4.6.0版本增加的别名
        ]);
        //监听WebSocket连接打开事件
        $ws->on('open', function ($ws, $request) {
//            var_dump($request->fd, $request->get, $request->server);
            $ws->push($request->fd, "hello, welcome\n");
        });

        //监听任务协程
        $ws->on('Task',function($serv, \Swoole\Server\Task $task){
            //来自哪个 Worker 进程
            $task->worker_id;
            //任务的编号
            $task->id;
            //任务的类型，taskwait, task, taskCo, taskWaitMulti 可能使用不同的 flags
            $task->flags;
            //任务的数据
            $task->data;
            //投递时间，v4.6.0版本增加
            $task->dispatch_time;
            //协程 API
            co::sleep(0.2);
            //完成任务，结束并返回数据
            $task->finish([123, 'hello']);
        });


        //监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
            echo "Message: {$frame->data}\n";

            if(isset($this->onRecive)) {
                call_user_func($this->onRecive, $frame->data, $ws, $frame);
            }
        });

        //监听WebSocket连接关闭事件
        $ws->on('close', function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
            if(isset($this->onClose)) {
                call_user_func($this->onClose, $ws, $fd);
            }
        });

        $ws->start();
    }



}