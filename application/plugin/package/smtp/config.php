<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/2
 * Time: 15:57
 */


return [
    'smtpserver'=>['type'=>'text','title'=>'smtp服务器','value'=>'risenarrow.6655.la'],
    'smtpserverport'=>['type'=>'text','title'=>'smtp端口','value'=>'30004'],
    'smtpusermail'=>['type'=>'text','title'=>'smtp用户邮箱','value'=>"xiakucao.top@mail.xiakucao.top"],
    'smtpuser'=>['type'=>'text','title'=>'smtp用户','value'=>"xiakucao.top"],
    'smtppass'=>['type'=>'text','title'=>'smtp用户密码','value'=>"1yang234"],
    'debug'=>['type'=>'radio','title'=>'调试','value'=>[0=>'关闭',1=>'开启']],
    'week'=>[
        'type'=>'checkbox'
        ,'title'=>'测试',
        'value'=>[0=>'星期日',1=>'星期一',2=>'星期二']
    ]
];