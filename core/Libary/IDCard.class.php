<?php 
/** 
 * 身份证专用验证类
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: IDCard.class.php 2016年3月25日 13:01:41Z $  
 */ 
class IDCard { 

    //检证身份证是否正确 
    public static function isCard($card) { 
        $card = self::getIDCard($card); 
        if (strlen($card) != 18) { 
            return false; 
        } 
        return self::checkIdCard($card);
    }

    /**
     * 功能：把15位身份证转换成18位
     *
     * @param string $idCard
     * @return newid or id
     */

   private static  function getIDCard($idCard) {
        // 若是15位，则转换成18位；否则直接返回ID
        if (15 == strlen ( $idCard )) {
            $W = array (7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2,1 );
            $A = array ("1","0","X","9","8","7","6","5","4","3","2" );
            $s = 0;
            $idCard18 = substr ( $idCard, 0, 6 ) . "19" . substr ( $idCard, 6 );
            $idCard18_len = strlen ( $idCard18 );
            for($i = 0; $i < $idCard18_len; $i ++) {
                $s = $s + substr ( $idCard18, $i, 1 ) * $W [$i];
            }
            $idCard18 .= $A [$s % 11];
            return $idCard18;
        } else {
            return $idCard;
        }
    }  
   /**
     * 功能：验证18位身份证号码正确性 根据GB/T7408
     *
     * @param string $idCard
     * @return newid or id
     */
   private static function checkIdCard($idcard){  
  
        // 只能是18位  
        if(strlen($idcard)!=18){  
            return false;  
        }  
      
        // 取出本体码  
        $idcard_base = substr($idcard, 0, 17);  
      
        // 取出校验码  
        $verify_code = substr($idcard, 17, 1);  
      
        // 加权因子  
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);  
      
        // 校验码对应值  
        $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');  
      
        // 根据前17位计算校验码  
        $total = 0;  
        for($i=0; $i<17; $i++){  
            $total += substr($idcard_base, $i, 1)*$factor[$i];  
        }  
      
        // 取模  
        $mod = $total % 11;  
      
        // 比较校验码  
        if($verify_code == $verify_code_list[$mod]){  
            return true;  
        }else{  
            return false;  
        }  
      
    } 
} 