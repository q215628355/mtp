<?php
/** 
 * 核心函数库
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: common.php 2016年3月17日 13:01:41Z $  
 */  


/**   
* 读取全局配置 
* @author Huliangming<215628355@qq.com>
* @param  string $name 配置键名
* @return mixed 
*/ 
function C($name,$value=null){
    static $config =null;        
    if(null===$config) $config = include(CODE_PATH . '/config.php'); 
    if($value !==null )$config[$name] = $value;    
    return  isset($config[$name]) ? $config[$name] : null ;   
}
/**
 * 简单COOKIE存取函数实现 
 * @author Huliangming<215628355@qq.com>
 * @param string $name 要存取的键名 必须
 * @param string $value 要保存的内容 可选 不传此参数 将为获取值 传为null 则为删除值,注意存储一个非NULL空值 将存储失败
 * @param array $params 设置本次cookie存储的expire,path,domain,secure 不传将采用默认设置
 * @return mixed 
 */
 
 function cookie($name,$value=false,$params = array()){
	 if(false===$value){
		 if(isset($_COOKIE[$name])) return $_COOKIE[$name];		 
		 return null;	 
	 }
	$COOKIE_PATCH =  C('COOKIE_PATCH');
	$COOKIE_DOMAIN =  C('COOKIE_DOMAIN');
	$expire = isset($params['expire']) ? (int)$params['expire'] : 0;
	$path =  isset($params['path']) ? $params['path'] : (empty($COOKIE_PATCH) ? '/' : $COOKIE_PATCH);		  
	$domain =  isset($params['domain']) ? $params['domain'] : (empty($COOKIE_DOMAIN) ?  null : $COOKIE_DOMAIN);
    $secure = isset($params['secure']) ? ($params['secure'] === true ? true : false) : false;
	if($expire > 0 ) $expire +=time();
 	if(null === $value){	
		setcookie($name, '0', time()-3600 , $path, $domain,$secure);	
		$_COOKIE[$name] = null;
		unset($_COOKIE[$name]);
		return;		
	}	
	if($value == '') return false; //传递空字符串会删除整个cookie
	return setcookie($name, $value, $expire, $path, $domain,$secure);	        
}

/**
 * 简单SESSION存取函数实现 
 * @author Huliangming<215628355@qq.com>
 * @param string $name 要存取的键名 必须
 * @param string $value 要保存的内容 可选 不传此参数 将为获取值 传为null 则为删除值
 * @return mixed 
 */
 
 function session($name,$value=false){
	 if(false===$value){
		 if(isset($_SESSION[$name])) return $_SESSION[$name];		 
		 return null;	 
	 }
 	if(null === $value){	
		$_SESSION[$name] = null;
		unset($_SESSION[$name]);
		return;		
	}		
	return $_SESSION[$name] = $value;	        
}

/**
 * URL重定向
 * @author Huliangming<215628355@qq.com>
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg='') {
    //多行URL地址支持
    $url        = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

/**
 * 获取客户端IP地址
 * @author Huliangming<215628355@qq.com>
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装） 
 * @return mixed
 */
function get_client_ip($type = 0,$adv=true) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 发送HTTP状态
 * @author Huliangming<215628355@qq.com>
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code,$return = 1) {
    static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
    );
    if(isset($_status[$code])) {
        if($return==2) return $_status[$code];
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:'.$code.' '.$_status[$code]);
    }
    return '';
}

/**
 * DB操作数据库,不建议使用此方式，建议将数据库操作移到模型中，使用self::db 进行查询
 * @author Huliangming<215628355@qq.com>
 * @param string $dbname 指定数据库名称不带前缀
 * @param array $config 连接配置数组
 * @return class
 */
function DB($dbname='',$config='default'){
    static $db=array();    
    $dbConfig = C('DB_CONFIG');
    $config = isset($dbConfig[$config]) ? $config : 'default';
    if(!isset($db[$config])){        
        $db[$config] =  new Pdo_Mysql($dbConfig[$config]);  
    }
    if(!empty($dbname)) $db[$config]->clear();
    return $db[$config]->table($dbname);      
}
/**
 * RD操作redis
 * @author Huliangming<215628355@qq.com>
 * @param array $config 连接配置数组
 * @return class
 */
