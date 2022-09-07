<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/28
 * Time: 10:16
 */

namespace app\utilsbox\model;

use app\common\model\FrontModel;
use app\common\utils\File;
use app\common\utils\Socket\interfaceClass\SocketRev;
use app\common\utils\Socket\SocketPack;
use app\common\utils\Yutils;
use think\Exception;

class Wangpan  extends FrontModel implements SocketRev
{

    private  $socket;
    private  $clients;

    private  $packHeader;           //包体头部大小
    private  $header;               //头部数据
    private  $body;                 //身体数据
    private  static  $totol_len = 2048;



    /**
     *
     * @param $socket       发送消息的socket
     * @param $clients      连接服务器的socket
     * @param $param        解包后的数据
     * @author yang
     * Date: 2022/6/30
     */
    //有客户端发消息给服务器
    public function setSocket(&$socket,&$clients,$param=[])
    {
        // TODO: Implement firstConnect() method.

        $this->socket = $socket;
        $this->clients = $clients;
        $this->packHeader = $param['packHeader'];
        $this->body = $param['body'];
        $this->header = $param['header'];

        return $this->requestMsg();
    }



    /**
     *
     * 第一次连接发送消息
     * @param $socket
     * @author yang
     * Date: 2022/7/1
     */
    public function firstConnect(&$socket)
    {
        // TODO: Implement firstConnect() method.
        $this->socket = $socket;
        $this->send($socket,'你好!',"text");
    }

    /**
     * 发送消息到客户端 统一分配函数
     * @param string $msg
     * @param string $type
     * @param array $param
     * @author yang
     * Date: 2022/7/1
     */
    public function send(&$socket,$msg='',$type='text',$param=[]){
        switch ($type){
            case 'text':
                $this->sendText($socket,$msg,$param);
                break;
            case 'file':
                $this->sendFile($socket,$msg,$param);
        }
    }

    /**
     * 发送文本消息到客户端
     * @param $msg
     * @author yang
     * Date: 2022/7/1
     */
    public function sendText(&$socket,$msg,$param=[]){
        /**
         * json["request_type"] = "upload_file";//请求类型
        json["user_id"] = "1";//用户
        json["path"] = path.Substring(3, path.Length - 3);
        json["filesize"] = fileinfo.Length.ToString();//大小
        json["filename"] = fileinfo.Name.ToString();//文件名
         *
         *
         */
        $sendCount = 0;         //发送了多少
        $pack_count_buffer =[];   //包大小
        $header_count_buffer = []; //头部大小
        $jsbuffer =  [];         //头部数据
        $_sendBuffer =  [];      //身体数据
        $clientBuffer = [];     //包数据 = 包大小+头部大小+头部数据+身体数据


        $header =[
            'code'=>isset($param['code'])?$param['code']:0,
            'respond_type'=>'text'
        ];
        $msg = Yutils::toEncodeUtf8($msg);
        $count = strlen($msg);
        while($count > $sendCount){
            $body = substr($msg,$sendCount,1600);
            //打包数据
           $c =  SocketPack::pack($header,$body,$clientBuffer,$sendCount);
           //发送数据
            $sendata = call_user_func_array("pack",array_merge(["C*"],$clientBuffer));

            $res = $this->my_socket_write($socket,$sendata,strlen($sendata));

            if($res !== false){
                $sendCount += $c;
            }else{
                break;
            }
        }
        //身体为空即传输完
        $body = "";
        //打包数据
        SocketPack::pack($header,$body,$clientBuffer,0);
        $res = $this->my_socket_write($socket,call_user_func_array("pack",array_merge(["C*"],$clientBuffer)),self::$totol_len);

        return $res;
    }

