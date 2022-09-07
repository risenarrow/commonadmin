<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/13
 * Time: 16:05
 */

namespace app\gotochat\controller;

use think\Controller;

class Ping extends Controller
{
    private  $msg;
    public function __construct($msg=[],$ws,$frame)
    {
        parent::__construct(null);
        $this->msg = $msg ;
    }
    public function index($msg,$ws,$frame){
        $ws->push($frame->fd,$this->echoJson1("OK",0,0));
    }
    public function echoJson1($msg = '', $code = 0, $count = 0, $data = [], $header = [])
    {
        $result=  [
            'header'=>array_merge($this->msg['header'],$header),
            'body'=>[]
        ];
        $result['body'] = [
            'code'=>$code,
            'count'=>$count,
            'data'=>$data,
            'msg'=>$msg
        ];
        return json_encode($result);
    }
}