function RD($config=array()){
    static $rd = null;
    if(null===$rd){
        $rd = new RedisRac(); 
        if(empty($config)) $config= array('host'=>C('REDIS_HOST'),'port'=>C('REDIS_POST'),'auth'=>C('REDIS_AUTH'));
        $res = $rd->connect($config);   
    }
    return $rd;     
}
/**
 * 抛出异常处理
 * @author Huliangming<215628355@qq.com>
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @return void
 */
function E($msg, $code=0) {
    die(json_encode(array('message'=>$msg,'error'=>$code),JSON_UNESCAPED_UNICODE));
}
/**
 * 默认全局自动过滤函数
 * @param mixed $value 要过滤的内容
 * @return void
 * @author Huliangming<215628355@qq.com>
 */
function _filter(&$value){
	// TODO 其他安全过滤
    if(!get_magic_quotes_gpc())$value = addslashes($value);
	// 过滤查询特殊字符
    if(preg_match('/^(OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i',$value)){
        $value .= ' ';
    }
    $prefix =   C('DB_SEICE_PREFIX') ? :  '##__';  
    $value  = str_replace($prefix,'',$value);
}
/**
 * I函数使用的默认过滤,支持单引号转义
 * @author Huliangming<215628355@qq.com>
 * @param $string
 * @return string
 */

function _htmlspecialchars(&$string){
   $string = htmlspecialchars($string,ENT_QUOTES);
   return $string;
} 
/**
 * 过滤内容转化为文本 I函数可选 用法 I('post.name','','htmltotxt')
 * @author Huliangming<215628355@qq.com>
 * @param $string
 * @return string
 */
function htmltotxt(&$string)
{
    $string = htmlspecialchars_decode($string);//先 反转为正常html
	$string = str_replace ( '"', '＂', $string );	
    $string = str_replace ( "'", '＇', $string );
    $string = htmlspecialchars(strip_tags ( $string ));     
    return $string;
}
/**
 * 过滤内容转化为数字,支持长数字 I函数可选 用法 I('post.name','','_int')
 * @author Huliangming<215628355@qq.com>
 * @param $string
 * @return string
 */
function _int(&$string)
{   
    $string = explode('.',$string);
    $string = preg_replace('/[^\d]+/isu','',$string[0]);
    if(empty($string)) $string = 0;
    return  $string;  
}
/**
 * 将数字限定在 指定 范围
 * @author Huliangming<215628355@qq.com>
 * @param $string
 * @return string
 */
function int_range($number,int $min=0,int $max=5)
{   
    $number = intval($number);
    if($number < $min) $number = $min;
    if($number > $max) $number = $max;
    return $number;  
}
/**
 * 将数字限定在 0-5 范围 适用于评论分数
 * @author Huliangming<215628355@qq.com>
 * @param $string
 * @return string
 */
function _int05(&$string)
{   
    $string = int_range($string,0,5);
    return $string;  
}

/**
 * 去掉所有特殊字符
 * @author Huliangming<215628355@qq.com>
 * @param $string
 * @return string
 */
function _special(&$string){
    
     $string = preg_replace("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/isu",'',$string);
     return  $string;
}
/**
 * 去掉所有逗号间隔的关键字里的特殊字符,并去掉重复值和空值
 * @author Huliangming<215628355@qq.com>
 * @param $string
 * @return string
 */
function _keywords(&$keywords,$split=","){
	$arr = explode(',',$keywords);
	$arr = array_unique($arr);
    foreach($arr as $k=>$val){
		$arr[$k] = _special($val);
		if(!$arr[$k]) unset($arr[$k]);
	}
	$keywords = join($split,$arr);
	return $keywords ;
}

/*
 * 过虑一个逗号间隔的字符串，将其转换为数组，并去掉重复值和空值
 * @author Huliangming<215628355@qq.com>
 * @param $string
 * @return array
 */
