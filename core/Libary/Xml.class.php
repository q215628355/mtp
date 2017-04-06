<?php
/** 
 * 处理XML数据
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: Xml.class.php 2016年9月5日 13:01:41Z $  
 */ 

class Xml{
    
    
    
    public $name = "root";
    
    public function __construct($name=''){   
        if($name ) $this->name=$name;     
    }    
    
    //将数组转换为XML
    public function arrayToXml($arr){ 
        $xml = "<".$this->name.">"; 
        foreach ($arr as $key=>$val){ 
            if(is_array($val)){ 
              $xml.="<".$key.">".$this->arrayToXml($val)."</".$key.">"; 
            }else{ 
               $xml.="<".$key.">".$val."</".$key.">"; 
            } 
        } 
        $xml.="</".$this->name.">"; 
        return $xml; 
    }

    //将XML转换为数组
   public function xmlToArray($xml){ 
  
         //禁止引用外部xml实体 
         
        libxml_disable_entity_loader(true); 
         
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA); 
         
        $val = json_decode(json_encode($xmlstring),true); 
         
        return $val; 
     
    } 

}