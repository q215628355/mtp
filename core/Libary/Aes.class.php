<?php
/** 
 * Aes 加密解密类 用于支付过程
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: Aes.class.php 2016年4月5日 13:01:41Z $  
 */ 
class Aes {
 
    // CRYPTO_CIPHER_BLOCK_SIZE 32
     
    private static $_secret_key = null;
    //获取KEY
    private static function getKey(){
       if(self::$_secret_key===null){
            self::$_secret_key =  C('AES_SECRET_KEY');           
            if(self::$_secret_key === null) self::$_secret_key = '1a2q3w4d5f6g7j8k9l0mzxcv';
       }     
    }     
    public static function setKey($key) {
        self::$_secret_key = $key;
    }
    //加密字符串
    public static function encode($data) {
        self::getKey();        
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256,'',MCRYPT_MODE_CBC,'');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
        mcrypt_generic_init($td,self::$_secret_key,$iv);
        $encrypted = mcrypt_generic($td,$data);
        mcrypt_generic_deinit($td);         
        return $iv . $encrypted;
    }
    //解密字符串
    public static function decode($data) {
        self::getKey();
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256,'',MCRYPT_MODE_CBC,'');
        $iv = mb_substr($data,0,32,'latin1');
        mcrypt_generic_init($td,self::$_secret_key,$iv);
        $data = mb_substr($data,32,mb_strlen($data,'latin1'),'latin1');
        $data = mdecrypt_generic($td,$data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);         
        return trim($data);
    }
}