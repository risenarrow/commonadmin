<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/11
 * Time: 15:17
 */

namespace app\gotochat\controller;

use app\common\controller\FrontBase;
use app\common\utils\Swoole\SwooleService;

class Swoolewebsocket extends FrontBase
{
    const errcode = [
        '1001'=>'path not found'
    ];
    public function index(){

        $swooleservice = new SwooleService("0.0.0.0",30005);
        $swooleservice->onRecive = function($data,$ws,$frame){

            $rootpath = "app\\gotochat";
            //解析文本

            $msg =  json_decode(trim(stripslashes($data),'"'),true);

            if($msg !== false && isset($msg['header']) && isset($msg['body'])){
                $header = $msg['header'];
               
                if(!is_string($header['pathinfo'])){
                    $ws->push($frame->fd, json_encode(['error'=>1001]));
                    return ;
                }
                //解析pathinfo
                $pathinfo = explode("/",$header['pathinfo']);
                if(!is_array($pathinfo)){
                    $ws->push($frame->fd, json_encode(['error'=>1001]));
                    return ;
                }
                $class = $rootpath."\\controller\\".$pathinfo[0];
                if(!class_exists($class)){
                    //不存在就通知
                    $ws->push($frame->fd, json_encode(['error'=>1002]));
                    return ;
                }

                $instance = new $class($msg,$ws,$frame);
                if(!method_exists($instance,$pathinfo[1])){
                    //不存在就通知
                    $ws->push($frame->fd, json_encode(['error'=>1003]));
                    return ;
                }
                //进入对应的controller
                call_user_func([$instance,$pathinfo[1]],$msg,$ws,$frame);
            }
        };

        $swooleservice->onClose = function($ws,$fd){

        };
        $swooleservice->run();
    }

}