function str2Arr(&$string,$empty=true){
    if(is_array($string)) return $string;
    $string = explode(',',$string);
    $string = array_unique($string);
    if(!$empty)return $string;
    foreach($string as $k=>$val){
        if(empty($val)) unset($string[$k]);        
    }
    return $string;    
}
/*
 * 强制转换为数组，哪怕没有定义
 * @author Huliangming<215628355@qq.com>
 * @param $string
 * @return array
 */
function _array(&$name){        
        
    return isset($name) ? (array)$name : array();
}
/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @author Huliangming<215628355@qq.com>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
* @param mixed  $isarr 是否支持数组过滤
 * @return mixed
 */
 function I($name,$default='',$filter=null,$isarr=false) {    
    if(strpos($name,'.')) { // 指定参数来源
        //判断参数$name中是否包括.号
        list($method,$name) =   explode('.',$name,2);
        //如果包括.号将.号前后分隔，并且分别赋值给$method以及$name
    }else{ // 默认为自动判断
        //如果没有.号
        $method =   'param';
    }
    switch(strtolower($method)) {//将$method转换为小写
        //如果$method为get，则$input为$_GET
        case 'get'     :   $input =& $_GET;break;
        //如果$method为get，则$input为$_POST
        case 'post'    :   $input =& $_POST;break;
        //如果为put，则将post的原始数据转参数给$input
        case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
        //如果是param
        case 'param'   :
            //判断$_SERVER['REQUEST_METHOD']
            switch($_SERVER['REQUEST_METHOD']) {
                //如果为post，则$input的内容为$_POST的内容
                case 'POST':
                    $input  =  $_POST;
                    break;
                //如果为PUT.则input的内容为PUT的内容
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $input);
                    break;
                //默认为$_GET的内容
                default:
                    $input  =  $_GET;
            }
            break;
        //如果$method为request，则$input为$_REQUEST
        case 'request' :   $input =& $_REQUEST;   break;
        //如果$method为session，则$input为$_SESSION
        case 'session' :   $input =& $_SESSION;   break;
        //如果$method为cookie，则$input为$_COOKIE
        case 'cookie'  :   $input =& $_COOKIE;    break;
        //如果$method为server，则$input为$_SERVER
        case 'server'  :   $input =& $_SERVER;    break;
        //如果$method为globals，则$input为$GLOBALS
        case 'globals' :   $input =& $GLOBALS;    break;
        //默认返回空
        default:
            return NULL;
    }
   if(isset($input[$name])) { // 取值操作
        $data       =   $input[$name];     
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');      
        if($filters) {
            $filters    =   explode(',',$filters);
            foreach($filters as $filter){
                if(function_exists($filter)) {
					if(is_array($data) && ($isarr === true)){						
					  $data   =  array_map_recursive($filter,$data);
					}else{
					  $data   =  is_array($data)? NULL :$filter($data); // 参数过滤
					}                    
                }else{   
                    $data   =   filter_var($data,preg_match('/^\d+$/',$filter)?$filter:filter_id($filter));
                    if(false === $data) {
                        return   isset($default)?$default:NULL;
                    }
                }
            }
        }
    }else{ // 变量默认值
        $data       =    isset($default)?$default:NULL;
    }
    return $data;
 }


/**
 * 简写URL
 * @author Huliangming<215628355@qq.com>
 * @access  public
 * @param   string  $app        执行程序 www@开头将 加上 根域名参数,比如 http://www.xxx.com@index/index 或者 www@index/index.php .php表示后缀名 
 * @param   array   $params     参数数组
 * @param   array   $addparam     附加参数数组，在伪静态时候附加参数不会写进URL里面 而是作为传统GET附加到 最后
 * @return  void
 */
