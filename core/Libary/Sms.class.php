<?php 
//

/** 
 * 阿里云短信发送接口
 * @copyright  Copyright (c) 2015-2016
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: Sms.class.php 2017年3月15日 09:34:43Z $  
 */  
class Sms{
    const HOST    =  "http://sms.market.alicloudapi.com";
    const SMS_PATH =  "/singleSendSms";
    const APPCODE = '6f7711b7325242e5975c836722a68ba8';
    const SIGNNAME = '胖达点餐';
    
    private  $error = ['error'=>0,'message'=>'发送成功'];
  
    //模板CODE设置，模板必须与线上设置一致    
    private $template=[  
              //模板code           //变量名称    //模板详情
        1 => ['code'=>'SMS_55990022', 'name'=>'verifycode','content'=>'您好，您的验证码是${verifycode}，有效期10分钟。'],
    ];
    //发短信
    public function send(int $template,$mobile,array $content){  
    
        //若指定的不要在列表中 
        $tpl = $this->template[$template] ?? '';        
        if(!$tpl) {
            $this->setError(10001);            
            return false;            
        }
        $mobile = (array)$mobile;
        foreach($mobile as $k=>$v){
           if(!$v) unset($mobile[$k]); 
           $count = $this->getMobileCount($v); 
           if( $count >= 3){               
             $this->setError(10007);            
             return false;  
           }           
        }
        if(!$mobile){
           $this->setError(10002);            
           return false; 
        }        
        $mobiles = join(',',$mobile);    
        $arr =   array_key_new($content,$tpl['name']);  
        if(!$arr){
           $this->setError(10003);            
           return false;
        }        
        $ParamString =  json_encode($arr);  
        //实际收到的内容
        $sendcontent = $tpl['content'];        
        foreach($arr as $key => $v){
            $sendcontent  = str_replace( '${'.$key.'}',$v,$sendcontent);
        }   
        //echo $ParamString;die;
        $data = array(
            'ParamString'  => $ParamString,
            'RecNum'       => $mobiles,
            'SignName'     => self::SIGNNAME,//签名名称
            'TemplateCode' => $tpl['code'],//模板code
        );       
        $querys = http_build_query($data);
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . self::APPCODE);
        $url = self::HOST . self::SMS_PATH . "?" . $querys;
        $body = $this->curl($url,'GET',$headers,self::HOST);       
        $json = json_decode($body,true);
        if(!$body || !$json){
            $this->setError(10005);
            return false;
        }
        if($json['success']) {
            $this->setMobileSms($mobile,$sendcontent);
            $this->setError(0);
            return true;
        }        
        $this->setError(10006,$json['message']);
        return false; 
    }
    //获取错误信息
    public function getError(){
        
        return  $this->error;
    }
    //设置错误信息
    private function setError($code=0,$msg=''){
        $errcode = $code;        
        switch($code){
            case 10001:               
                $msg = '模板不正确';
                break;
            case 10002:             
                $msg = '手机号不能为空';
                break;
            case 10003:             
                $msg = '发送内容不能为空';
                break;    
            case 10004:          
                $msg = '发送内容太长';
                break;
            case 10005:               
                $msg = '接口调用失败:Invalid AppCode?';
                break;
            case 10006:
                $msg  ='接口调用异常:'.$msg;
                break;  
            case 10007:               
                $msg = '该手机号发送太频繁';
                break;
            default:
                $errcode = 0;
                $msg = '发送成功';
        }
        $this->error =  ['error'=>$errcode,'message'=>$msg];
    }
    //获取手机号24小时内发送次数
    private function getMobileCount($mobile){
        $where = ['mobile'=>$mobile];
        $endtime = time();
        $strtime = time()-86400;
        $where[] = "send_time >=".$strtime . " and  send_time <=".$endtime  ;
        return DB('sms')->where($where)->count();
    }
    
    //写入发送日志
    private function setMobileSms($mobile,$content){
        $data= [];
        foreach($mobile as $v){            
           $data[] = array(
            'mobile'=>$v,
            'content'=>$content,
            'send_time'=>time(),
            'send_ip'=>get_client_ip(),           
           ); 
        }       
        return DB('sms')->addd($data);
    }
    //curl请求
    private function curl($url,$method="GET",$headers=[],$host){        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }        
        return curl_exec($curl);
    }
    
    
}
    