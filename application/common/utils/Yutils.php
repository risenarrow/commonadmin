<?php
/**
 *
 * 格式化字符
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/5/24
 * Time: 14:27
 */
namespace app\common\utils;
use app\admin\model\Setting;
use app\plugin\model\Plugin;
use function PHPSTORM_META\type;
use think\Exception;
use think\facade\Cache;
use think\facade\Env;
use think\facade\Log;
use app\common\utils\File;

class Yutils{

    static $sys_config='';


    /**
     *
     * 格式化数组或枚举的配置项
     * @param string $string
     * @return array
     * @author yang
     * Date: 2022/5/24
     */
    public static function formatFormType($string=''){
        $list = array();
        $arr = explode("\n",$string);

        foreach ($arr as $k=>$v){
            $arr1 = explode('|',$v);
            $list[$arr1[0]] = $arr1[1];
        }
        return $list;
    }

    /*获取缓存配置*/
    public static function getSysConfig($item=''){

        if(!self::$sys_config){
            $sys_config =  Cache::get('sys_config');

            if(!$sys_config){
                $set = new Setting();
                $sys_config =  $set->updateCache();
            }
            self::$sys_config = $sys_config;
        }
        if($item && !isset(self::$sys_config[$item]['value'])){
            return "";
        }
        return $item?self::$sys_config[$item]['value']:self::$sys_config;
    }

    /*密码加密方法*/
    public static function encrypt($password,$salt){
        return md5(md5($password.$salt).config('encrypt'));
    }

    /*产盐*/
    public static function getSalt(){
        return self::getRandomString(6);
    }

    /*随机获取规定长度个字符*/
    public static function getRandomString($len=0){
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=[]!@#$%^&*()';
        $string = str_split($string,1);

        $str= '';
        for ($i=0;$i<$len;$i++) {
            $str .=  $string[rand(0,75)];
        }
        return $str;
    }

    public static function copyFile($source,$dest,$all=1){
       return File::copyFile($source,$dest,$all);
    }


    public static function is_cli(){
        return preg_match("/cli/i", php_sapi_name()) ? true : false;
    }


    public static function Log($string='',$file_name=''){

        if(!$file_name){
            $default_path = Env::get('ROOT_PATH').'/runtime/mylog';
            $dir = $default_path.'/'.date('Ym');
            $file_name = date('Ymd').".txt";
            if(!is_dir($dir)){
                \app\common\utils\File::createDir($dir);
            }
            $file_name = $dir.'/'.$file_name;
        }
        file_put_contents($file_name,$string."\r\n",FILE_APPEND);
    }


    public static function toEncodeUtf8($str=''){
        $encode = strtoupper(mb_detect_encoding($str, ["ASCII",'UTF-8',"GB2312","GBK",'BIG5']));

        if($encode!='UTF-8'){

            $str = mb_convert_encoding($str, 'UTF-8', $encode);

        }

        return $str;
    }

    /**
     * Byte数组转字符串
     * @param  array $bytes
     * @return string
     */
     public static  function bytesToStr($bytes,$index=0,$len=0)
    {
        $str = '';
        if(!$len){
            $len = count($bytes);
        }

        for ($i = $index;$i < $len;$i++){
            if(isset($bytes[$i])){
                $str .= chr($bytes[$i]);
            }
        }


//        foreach ($bytes as $ch) {
//            $str .= chr($ch);
//        }
        return $str;
    }

    /**
     * 字符串转Byte数组
     * @param  string $string
     * @return array
     */
    public static  function strToBytes($string,$index=0,$byte_size=1024*4)
    {
        if($string === ""){
            return [];
        }
        $bytes = array($byte_size);
        for ($i = 0; $i < strlen($string); $i++) {
            $bytes[$index] = ord($string[$i]);
            $index ++;
        }
        return $bytes;
    }


    /**
     * 整数转字节数组
     * @param $val
     * @return array
     * @author yang
     * Date: 2022/7/2
     */
    public static function integerToBytes($val) {

        $val = (int)$val;

        $byte = array();

//低位在前，即小端法表示

        $byte[0] = ($val & 0xFF);//掩码运算

        $byte[1] = ($val >> 8 & 0xFF);

        $byte[2] = ($val >> 16 & 0xFF);

        $byte[3] = ($val >> 24 & 0xff);

        return $byte;

    }

