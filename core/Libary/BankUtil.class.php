<?php 
  
/**
 * @作者 huliangming<215628355@qq.com>
 * @类名称： BankUtil 
 * @类描述： 银行卡校验 ,通过银行的Bin号 来获取 银行名称   
 * @创建时间： 2017-1-11 下午16:38:22
 */  
class BankUtil {  
    private static $bankList = null;
    private static $bakName  = null;
    private static $cardTypeMap = null;
    //自动载入配置文件
    private static function loadConfig($name)
    {
        if(null === self::$$name ){           
            self::$$name = include 'BankUtil/'.$name.'.config.php'; 
        } 
    }
 
   //用Luhn算法校验信用卡及借记卡卡号
    public static function isCard($card){
        if(!preg_match('/^\d+$/',$card)) return false;
        $arr_no = str_split($card);
        $last_n = $arr_no[count($arr_no)-1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n){
            if($i%2==0){
                $ix = $n*2;
                if($ix>=10){
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                }else{
                    $total += $ix;
                }
            }else{
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $x = 10 - ($total % 10);
        if($x == $last_n){
            return true;
        }
        return false;
    }
   
     //使用淘宝接口,传入卡号 得到银行名称，卡别，图片 
    public static function getTabaoBank($idCard) {
        self::loadConfig('bakName');   
        self::loadConfig('cardTypeMap');          
        $url = "https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardBinCheck=true&cardNo=$idCard";   
        $json = file_get_contents($url);            
        $json =  json_decode($json,true);  
        if(isset($json['bank'])){            
            return  array(
               'validated'    => true,
               'idCard'       => $idCard,
               'bank'         => $json['bank'],
               'bankName'     => self::$bakName[$json['bank']] ??  '未知银行' ,
               'bankImg'      => "https://apimg.alipay.com/combo.png?d=cashier&t=".$json['bank'],  
               'cardType'     => $json['cardType'],  
               'cardTypeName' => self::$cardTypeMap[$json['cardType']] ?? '未知卡别' 
            );          
        }else{            
            return null;
        }
    }  
  
    //传入卡号 得到银行名称  
    public static function getNameOfBank($idCard) { 
        self::loadConfig('bankList');     
        $card_8 = substr($idCard, 0, 8);  
        if (isset(self::$bankList[$card_8])) { 
            return self::$bankList[$card_8];  
        }  
        $card_6 = substr($idCard, 0, 6);  
        if (isset(self::$bankList[$card_6])) {
            return self::$bankList[$card_6]; 
        }  
        $card_5 = substr($idCard, 0, 5);  
        if (isset(self::$bankList[$card_5])) { 
            return self::$bankList[$card_5];  
        }  
        $card_4 = substr($idCard, 0, 4);  
        if (isset(self::$bankList[$card_4])) { 
            return self::$bankList[$card_4]; 
        }  
        return '';   
    }  
}  