function U($app='',$params=array(),$addparam=array()){    
    $domain = '';   
    if(false!==strpos($app,'@')){        
        $domain = explode('@',$app);
        $app = isset($domain[1]) ?$domain[1] : '';
        $domain = $domain[0];
        if(!preg_match('/\./',$domain)){
            
            $domain = $domain.'.'.ROOTDOMAIN;
        } 
        if(!preg_match('/^http:\/\/|https:\/\//',$domain))  {
            
            $domain = 'http://'.$domain;
        }        
    }
    $suffix = explode('.',$app); 
    $app = $suffix[0];   
    $left = strpos($app,'/');
      
    if(isset($suffix[1]))$suffix = '.'.$suffix[1];
    else $suffix = C('REWRITE_SUFFIX');
   
    $domain = rtrim($domain,'/').C('WEBURL');    
    $app = trim($app,'/'); 
    $app = empty($app) ? 'index' : $app;
    $patch = explode('/',$app);
    $grp = '';
    $mdl = humpencode($patch[0]);    
    $act = empty($patch[1]) ? 'index' : humpencode($patch[1]);   

  //如果存在分组
    if(C('GROUP_LIST')){
               $list = explode(',',C('GROUP_LIST'));
               $grp = isset($patch[0]) ? ucfirst($patch[0]) : '';   

               if(!in_array($grp,$list) && ($left !==0)){                        
                   $grp =  empty(App::$group) ? (C('DEFAULT_GROUP') ? C('DEFAULT_GROUP') :  $list[0]) : App::$group; 
               }else{
                   $mdl = isset($patch[1]) ? humpencode($patch[1]) : 'index'; 
                   $act = isset($patch[2]) ? humpencode($patch[2]) : 'index';  
               }     
               $grp = $grp == C('DEFAULT_GROUP') ? '' : strtolower($grp);
     }   
         
    if(C('REWRITE')==true){
        $url = $grp ? $grp .'/'.$mdl : $mdl;       
        if(!empty($params)){
            $par = array();
            foreach($params as $k=>$v){    
               $v = empty($v) ? 0 : $v;//不能为空
               $par[]= $k.'-'.$v;           
            }
            $url.='/'.(($act == 'index') ? '' : $act.'/').join('-', $par).$suffix;  
            if(!empty($addparam)) $url.='?'.http_build_query($addparam);       
        }else {
            $url.=  ($act == 'index') ? '' : '/'.$act.$suffix;
            if(!empty($addparam)) $url.='?'.http_build_query($addparam);  
        }  
    }else{  
        $url = '?'.($grp ? C('URLGROUPNAME').'='.$grp.'&' : '').C('URLMOUDENAME').'='.$mdl.'&'.C('URLACTION').'='.$act.(empty($params) ? '' : '&'.http_build_query($params));
        if(!empty($addparam)) $url.= '&'.http_build_query($addparam);  
    }      
    return  $domain.(($url == 'index' || $url == 'index/index') ? '/' : '/'.$url);     
} 

/**
 * se操作session
 * @author Huliangming<215628355@qq.com>
 * @return class
 */
function se(){
    static  $sess = null;
    if(null===$sess)$sess = new Session('sessions');
    return $sess;
}
/**
 * 从一维数组中取出指定的一个键值或多个键值 返回新的一维数组
 * @author Huliangming<215628355@qq.com>
 * @param array $arr 要处理的数组
 * @param string $value 要提取的键名，多个用逗号隔开
 * @return array
 */
function array_key_new($arr,$value) {
	$list = explode(',',$value);
	$new = array();
	foreach($list as $v){
		$new[$v] = isset( $arr[$v]) ? $arr[$v] : null;
	}
	return $new;	
}
/**
 * 给图片加上域名
 * @author Huliangming<215628355@qq.com>
 * @param array|string $img 
 * @param boolean $ismy
 * @return array
 */
