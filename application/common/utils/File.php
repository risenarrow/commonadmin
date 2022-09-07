<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/8
 * Time: 12:26
 */

namespace app\common\utils;


use think\Exception;

class File
{
    /**
     *
     * 复制文件或文件夹
     * @param $source
     * @param $dest
     * @param int $all   1:复制当前目录及子目录 0:只复制当前目录
     * @return bool
     * @author yang
     * Date: 2022/6/8
     */
     public static function copyFile($source,$dest,$all=1){
         if(is_file($source)){
             //获取目的地文件上一级目录
             if(preg_match('/^(.*)\/[\s\S]+\.png$/',$dest,$arr)){
                  if(!is_dir($arr[1])){
                      @mkdir($arr[1],0777,true);
                  }
                 copy($source,$dest);
             }else{
                 throw  new Exception('文件夹创建失败');return false;
             }
         }else{
             if(!is_dir($source)){
                 throw new Exception('文件不存在');  return false;
             }
             if(!is_dir($dest)){
                 if(!@mkdir($dest,0777)){
                     throw  new Exception('文件创建失败');return false;
                 }
             }
             $handle=dir($source);
             while($entry=$handle->read()) {
                 if(($entry!=".")&&($entry!="..")){
                     if(is_dir($source."/".$entry)){
                         if($all)
                             self::copyFile($source."/".$entry,$dest."/".$entry,$all);
                     }
                     else{
                         copy($source."/".$entry,$dest."/".$entry);
                     }
                 }
             }
         }
     }

    /**
     * 创建文件
     * @param string $file_name
     * @param string $path
     * @return bool
     * @throws Exception
     * @author yang
     * Date: 2022/6/8
     */
     public static function createFile($file_name='',$path='',$str=''){
         if(!$file_name || !$path){
             throw  new Exception('文件名不正确');return false;
         }
         if($path && !is_dir($path)){
             throw  new Exception('非法路径');return false;
         }
         if(file_exists($path.$file_name)){
             throw  new Exception('文件已存在');return false;
         }

         $path = rtrim($path,DIRECTORY_SEPARATOR);

         $fp= fopen($path.DIRECTORY_SEPARATOR.$file_name,"w+");
         fputs($fp,$str);
         fclose($fp);
        return true;
     }

    /**
     *
     * 创建文件夹
     * @param string $dir
     * @param string $path
     * @return bool
     * @throws Exception
     * @author yang
     * Date: 2022/6/8
     */
     public static function createDir($dir='',$path=''){
         if(!$dir){
             throw  new Exception('文件名不正确');return false;
         }
//         if(!is_dir($path)){
//             throw  new Exception('非法路径');return false;
//         }
//         if(is_dir($path.DIRECTORY_SEPARATOR.$dir)){
//             throw  new Exception('文件夹已存在');return false;
//         }
         $path = rtrim($path,DIRECTORY_SEPARATOR);
         $newdir = $dir;
         if(!empty($path)){
             $newdir = $path.DIRECTORY_SEPARATOR.$dir;
         }
         if(mkdir($newdir, 0777,true ))
             return true;
         return false;
     }

    /**
     * 删除文件
     * @param $file
     * @author yang
     * Date: 2022/7/16
     */
    public static  function delfile($file){
        try{
            return unlink($file);
        }catch (Exception $e){
            return false;
        }

    }


    /**
     * 删除文件及文件夹
     * @param $dir
     * @return bool
     * @author yang
     * Date: 2022/6/8
     *
     */
   public static function deldir($dir) {
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    self::deldir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 移动文件或文件夹
     * @param string $oldpath
     * @param string $newpath
     * 当$oldpath 为数组时，$newpath只需要目标的文件夹路径
     * 当$oldpath为字符串，$newpath需要完整的路径
     * @return bool
     * @author yang
     * Date: 2022/7/29
     */
    public static function moveDir($oldpath,$newpath=""){

       if(empty($oldpath)||empty($newpath)){
           return false;
       }
        $res = false;
       if (is_array($oldpath)){
           foreach($oldpath as $v){
               if(preg_match("/^([\s\S]){0,}\/(.*)$/",$v,$arr)){
                   $res =  rename($v,$newpath."/".$arr[2]);
               }

           }
       }elseif(is_string($oldpath)){
           $res = rename($oldpath, $newpath);
       }

       return $res;
    }


}