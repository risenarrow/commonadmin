<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/28
 * Time: 10:13
 */

namespace app\common\utils\interfaceClass;


use app\common\utils\Socket;

interface  SocketRev
{
    function firstConnect(&$socket);
    function setSocket(&$socket,&$clients,$param);
}