function img_patch($img,$ismy = false,$domain=false){
  $imgurl = C('IMGURL');
  $weburl = rtrim( C('WEBURL'),'/').'/';
  if(true===$domain){
      $imgurl = rtrim( C('DOMAIN'),'/').'/';
  }
  $match = '/^data:image|http:\/\/|https:\/\//';
  if(is_array($img)){       
    foreach($img as &$url){ 
	  if(!preg_match($match,$url)){
		   $url =  ltrim($url,'/');		
           if(true === $ismy) $url = empty($url)? '' : $weburl.$url;
		   else $url = empty($url)? '' : $imgurl.$url; 
      }
    }        
    return $img;
  }   
  if(preg_match($match,$img)) return $img;
  $img =  ltrim($img,'/');
  if(true===$ismy)return empty($img)? '' : $weburl.$img;   	
  return empty($img)? '' :  $imgurl.$img;    
}
/**
 * 给图片地址加上宽高参数，用法在img_patch之后 , 例子 $img  = img_patch($img); $img = preg_img ( $img ,400,400); 
 * @author Huliangming<215628355@qq.com>
 * @param array|string $img 
 * @param boolean $ismy
 * @return array
 */
 function preg_img($img,$width=null,$height=null){
   if((null === $width) || (null === $height)) return $img;
   $imgurl = C('IMGURL'); 
   $imgurl = trim($imgurl,'/'); 
   if(strpos($img,$imgurl) !== 0) return $img;     
    //仅对images,data目录处理  
   $url = parse_url($img);
   $url = explode('/',trim($url['path'],'/'))[0];
   if( $url == 'images' || $url =='data'){
       $suffix = '-'.$width.'x'.$height;		   
       return suffixFile($img,$suffix);  
   }
   return $img;
 }
 /**
 * 给图片加上域名和宽高，仅限image域名
 * @author Huliangming<215628355@qq.com>
 * @param array|string $img 
 * @param boolean $ismy
 * @return array
 */
function img_patch2($img,$width=100,$height=0){  
  return preg_img ( img_patch($img) ,$width,$height);     
}
 /**
 * 给文件名加上后缀
 * @author Huliangming<215628355@qq.com>
 * @param  string $filename 
 * @param  string $str
 * @return string
 */
function suffixFile($filename,$str='-s'){
    $path_parts = pathinfo($filename);
    $extension = empty($path_parts["extension"]) ? '' : '.'.$path_parts["extension"];
    $basename = basename($path_parts["basename"],'.'.$path_parts["extension"]);   
    return $path_parts["dirname"].'/'.$basename.$str.$extension;   
 }

/**
 * 兼容 array_column PHP<=5.4版本
 * 取出数组中指定键名的列 php 5.5版本及以上版本才有的函数 用法 参见PHP5.5手册
 * @author Huliangming<215628355@qq.com>
 * @return array
 */

if(!function_exists('array_column')){ 
    function array_column($input, $columnKey, $indexKey=null){
        $columnKeyIsNumber    = (is_numeric($columnKey))?true:false; 
        $indexKeyIsNull       = (is_null($indexKey))?true :false; 
        $indexKeyIsNumber     = (is_numeric($indexKey))?true:false; 
        $result               = array(); 
        foreach((array)$input as $key=>$row){ 
            if($columnKeyIsNumber){ 
                $tmp= array_slice($row, $columnKey, 1); 
                $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null; 
            }else{ 
                $tmp= isset($row[$columnKey])?$row[$columnKey]:null; 
            } 
            if(!$indexKeyIsNull){ 
                if($indexKeyIsNumber){ 
                  $key = array_slice($row, $indexKey, 1); 
                  $key = (is_array($key) && !empty($key))?current($key):null; 
                  $key = is_null($key)?0:$key; 
                }else{ 
                  $key = isset($row[$indexKey])?$row[$indexKey]:0; 
                } 
            } 
            $result[$key] = $tmp; 
        } 
        return $result; 
    } 
}
/**
 * 兼容 array_map_recursive PHP<=5.4版本
 * 对数组递归使用函数 php 5.5版本及以上版本才有的函数 用法 参见PHP5.5手册
 * @author Huliangming<215628355@qq.com>
 * @return array 
 */
if(!function_exists('array_map_recursive')){ 
    //2016年4月14日 14:41:54 修正  改为 array_walk_recursive 方式递归
    function array_map_recursive(callable $func, array $arr) {
        array_walk_recursive($arr, function(&$v) use ($func) {
            $v = $func($v); 
        });
        return $arr;
    }    
}
/**
 * 将Html进行过滤 转化为MOBILE专用内容
 * @author Huliangming<215628355@qq.com>
 * @param string $html
 * @return string
 */
