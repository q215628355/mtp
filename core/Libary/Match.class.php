<?php
/** 
 * 处理字符验证
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: Match.class.php 2016年3月17日 13:01:41Z $  
 */ 
 
class Match 
{
	 
	/**
  * 判断是否是数字
	* @param $void  mixed 要判断的内容
  * @return bool  
  */
	public static function is_number($void){
		if(!is_array($void)){           
          return  preg_match('/^\d+$/i', $void);
       } 
       return false;		 
	}
   	/**
      * 判断是否是小数
      * @param $void  mixed 要判断的内容
      * @return bool  
      */
    
    public static function is_float($void,$number =2){
       if(strpos($void,'-')===0) $void = ltrim($void,'-');
       if(!is_array($void)){           
          $ret =  preg_match('/^([\d]+|([\d]+[.]?|[\d]+[.]?[\d]+))$/i', $void);
          if($ret){
              $void = explode('.',$void);
              if(empty($void[1])) return true;
              if(strlen($void[1]) > $number) return false;
              return true;
          }
       } 
       return false;	
    }
	/**
  * 判断是否是身份证号
	* @param $void  mixed 要判断的内容
  * @return bool  
  */
	public static function is_card($void){
	   if(!is_array($void)){           
         return  IDCard::isCard($void);
       } 
       return false;		 
	}
    /**
    * 判断是否是借记卡和储蓄卡号
	* @param $void  mixed 要判断的内容
    * 用Luhn算法校验信用卡及借记卡卡号
   * @return bool  
   */
    public static function is_bankCard($card){
        if(!is_array($card)){ 
            return BankUtil::isCard($card);
        } 
        return false;
    }
 /**
  * 判断是否是手机号
	* @param $void  mixed 要判断的内容
  * @return bool  
  */
	public static function is_mobile($void){
		if(!is_array($void) ){           
           return  preg_match("/^1[34578]{1}\d{9}$/",$void);
        } 
       return false;		 
	}
  
  /**
  * 判断是否是电话号码
	* @param $void  mixed 要判断的内容
  * @return bool  
  */
	public static function is_call($void){
		if(!is_array($void)){           
           return preg_match('/^\d{3,4}-\d{7,8}$/',$void);
       } 
        return false;		 
	}
  /**
  * 判断是否是电话号码或者手机号
	* @param $void  mixed 要判断的内容
  * @return bool  
  */
	public static function is_tel($void){
	   return (self::is_mobile($void)	 || self::is_call($void));
	}
  
  /**
  * 判断是否是中文姓名 UTF8专用
	* @param $void  mixed 要判断的内容
  * @return bool  
  */
	public static function is_chinese_name($void){
		if(!is_array($void)){  
          return NameSex::isName($void);   
       }
       return false;		 
	}
	 
	//是否为邮箱
	public static function is_email($void){
		if(!is_array($void)){
			return preg_match('/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/',$void);
		}
		return false;
	}
	
  /**
  * 判断是否是英文姓名
	* @param $void  mixed 要判断的内容
  * @return bool  
  */
	public static function is_english_name($void){
		if(!is_array($void)){           
           return preg_match('/^([a-zA-Z]{1,}\s[a-zA-Z]{1,}){1,20}$/',$void);
        }
        return false;		 
	}
  
  /**
  * 判断是否是正确的密码 返回密码强度  8-30位 数字 字母 和特殊符号三种组合 
  * 这些特殊字符不能使用: 空格,换行,制表符,半角引号,小于号,大于号,反斜杠
	* @param $void  mixed 要判断的内容
  * @return bool  
  */
	public static function is_password($void){
		if(!is_array($void)){
            if(preg_match('/[\s\r\n\t\"\'\<\>\\\]/',$void)) return -1;//这些特殊字符不能被注册
            if(preg_match('/^(?=.*[0-9])(?=.*[a-zA-Z])[a-zA-Z0-9]{8,30}$/',$void)) return 2;//数字和字母组合强度2 
            else if(preg_match('/^(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[^a-zA-Z0-9]).{8,30}$/',$void)) return 3;//全组合强度3
            else if(preg_match('/^(?=.*[0-9]).{8,30}$/',$void)) return 2;//数字和特殊组合强度2 
            else if(preg_match('/^(?=.*[a-zA-Z]).{8,30}$/',$void)) return 2;//字母和特殊组合强度2 
            else if(preg_match('/^.{8,30}$/',$void)) return 1;//单个强度1 
            else return 0;
        }
        return false;		 
	}
  
  /**
  * 判断是否是合法的注册账户 必须是英文和数字组合？
	* @param $void  mixed 要判断的内容
  * @return bool  
  */
	public static function is_register($void){
		if(!is_array($void)){           
          return preg_match('/^([a-zA-Z0-9]{3,}$/',$void);
        }
        return false;		 
	}
	
    
  /**
  * 判断是否是合法的域名 命名 ,域名命名规范必须是 小写字母数字和横线，且不能是横线开头和结尾，不能出现两个连续的横线
	* @param $void  mixed 要判断的内容
  * @return bool  
  */
	public static function is_domain($void){         
		if(!is_array($void)){         
          if(preg_match('/^[a-z0-9\-]{1,25}$/',$void)){              
              if(preg_match('/^\-/',$void)) return false;
              if(preg_match('/\-$/',$void)) return false;
              if(preg_match('/\-\-/',$void)) return false;
              return true;
          }
        }
        return false;		 
	}
    
    	/**
  * 判断是否是营业执照
	* @param $void  mixed 要判断的内容
  * @return bool  
  */
	public static function is_business($void){
        
		if(!is_array($void)){    
            $vlist = array(
                '/^[0-9A-HJ-NPQRTUWXY]{2}\d{6}[0-9A-HJ-NPQRTUWXY]{10}$/',//新三证合一
                '/^\d{15}$/' //老执照
            );
            foreach( $vlist as $match){                
               $ret =  preg_match($match, $void);
               if($ret) return true;
            }
          return  false;
       } 
       return false;		 
	}

    public static function is_qq($void){
        if(!is_array($void)){
            return  preg_match('/^[1-9]\d{4,10}$/', $void);
        }
        return false;
    }

    // 验证url
    public static function is_url($void)
    {
      if (!is_array($void)) {
        return preg_match('/^((ht|f)tps?):\/\/([\w\-]+(\.[\w\-]+)*\/)*[\w\-]+(\.[\w\-]+)*\/?(\?([\w\-\.,@?^=%&:\/~\+#]*)+)?/', $void);
      }
      return false;
    }
}