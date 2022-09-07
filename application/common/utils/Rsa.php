<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/8
 * Time: 18:10
 */

namespace app\common\utils;


use think\Exception;

class Rsa
{
    /**
     *获取私钥
     * @param $private_key_path
     * @return bool|resource
     * @author yang
     * Date: 2022/6/8
     */
    private static function getPrivateKey($private_key_path=''){
        $abs_path = $private_key_path;
        $content = file_get_contents($abs_path);
        return openssl_pkey_get_private($content);
    }

    /**
     * 获取公钥
     * @param string $public_key_path
     * @return resource
     * @author yang
     * Date: 2022/6/8
     */
    private static function getPublicKey($public_key_path=''){
        $abs_path = $public_key_path;
        $content = file_get_contents($abs_path);

        return openssl_pkey_get_public($content);
    }


    /**
     * 私钥加密
     * @param string $data
     * @return null|string
     */
    public static function privEncrypt($data = '',$private_key_path=''){
        if (!is_string($data)) {
            return null;
        }
        return openssl_private_encrypt($data,$encrypted,self::getPrivateKey($private_key_path)) ? base64_encode($encrypted) : null;
    }

    /**
     * 公钥加密
     * @param string $data
     * @return null|string
     */
    public static function publicEncrypt($data = '',$public_key_path='')
    {

        if (!is_string($data)) {
            return null;
        }

        return openssl_public_encrypt($data,$encrypted,self::getPublicKey($public_key_path)) ? base64_encode($encrypted) : null;
    }

    /**
     * 私钥解密
     * @param string $encrypted
     * @return null
     */
    public static function privDecrypt($encrypted = '',$private_key_path='')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::getPrivateKey($private_key_path))) ? $decrypted : null;
    }

    /**
     * 公钥解密
     * @param string $encrypted
     * @return null
     */
    public static function publicDecrypt($encrypted = '',$public_key_path='')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, self::getPublicKey($public_key_path))) ? $decrypted : null;
    }
}