function htmlreplaceMobile($html){    
    $html = preg_replace_callback('/<img(.*?)src=\"(.*?)\"(.*?)>/i', function ($matches) { return '<img src="'.img_patch($matches[2]).'" />[br]'; },$html);  
    $html = strip_tags($html,"<img> <table> <th> <tr> <td>");//仅允许img标签
    $html = str_replace('[br]','<br />', $html);  
    return $html;    
}
/**
 * 相似度比较支持中文
 * @author Huliangming<215628355@qq.com>
 * @param string $html
 * @return string
 */    
function similar_text_cn($str1, $str2) {  
    preg_match_all("/./u", $str1, $arr);  
    $arr_1 = array_unique($arr[0]);  
    preg_match_all("/./u", $str2, $arr); 
    $arr_2 = array_unique($arr[0]);  
    $la = count($arr_1);
    $lb = count($arr_2);  
    $similarity = $lb - count(array_diff($arr_2, $arr_1));//相同的个数 
    if($similarity >0){
        if($lb  >$la ){
            $similarity /= $lb;
        }else if($la> 0 ){
            $similarity /= $la;
        }        
    }   
    return $similarity*100;  
 }   
/**
 * 设置AJAX的头部信息
 * @author Huliangming<215628355@qq.com>
 * @return void
 */
function AjaxHeader($time = 0){
    if($time>0){
          header("Cache-Control: public");
          header("Pragma: cache");      
          $ExpStr = "Expires: ".gmdate("D, d M Y H:i:s", time() + $time)." GMT";
          header($ExpStr);
    }
    header("Content-type:application/json");     
}
//set 304 cache
function SetHeader304($now_url,$tmp_time){
    $md5 = md5($now_url.$tmp_time);
    $etag = '"' . $md5 . '"';
    header('Last-Modified: '.gmdate('D, d M Y H:i:s',$tmp_time ).' GMT');
    header("ETag: $etag");
    if((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $tmp_time) || (isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) < $tmp_time) || (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag)){
        header("HTTP/1.1 304 Not Modified"); 
        exit(0);
    }  
}
/**
 * 判断上次发出的令牌 
 * @author Huliangming<215628355@qq.com>
 * @param string $token
 * @return void
 */
function isToken($token='',$key='',$del=true){ 
    if(!empty($token)) { 
        if(empty($key) ){ 
            if( isset($_SESSION['_TOKEN_'][0]) && $_SESSION['_TOKEN_'][0] === $token) {    
                if(true===$del) $_SESSION['_TOKEN_'][0] = 0;        
                return true;
            }
        }else{        
            if(isset($_SESSION['_TOKEN_'][$key]) &&  $_SESSION['_TOKEN_'][$key] === $token) {            
                if(true===$del)$_SESSION['_TOKEN_'][$key] = 0;        
                return true;
            }            
        }
    }
    return false;    
}
/**
 * 发出新令牌 
 * @author Huliangming<215628355@qq.com>
 * @return string
 */

function getToken($key=''){
     //发出新令牌    
    //令牌产生格式 当前时间 精确到 分 + 域名
    $token = md5(C('COOKIE_DOMAIN').date("Ymdhis"));  
    $_SESSION['_TOKEN_'][0] =$token  ;
    if(!empty($key) )$_SESSION['_TOKEN_'][$key] = $token;
    return  $token;      
}
/**
 * 简化打印函数
 * @author ysy<448970486@qq.com>
 */
function dump($val){
    echo '<pre>';
     print_r($val);
    echo '</pre>';
}
/**
 * 将下划线转换为驼峰式
 * @author Huliangming<215628355@qq.com>
 * @return string
 */

function humpencode($text){   
    return  preg_replace_callback(
        '/\_[a-zA-Z]{1}/',
        function($m){
            return ucfirst(trim($m[0],'_'));
        },
        $text
    ); 
}
/**
 * 将驼峰式转为下划线
 * @author Huliangming<215628355@qq.com>
 * @return string
 */

