<?php
/** 
 * 处理JSON数据
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: Json.class.php 2016年3月17日 13:01:41Z $  
 */ 

class Json{          
    
   
       /**
        *  输出正确JSON信息，$data可以是数组
        *  @param  string   $message      提示信息
        *  @param  array    $data         可选 返回数组
        *  @param  string   $type         可选 json 或jsonp
        *  @param  string   $jsoncallback 可选 当设置JSONP时的回调函数名
        *  @return json
        */
       public static function success($message,$data=array(),$type='json',$jsoncallback='jsoncallback'){  
           $result =  json_encode(array('message'=>$message,'data'=>$data,'status'=>1),JSON_UNESCAPED_UNICODE);   
           if($type == 'jsonp') return self::jsonp($result,$jsoncallback);
           return $result;
       }    
    
        //输出错误JSON信息
        /**
        *  输出错误JSON信息，$data可以是数组
        *  @param  string   $message      提示信息
        *  @param  array    $data         可选 返回数组
        *  @param  string   $type         可选 json 或jsonp
        *  @param  string   $jsoncallback 可选 当设置JSONP时的回调函数名
        *  @param  int      $code         可选 设置错误编码
        *  @return json
        */
       public static function error($message,$data=array(),$type='json',$jsoncallback='jsoncallback',$code=1){                    
            $result = json_encode(array('message'=>$message,'data'=>$data,'status'=>0,'error'=>$code),JSON_UNESCAPED_UNICODE);    
            if($type == 'jsonp') return self::jsonp($result,$jsoncallback);
            return $result;            
       }
        /**
        *  输出jsonp
        *  @param  string   $result       输出的json信息    
        *  @param  string   $jsoncallback 回调函数名   
        *  @return jsonp
        */
       protected static function jsonp($result,$jsoncallback){
           if(!preg_match('/^[A-Za-z](\w)*$/',$jsoncallback))$jsoncallback = 'jsoncallback';  
           return $jsoncallback . '('.$result.');';           
       }
	   
	    /**
        *  编码
        *  @param  arr|object   $data  要编码的对象  
        *  @return json
        */
       public static function encode($data){
          return json_encode($data,JSON_UNESCAPED_UNICODE);               
       }
	    /**
        * 解码
        *  @param  arr|object   $data  要编码的对象  
        *  @return json
        */
       public static function decode($data){
          return json_decode($data,true);               
       }
}