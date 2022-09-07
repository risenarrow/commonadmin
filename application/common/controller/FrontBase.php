<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/5
 * Time: 12:00
 */

namespace app\common\controller;
use app\admin\model\Module;
use app\common\controller\Base;
use think\App;
use think\Response;
use think\exception\HttpResponseException;
use app\common\utils\Redis\RedisService;

class FrontBase extends Base
{
    public  static $redis ;
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        if(!Module::getModuleStatus($this->request->module())){
            $this->error('模块已禁用或未安装');
        }
        //初始化redis
        self::$redis = RedisService::getInstance();
    }

    /**
     * 输出json数据用
     * @param int $code
     * @param int $count
     * @param array $data
     * @param string $msg
     * @param array $header
     * @author yang
     * Date: 2022/5/23
     */
    public function echoJson($msg='',$code=0,$count=0,$data=[],$header=[]){
        $type = $this->getResponseType();
        $type = 'json';
        $result = [
            'code'=>$code,
            'count'=>$count,
            'data'=>$data,
            'msg'=>$msg
        ];
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }


}