    /**
     * 下载文件
     * @param $socket
     * @param $msg
     * @param $param
     * @author yang
     * Date: 2022/7/18
     */
    public function sendFile(&$socket,$msg,$param){
        $sendCount = 0;         //发送了多少
        $clientBuffer = [];     //包数据 = 包大小+头部大小+头部数据+身体数据

        $last_pos = isset($param['last_pos'])?intval($param['last_pos']):0;
        $filename = isset($param['filename'])?$param['filename']:"";
        $path = $this->getPath($param['path']);
        $user_id = isset($param['user_id'])?$param['user_id']:0;
        $user_root_path = $this->getUserFilePath($user_id);
        //获取用户目录路径
        if($user_root_path === false){
            $this->send($socket,$this->msg,"text");return ;
        }
        //文件路径
        $filepath = $user_root_path.$path."/".$filename;
        if(!file_exists($filepath)){
            $this->send($socket,"请选择正确的文件","text");return ;
        }
        //获取文件大小
        $filesize = filesize($filepath);

        //组装头部
        $header =[
            'code'=>isset($param['code'])?$param['code']:0,
            'respond_type'=>'file',
            'filename'=>$filename,
            'filesize'=>$filesize,
            'path'=>$path
        ];

        try {


//****************************************服务器分段发送数据*******************
            if($last_pos<$filesize){
                $file = fopen($filepath, "rb");
                fseek($file,$last_pos);
                $body = fread($file, 1600);
                if($filesize-$last_pos >= 1600){
                    $header['last_pos'] = $last_pos+1600;
                }else{
                    $header['last_pos'] = $filesize;
                }

                //打包数据
                $c = SocketPack::pack($header, $body, $clientBuffer, $sendCount);
                //发送数据
                $sendata = call_user_func_array("pack", array_merge(["C*"], $clientBuffer));
                $res = $this->my_socket_write($socket, $sendata, strlen($sendata));
                //关闭文件
                fclose($file);
            }

//*****************************************服务器一次性发送数据******************
//            while (!feof($file)) {
//                //组装身体
//                $body = fread($file, 1600);
//
//                //打包数据
//                $c = SocketPack::pack($header, $body, $clientBuffer, $sendCount);
//                //发送数据
//                $sendata = call_user_func_array("pack", array_merge(["C*"], $clientBuffer));
//                $res = socket_write($socket, $sendata, strlen($sendata));
//                if ($res !== false) {
//                    $sendCount += $res;
//                } else {
//                    break;
//                }
//            }


        }catch (Exception $e){
            $this->send($socket,"传输出错".$e->getMessage());
        }
        //身体为空即传输完

        $body = "";
        //打包数据
        SocketPack::pack($header,$body,$clientBuffer,0);

        $senddata=call_user_func_array("pack",array_merge(["C*"],$clientBuffer));

        $res = $this->my_socket_write($socket,$senddata,strlen($senddata));

        return $res;
    }





    /**
     * 处理请求 统一分配函数
     * @author yang
     * Date: 2022/7/1
     */
    public function requestMsg(){

        switch ($this->header['request_type']){
            case 'upload_file':
                $re =  $this->request_upload_file();
                break;
            case 'download':

                $re = $this->send($this->socket,$msg='',$type='file',[
                    'filename'=>$this->header['filename'],
                    'path'=>$this->header['path'],
                    'user_id'=>$this->header['user_id'],
                    'last_pos'=>$this->header["last_pos"]
                ]);
                break;
            default:
                $re=-1;
                break;
        }
        return $re;
    }


    /**
     * 处理上传文件
     * @author yang
     * Date: 2022/7/1
     */
    private function request_upload_file(){
        $param = $this->header;
        $filename = $param['filename'];
        $path = $param['path'];
        $path = $this->getPath($path);;

        if(!empty($this->body) && $this->header['pack_len'] > 0){
            if(!($user_file_path = $this->getUserFilePath($param['user_id']))){
                $this->send($this->socket,$this->msg,'text',['code'=>1]);
                return 0;
            }
            if(!is_dir($user_file_path.$path)){
                File::createDir($user_file_path.$path);
            }

            $file = fopen($user_file_path.$path.'/'.$filename,'a+');
            fwrite($file,call_user_func_array("pack",array_merge(["C*"],$this->body)));
            fclose($file);
        }else{
            $this->send($this->socket,'上传完毕','text',['code'=>1]);
            return 1;
        }
    }




    /**
     * 创建文件目录
     * @author yang
     * Date: 2022/7/9
     */
    public function createDir(){
        $data = $this->param;
        $path = $data['path'];
        $filename = $data['filename'];

        if(!($user_file_path = $this->getUserFilePath($data['user_id']))){
            return false;
        }
        try{
            $path = $this->getPath($path);;
            $dir = $user_file_path.$path."/".$filename;
            if(!is_dir($dir)){
                if(!File::createDir($dir)){
                    $this->msg = '文件夹创建失败';return false;
                }
                $this->msg = '文件夹创建成功';
                return true;
            }
            return true;
        }catch (Exception $e){
            $this->msg = "文件夹创建失败".$e->getMessage();
            return false;
        }

    }

