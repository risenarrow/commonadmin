<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/17
 * Time: 16:06
 */

namespace app\gotochat\model;


use app\common\model\FrontModel;

class GotoChatBaseModel extends FrontModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);

    }
}