<?php
/**
 * 文件上传
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/5/24
 * Time: 9:37
 */

namespace app\common\utils;
use think\Exception;
use think\facade\Cache;
use think\facade\Config;


class Upload
{
    private $ext = '';
    private $type='';
    private $path = '';
    private $config = array();
    private $msg='';
    private $upload_path='';
    private $size = 0;
    private $dir_name = "";

    function __construct()
    {
        $this->config=Yutils::getSysConfig();
        $this->type =Yutils::getSysConfig('FILE_TYPE');
        $this->ext =Yutils::getSysConfig('FILE_EXT');
        $this->upload_path = Yutils::getSysConfig('FILE_UPLOAD_PATH');
        $this->size = Yutils::getSysConfig('FILE_SIZE');
    }

    public function setDirName($str=""){
        $this->dir_name = $str;
    }

    public function upload($files){
        //取出选中的后缀名

        $config_item = Yutils::formatFormType($this->config['FILE_EXT']['config_item']);
        $ext = explode(',',$this->ext);
        $str = '';
        foreach ($config_item as $k=>$v){
            if(in_array($k,$ext)){
                $str.=$v.',';
            }
        }
        $str = substr($str,0,strlen($str)-1);
        $upload_path = './'.$this->upload_path;
        if($this->dir_name){
            $upload_path = $upload_path."/".$this->dir_name;
        }
        if(!is_dir($upload_path)){
            File::createDir($upload_path);
        }
        //开始移动上传的图片
        foreach ($files as $file){
            $info = $file->validate(['size'=>$this->size,'ext'=>$str])->move($upload_path);
        }

        if($info){
            if($this->dir_name){
                $this->path = $this->upload_path.DIRECTORY_SEPARATOR.$this->dir_name.DIRECTORY_SEPARATOR.$info->getSaveName();
            }else{
                $this->path = $this->upload_path.DIRECTORY_SEPARATOR.$info->getSaveName();
            }

            return true;
        }
        $this->msg= $file->getError();
        return false;
    }

    /**
     * 上传成功后的文件路径
     * @return string
     * @author yang
     * Date: 2022/5/24
     */
    public function getPath(){
        return $this->path;
    }

    /**
     * 上传后返回信息
     * @return string
     * @author yang
     * Date: 2022/5/24
     */
    public function getMsg(){
        return $this->msg;
    }

}