function humpdecode($text){    
    return  preg_replace_callback(
        '/([A-Z]+)/',
        function($m){
            return '_'.strtolower($m[0]);
        },
        $text
    );
}
/**
 * 实例化一个控制器
 * @author Huliangming<215628355@qq.com>
 * @return void
 */
function A($model,$action='index'){
    App::import($model);
    if(!class_exists(App::$classname)){            
         die('class not find!');
    }   
    $app = new App::$classname;    
    try{
         App::invokeAction($app,$action);
     } catch (ReflectionException $e) {            
         exit('not a valid function file!');
    }       
    return ;   
}
/**
 * 设置页面禁止缓存，防止后退
 * @author Huliangming<215628355@qq.com>
 * @return void
 */
function noCchche(){    
    header("Cache-control:no-cache,no-store,must-revalidate");
    header("Pragma:no-cache");
    header("Expires:0");    
}

/**
 * 请用 number_format2 替代 number_format
 * @author Huliangming<215628355@qq.com>
 * @return string
 */
function number_format2($number,$num=2){
    
    return number_format($number, $num, '.', '');
}
/**
 * 判断是否是微信浏览器打开
 * @author Huliangming<215628355@qq.com>
 * @return string
 */

function is_weixin(){
     return HTTP_USER_AGENT=='weixin';
}
/**
 * 判断是否是360浏览器打开
 * @author Huliangming<215628355@qq.com>
 * @return string
 */

function is_360(){
     return HTTP_USER_AGENT=='360';
}
/**
 * 格式化时间为XX前
 * @author Huliangming<215628355@qq.com>
 * @return string
 */
function mdate($time = NULL) {
    $text = '';
    $time = $time === NULL || $time > time() ? time() : intval($time);
    $t = time() - $time; //时间差 （秒）
    $y = date('Y', $time)-date('Y', time());//是否跨年
    switch($t){
     case $t == 0:
       $text = '刚刚';
       break;
     case $t < 60:
      $text = $t . '秒前'; // 一分钟内
      break;
     case $t < 60 * 60:
      $text = floor($t / 60) . '分钟前'; //一小时内
      break;
     case $t < 60 * 60 * 24:
      $text = floor($t / (60 * 60)) . '小时前'; // 一天内
      break;
     case $t < 60 * 60 * 24 * 3:
      $text = floor($time/(60*60*24)) ==1 ?'昨天 ' . date('H:i', $time) : '前天 ' . date('H:i', $time) ; //昨天和前天
      break;
     case $t < 60 * 60 * 24 * 30:
      $text = date('m月d日 H:i', $time); //一个月内
      break;
     case $t < 60 * 60 * 24 * 365&&$y==0:
      $text = date('m月d日', $time); //一年内
      break;
     default:
      $text = date('Y年m月d日', $time); //一年以前
      break; 
    }
        
    return $text;
}

/*
*
*函数功能：计算两个以YYYY-MM-DD为格式的日期，相差几天
*
*/
function getChaBetweenTwoDate($startdate,$enddate){
   //echo $enddate.'======'.$startdate.'<br />';
    $startdate = strtotime($startdate);
    $enddate = strtotime($enddate);
   // echo $enddate.'======'.$startdate;die;
    $date=floor(($enddate-$startdate)/86400);
    if($date>0) return ChineseNumber::ParseNumber($date).'天后';
    
    $hour=floor(($enddate-$startdate)%86400/3600);
    if($hour>0) return ChineseNumber::ParseNumber($hour).'小时后';
    
    $minute=floor(($enddate-$startdate)%86400/60);
    
    if($minute>0) return ChineseNumber::ParseNumber($minute).'分钟后';
    
    $second=floor(($enddate-$startdate)%86400%60);
    
    if($second>0) return ChineseNumber::ParseNumber($second).'秒后';
    
    return '立即';
}
//将时间进行转换
function getDay($hour){
    $date=floor($hour/24);
    if($date>0) return $date.'天内';
    return $hour.'小时内';
}