    /**
     * 改变目录名
     * @author yang
     * Date: 2022/7/9
     */
    public function changeDir(){
        $data = $this->param;
        $path = $data['path'];
        $filename = $data['filename'];
        $oldfilename = $data['oldfilename'];
        if(!($user_file_path = $this->getUserFilePath($data['user_id']))){
            return false;
        }
        $path = $this->getPath($path);;
        $oldpath = $user_file_path.$path."/".$oldfilename;
        $newpath = $user_file_path.$path."/".$filename;

        if(is_dir($newpath) || file_exists($newpath)){
            $this->msg = '文件或文件夹已存在';
            return false;
        }
        try{

            if(!is_dir($oldpath) && !file_exists($oldpath)){
                $this->msg = '文件或文件夹不存在';return false;
            }else{
               if(rename($oldpath,$newpath)){
                   $this->msg = '';
                   return true;
               }
                $this->msg = '修改失败';
               return false;
            }
        }catch (\Exception $e){
            $this->msg = "文件夹创建失败".$e->getMessage();
            return false;
        }
    }


    /**
     * 获取文件列表
     * @return array|bool
     * @author yang
     * Date: 2022/7/15
     */
    public function getlist(){
        $data = $this->param;
        $path = isset($data['path'])?$data['path']:"/";

        if(!($user_file_path = $this->getUserFilePath($data['user_id']))){
            return false;
        }
        $path = $this->getPath($path);
        $aim = $user_file_path.$path;
        if(!is_dir($aim)){
            $this->msg = "目录不存在";return false;
        }
        $list = scandir($aim);
        $data = [];

        foreach ($list as  $key=>$li){

            if($li == '.' || $li == '..')
            {  unset($list[$key]);continue;}
            $aimfile  = $aim."/".$li;
            $type = '文件夹';
            if(is_file($aimfile)){
                $type = "文件";
            }
            $data[] = [
                'filename'=>$li,
                "datetime"=>date("Y-m-d H:i:s",filemtime($aimfile)),
                "type"=>$type,
                'size'=>floor(filesize($aimfile)/1024)." KB"
                ];
        }
        return $data;
    }

    /**
     * 删除文件及文件夹
     * @return bool
     * @author yang
     * Date: 2022/7/29
     */
    public function delDir(){
        $data = $this->param;
        if(!($user_file_path = $this->getUserFilePath($data['user_id']))){
            return false;
        }
        $path = $this->getPath($data['path']);;
        $filenames = $data['paths'];
        $paths = explode(",",$filenames);

        foreach ($paths as $key=>$v){
            $p = $user_file_path.$path."/".$v;
            if(is_dir($p)){
                File::deldir($p);
            }
            if(file_exists($p)){
                File::delfile($p);
            }
        }
        $this->msg ="删除成功";
        return true;
    }



    public function moveDir(){
        $data = $this->param;
        if(!($user_file_path = $this->getUserFilePath($data['user_id']))){
            return false;
        }
        if(!isset($data['old_path']) || !isset($data['new_path'])){
            $this->msg= "移动失败，路径不存在";return false;
        }
        $newpath = $user_file_path.$this->getPath($data['new_path']);
        $oldpath = explode(",",$data['old_path']);
        foreach ($oldpath as $key=>$v){
            $oldpath[$key] = $user_file_path.$this->getPath($v);
        }
        try{
            if(File::moveDir($oldpath,$newpath)){
                $this->msg = "移动成功";
                return true;
            }
        }catch (Exception $e){
            $this->msg="移动失败".$e->getMessage();
            return false;
        }

        $this->msg="移动失败";
        return false;
    }


    public function getPath($path = ''){
        $path = "/".ltrim($path,'/');
        return $path;
    }
    public function getUserFilePath($user_id = 0){
        if(!$user_id){
            $this->msg  =  "请先登录";return false;
        }
        $file_root_path =config('upload_file_path');
        $user_file_path = $file_root_path."/".$user_id;
        return $user_file_path;
    }

    /**
     *
     * 封装写入socket流函数
     * @param $socket
     * @param $sendata
     * @param $length
     * @return int
     * @author yang
     * Date: 2022/7/29
     */
   public function my_socket_write(&$socket,$sendata,$length){
       $re = socket_write($socket,$sendata,$length);
       if($re === false){
           Yutils::Log(__CLASS__."socket_close");
           socket_close($socket);
       }
       return $re;
   }


}