    /**
     * 字节数组转整数
     * @param array $bytes
     * @param $pos
     * @return int
     * @author yang
     * Date: 2022/7/2
     */
    public static function bytesToInteger(array $bytes, $pos=0) {

        $val = 0;

        $val = $bytes[$pos + 3] & 0xff;

        $val <<= 8;

        $val |= $bytes[$pos + 2] & 0xff;

        $val <<= 8;

        $val |= $bytes[$pos + 1] & 0xff;

        $val <<= 8;

        $val |= $bytes[$pos + 0] & 0xff;

        return intval($val);

    }

    //获取首字母
    public static function getFirstLetter($str=""){
        if(!$str)
            return $str;
        $str= iconv("UTF-8","gb2312", $str);
        $tmp=bin2hex(substr($str,0,1));
        if($tmp>='B0'){
            $t = self::getLetter(hexdec(bin2hex(substr($str,0,2))));
            return $t==-1?"*":chr($t);
        }else{
            return substr($str,0,1);
        }
    }

    //获取首字母ascii码
    public static function getLetter($num){
        $limit = array( //gb2312 拼音排序
            array(45217,45252), //A
            array(45253,45760), //B
            array(45761,46317), //C
            array(46318,46825), //D
            array(46826,47009), //E
            array(47010,47296), //F
            array(47297,47613), //G
            array(47614,48118), //H
            array(0,0), //I
            array(48119,49061), //J
            array(49062,49323), //K
            array(49324,49895), //L
            array(49896,50370), //M
            array(50371,50613), //N
            array(50614,50621), //O
            array(50622,50905), //P
            array(50906,51386), //Q
            array(51387,51445), //R
            array(51446,52217), //S
            array(52218,52697), //T
            array(0,0), //U
            array(0,0), //V
            array(52698,52979), //W
            array(52980,53688), //X
            array(53689,54480), //Y
            array(54481,55289), //Z
        );
        $char_index=65;
        foreach($limit as $k=>$v){
            if($num>=$v[0] && $num<=$v[1]){
                $char_index+=$k; return $char_index;
            }

        }
        return -1;
    }

    /**
     *  分布式 id 生成类     组成: <毫秒级时间戳+机器id+序列号>
     *  默认情况下41bit的时间戳可以支持该算法使用到2082年，10bit的工作机器id可以支持1023台机器，12bit序列号支持1毫秒产生4095个自增序列id
     * */

    public static function createUniqueId($machineId = 0){
        $EPOCH = 1479533469598;//开始时间,固定一个小于当前时间的毫秒数
        $max12bit = 4095;
        $max41bit = 1099511627775;

        // 时间戳 42字节
        $time = floor(microtime(true) * 1000);
        // 当前时间 与 开始时间 差值
        $time -= $EPOCH;
        // 二进制的 毫秒级时间戳 41
        $base = decbin($max41bit + $time);
        // 机器id  10 字节
        $machineid = str_pad(decbin($machineId), 10, "0", STR_PAD_LEFT);
        // 序列数 12字节
        $random = str_pad(decbin(mt_rand(0, $max12bit)), 12, "0", STR_PAD_LEFT);
        // 拼接
        $base = $base.$machineid.$random;
        // 转化为 十进制 返回
        return bindec($base);
    }


    /**
     * 获取插件实例
     * @param string $name
     * @param array $param
     * @return mixed
     * @throws Exception
     * @author yang
     * Date: 2022/8/3
     */
    public static  function plugin($name='',$param=[]){
        $info = Plugin::getInfo($name);
        if(empty($info)){
           throw new Exception("插件不存在");
        }
        if($info['status'] == 1){
            $setting = Plugin::getSetting($name);
            $param = array_merge($setting,$param);
            $path = "app\\plugin\\package\\".$info['plugin_name']."\\".$info['plugin_name'];
            $instance =  new $path($param);
            return $instance;
        }
       throw new Exception("插件已禁用");
    }

    /**
     * 截取封面图
     * @param $input
     * @param $output
     * @author yang
     * Date: 2022/9/2
     */
    public static function  getVideoCover( $input, $output ) {
        $command = "ffmpeg -i $input -y  -f image2  -frames 1 $output ";
        // 视频 a.mp4 第一帧导出为 a.jpg
        //ffmpeg -i a.mp4 -y -f image2 -frames 1 a.jpg
        // 指定封面图大小
        //ffmpeg -i a.mp4 -y -f image2 -s 640*480 -frames 1 a.jpg
           shell_exec( $command );
    }

}