/*
*
*函数功能：计算退款时间还有多少天
*
*/
function getBackDay($time,$maxDay = 7,$getTime=false){

    $thistime = time();
    $maxTime = $time + (86400*$maxDay);
    $maxTime -= $thistime;
    $text = '';
    if($getTime=== true){
        
        return $maxTime >0 ? $maxTime  : 1;
    }
    if($maxTime >0){        
        $d=intval($maxTime/86400);
        $h=intval($maxTime%(86400)/3600);
        $m=intval($maxTime%(3600/24)/60);
        return "{$d}天{$h}小时{$m}分";
    }
    return '已超时';
}
/*
*
* 获取当前页面URL
*
*/
function getcurURL($type=1) 
{
    if($type==1){
        return 'http://'.$_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];        
        
    }else{
        
        return $_SERVER["REQUEST_URI"];
    } 
}
/*
*
* 获取上一页
*
*/
function getrefURL($type=1) 
{
    $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';  
    if($type==1 ||  $url==''){
        return  $url;        
        
    }else{         
        $path = parse_url($url,PHP_URL_PATH);
        $query = parse_url($url,PHP_URL_QUERY);
        if($query) $path .= '?'.$query;         
        return $path;
    } 
}
/*********判断是否手机浏览******/

function isMobile(){ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])){ 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 
    // 判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])){
        $clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        } 
    } 
    return false;
} 
//隐藏手机号中间四位
function hidtel($phone){    
    return substr($phone, 0, -8) . '****'. substr($phone, -4);
}
//隐藏姓名前一位
function hidname($name){  
    
    return  empty($name) ? '' : '*'. mb_substr($name, 1);
}
//隐藏银行卡前面N/4 位，显示后余数位
function hidcard($card){  
    $number = strlen($card);
    $num = $number % 4;
    $number = intval($number/4);
    if($num ==0){
        $number --;
        $num = 4;
    }  
    $output = ''; 
    for($i=1;$i<=$number;$i++){        
        $output.='**** ';        
    }
    return $output.substr($card,  - $num);    
}
//给一个数子加上正负号
function priceStatus($price){
    return $price >=0 ? '+￥'.$price : '￥-'.abs($price);
}

//创建编辑器
function wangEditor($name="mycontent",$content='',$upload=''){
    static $number = 0, $token = null, $wangEditor = [],$fileName = 'wangEditor',$patch = __PUBLIC__.'/wangEditor';   
    
    $token  =  $token  ? $token  : getToken('adminupload');//令牌
    $upload =  $upload ? $upload : U('Upload/wangEditor');//上传的地址	
    
    $css = '';
    if($number==0){        
$css =<<<DATA
    <link rel='stylesheet' type='text/css' href="{$patch}/css/wangEditor.min.css">
	<script src="{$patch}/js/wangEditor.min.js"></script>
DATA;
        
    }
    $number ++;
    $editor = 'wangEditor' . $number;    
    $name = empty($name) ? $editor : $name;
    if(isset($wangEditor[$name])) return '存在重复的命名'.$name.';初始化失败';
    $wangEditor[$name] = true;
	
	//$content = empty($content) ? '' : htmlspecialchars($content);
$html = <<<DATA
    $css
	<textarea name="{$name}" id="{$name}" style="height:200px;">{$content}</textarea>	
	<script>
    var {$editor} = new wangEditor("{$name}");
	{$editor}.config.uploadImgUrl = "{$upload}";
	{$editor}.config.uploadImgFileName = '{$fileName}',
	{$editor}.config.uploadParams = {token: "{$token}"};
	{$editor}.config.uploadHeaders = {'Accept' : 'text/x-json'};	
	//editor.config.hideLinkImg = true;
	{$editor}.config.zindex = 999999999;	
	{$editor}.create();
	$('.wangEditor-txt').css('min-height','200px').css('height','auto');
	</script>
DATA;
	return $html;
}
//异常模式删除文件
function tryUnlink($file){	
    if(file_exists($file)){
		try{
		    return unlink($file);
		}catch(Exception $e){
			return false;
		}
	}
	return true;
}
//获取模版路径
function view_patch($tpl){
    return  template::viewPatch($tpl);
}
/******************2016年11月29日 20:33:24 余下函数 写到 libary/functions.class.php 文件里 ***********************/