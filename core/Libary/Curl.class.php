<?php
//curl请求处理类
class Curl
{
    
    //使用fsockopen 打开一个URL,不等待返回,常用于PHP 异步处理
    //传递的地址必须是绝对地址
    // 远程请求（不获取内容）函数
    public static function asyncSock($url) {
          $host = parse_url($url,PHP_URL_HOST);
          $port = parse_url($url,PHP_URL_PORT);
          $port = $port ? $port : 80;
          $scheme = parse_url($url,PHP_URL_SCHEME);
          $path = parse_url($url,PHP_URL_PATH);
          $query = parse_url($url,PHP_URL_QUERY);
          if($query) $path .= '?'.$query;
          if($scheme == 'https') {
            $host = 'ssl://'.$host;
          }

          $fp = fsockopen($host,$port,$error_code,$error_msg,1);
          if(!$fp) {
            return array('error_code' => $error_code,'error_msg' => $error_msg);
          }
          else {
            stream_set_blocking($fp,true);//开启了手册上说的非阻塞模式
            stream_set_timeout($fp,1);//设置超时
            $header = "GET $path HTTP/1.1\r\n";
            $header.="Host: $host\r\n";
            $header.="Connection: close\r\n\r\n";//长连接关闭
            fwrite($fp, $header);
            usleep(1000); // 这一句也是关键，如果没有这延时，可能在nginx服务器上就无法执行成功
            fclose($fp);
            return array('error_code' => 0);
          }
    }
        
    //使用curl 打开一个URL,不等待返回,常用于PHP 异步处理
    //传递的地址必须是绝对地址
    // 远程请求（不获取内容）函数
   public static  function asyncCurl($url) {
          $ch = curl_init();
          curl_setopt($ch,CURLOPT_URL,$url);
          curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
          curl_setopt($ch,CURLOPT_TIMEOUT,1);
          $result = curl_exec($ch);
          curl_close($ch);
          return $result;
    }
    
    
    public static function curl_post($url='', $postdata=''){
        $ch = curl_init($url);        
        curl_setopt($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);      
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }    
   
   
    /**
     * cURL获取网页内容
     * @author huliangming<215628355@qq.com>  哥哥要变百度蜘蛛了
     * @param  [type] [param]
     * @return [type] [description]
     */
    public static function GetContent( $url )
    {
        $ch = curl_init();
        $ip = '220.181.108.91';  // 百度蜘蛛
        $timeout = 15;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_TIMEOUT,0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        //伪造百度蜘蛛IP  
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('X-FORWARDED-FOR:'.$ip.'','CLIENT-IP:'.$ip.'')); 
        //伪造百度蜘蛛头部
        curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $content = curl_exec($ch);
        if($content === false)
        {//输出错误信息
            $no = curl_errno($ch);
            switch(trim($no))
            {
                case 28 : $error = '访问目标地址超时'; break;
                default : $error = curl_error($ch); break;
            }
            return $error;
        }
        else
        {           
            return $content;
        }
    }
}