<?php 
/** 
 * Api调度文件
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: App.class.php 2016年3月17日 13:01:41Z $  
 */  
final class App{
    public static $group = null; 
    public static $appname = null; 
    public static $action = null;
    public static $classname = null;  
    public static $error = array();  
    public static $errorTpl =null;
    public static $errorType =null;
    public static $get =array();

    /**
    *  初始化
    *  @return void
    */
    public static function init(){
        self::$errorTpl = dirname(__file__).'/Tpl/Error.tpl';
        
        //注册核心类库自动载入方式
        spl_autoload_register('App::autoload');
        
        //设置警报器
        set_error_handler('App::errorHandler');
        //设置致命错误记录器
        register_shutdown_function('App::fatalErrorHandler');
        //session
        
        define('SESS_ID', se()->get_session_id());
        defined('ROOT') or  define('ROOT', C('WEBURL'));         
        //public路径
		defined('__PUBLIC__') or define('__PUBLIC__',C('DOMAIN').ROOT.'/Public');
        //imagepublic路径
		defined('__IMAGEPUBLIC__') or define('__IMAGEPUBLIC__',C('IMGURL').ltrim(ROOT,'/').'/Public'	);
		
        
         //将域名前缀存入常量
        defined('DOMAIN') or define('DOMAIN', self::getDomain());	
		//将根域名存入常量
		defined('ROOTDOMAIN') or define('ROOTDOMAIN', self::getRootDomain()); 
        
        //判断五种请求
        define('IS_AJAX', self::isAjax());
        define('IS_GET', self::isGet());
        define('IS_POST', self::isPost());
        define('IS_PUT', self::isPut());
        define('IS_HEAD', self::isHead());	      
              
        define('HTTP_USER_AGENT', self::getUserAgent());	
		
       		
        
   //分析URL获取appname和action
        self::url_deepen();
       
        //模板目录
        defined('VIEW_PATCH') or define('VIEW_PATCH',  BASE_PATH.'/'.(C('TPL_PATCH') ? C('TPL_PATCH') : 'View').(self::$group ? '/'.self::$group  : ''));        
 
      
        if(!ini_get('zlib.output_compression') && C('OUTPUT_ENCODE')) ob_start('ob_gzhandler');
       
		//输出当前路径
		define('__SELF__', U(self::$appname.'/'.self::$action));
        define('__URL__', 'http://'.$_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
        
        //使用import载入控制器
        self::import(); 

        if(!class_exists(self::$classname)){
            self::show404('class not find!');
        }
        
        $app = new self::$classname;
        try{
            self::invokeAction($app,self::$action);
        } catch (ReflectionException $e) {    
        	   
            self::show404('not a valid function file!');
        }       
       return ;
        
    }  
    /**
    *  执行方法
    *  @param  string $module 控制器名称
    *  @param  string $action 方法名称
    *  @return void
    */
    public static function invokeAction($module,$action){
        if(!preg_match('/^[A-Za-z](\w)*$/',$action)){
            // 非法操作
            throw new ReflectionException();
        }
        //执行当前操作
        $method =   new ReflectionMethod($module, $action);
        if($method->isPublic() && !$method->isStatic()) {
            $class  =   new ReflectionClass($module);
            // 前置操作
            if($class->hasMethod('_before_'.$action)) {
                $before =   $class->getMethod('_before_'.$action);
                if($before->isPublic()) {
                    $before->invoke($module);
                }
            }
            $method->invoke($module);
            // 后置操作
            if($class->hasMethod('_after_'.$action)) {
                $after =   $class->getMethod('_after_'.$action);
                if($after->isPublic()) {
                    $after->invoke($module);
                }
            }
        }else{
            // 操作方法不是Public 抛出异常
            throw new ReflectionException();
        }
    }
   /**
    *  自动载入APP类库文件
    *  @param  string $appname 类库名   
    *  @return void
    */   
    public static function import($appname=''){
        //处理Controller
        $appname = empty($appname) ?  self::$appname : $appname;
        $classname = ucfirst($appname).'Controller';
        $file = self::$group ? APP_PATH.'/'.self::$group.'/'.$classname.'.class.php' :  APP_PATH.'/'.$classname.'.class.php';

        if(file_exists($file)){
           require_once($file); 
           self::$classname = $classname;           
            
        }else{            
            self::show404('not a valid Controller file!');
        }
        
    } 
   /**
    *  自动载入核心类库文件
    *  @param  string $classname 类库名   
    *  @return void
    */ 
    public static function autoload($classname=''){

        //处理model  
        $loads = array("Models","Libary");
        $lo = false;
        foreach( $loads as $val){            
            $files[] = $file = CODE_PATH.'/'.$val.'/'.$classname.'.class.php';
            (file_exists($file) && require_once($file)) && $lo = true;                 
        } 
       // $lo === false  &&   self::show404("not a valid 'class::".$classname."' file!".$file);   
    }

    /**
    *  解析URL获取appname和action   
    *  @return void
    */ 
    protected static function url_deepen(){  
       $gp = C('URLGROUPNAME');
       $md = C('URLMOUDENAME');
       $ct = C('URLACTION');
	   $sux= C('REWRITE_SUFFIX');
       self::$get = $_GET;
       unset(self::$get[$md]);
       unset(self::$get[$ct]); 
       if(C('REWRITE')===true){   
           $get = array();   
           $get ['_SELF'] = $url = trim($_SERVER['QUERY_STRING'],'/');   
           $_url =  preg_replace('/^(.+?)(\&|\?)/','',$url);//去掉之前的        
           parse_str ($_url,self::$get);
//              dump($_SERVER['QUERY_STRING']);die;
           $url = preg_replace('/(\&|\?)(.*?)$/','',$url);//去掉参数
           $url = explode('.',$url);//去掉后缀
           if(count($url) >1){
                array_pop($url);
           }
           $url = implode('.', $url);      
           $url = explode('-', $url );   
          
           $url2 = array();
           if(isset($url[1])){            
               if(false === strpos($url[0],'/')){                
                   $get[$url[0]] = $url2[] = $url[1];                     
               }else{
                   $mod = explode('/', trim($url[0],'/') );                
                   $url[0]  = array_pop ($mod );       
               }
           }else if(isset($url[0])){                             
               $mod = explode('/', trim($url[0],'/') );  
           } 
           $group = '';            
           $appname = isset($mod[0]) ? $mod[0] : 'index'; 
           $action = isset($mod[1]) ? $mod[1] : 'index'; 
          
           //如果存在分组
           if(C('GROUP_LIST')){
               $list = explode(',',C('GROUP_LIST'));
               $group = isset($mod[0]) ? ucfirst($mod[0]) : '';   
               if(!in_array($group,$list)){  
                   $group = C('DEFAULT_GROUP') ? C('DEFAULT_GROUP') :  $list[0]; 
               }else{
                   $appname = isset($mod[1]) ? $mod[1] : 'index'; 
                   $action = isset($mod[2]) ? $mod[2] : 'index';  
               }     
           }    
         
           $url2[] = $get[$gp]  = humpencode($group);
           $url2[] = $get[$md]  = humpencode($appname);
           $url2[] = $get[$ct]  = humpencode($action);
         
          // foreach
           $i = 1;
           foreach($url as $v){               
               if($i %2 == 0){                  
                 $get[$url[$i-2]] = isset($url[$i-1]) ?$url[$i-1] : '';    
               }   
               $i++;               
           }
           $get ['_URL'] = array_merge($url2,$url);       
           $_GET = array_merge($get,$_GET);//兼容旧模式;     
       }
       $group   = $_GET[$gp] = isset($_GET[$gp]) ? $_GET[$gp] : '';
       $appname = $_GET[$md] = isset($_GET[$md]) ? $_GET[$md] : 'index';
       $action  = $_GET[$ct] = isset($_GET[$ct]) ? $_GET[$ct] : 'index';
       
       if(!preg_match('/^[A-Za-z](\w)*$/',$appname)) $appname =  'index';
       self::$group = $group;
       self::$appname = $appname;
       self::$action = $action; 
       $_REQUEST = array_merge($_REQUEST,$_GET);//兼容旧模式;              
    }    

    /**
     * 是否是AJAx提交的
     * @return boolean
     */
    public static function isAjax(){      
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            return true;
        }
        else
        {
            return false;
        } 
    }

    /**
     * 是否是GET提交的
     * @return boolean
     */
    public static function isGet(){
       return (isset($_SERVER['REQUEST_METHOD']) && strcasecmp($_SERVER['REQUEST_METHOD'],'GET')) ? true : false;
    }

    /**
     * 是否是POST提交
     * @return boolean
     */
    public static function isPost() {
      return (isset($_SERVER['REQUEST_METHOD']) && strcasecmp($_SERVER['REQUEST_METHOD'],'POST')) ? true : false;
    }  
    /**
     * 是否是PUT提交
     * @return boolean
     */
    public static function isPut() {
      return (isset($_SERVER['REQUEST_METHOD']) && strcasecmp($_SERVER['REQUEST_METHOD'],'PUT')) ? true : false;
    }     
     /**
     * 是否是HEAD提交
     * @return boolean
     */
    public static function isHead() {
      return (isset($_SERVER['REQUEST_METHOD']) && strcasecmp($_SERVER['REQUEST_METHOD'],'HEAD')) ? true : false;
    }
    /**
     * 获取域名前缀
     * @return string
     */
	public static function getDomain() {
        $domainArray = explode(".", $_SERVER['HTTP_HOST']);
        if(count($domainArray)>3){
            //若是IP访问则使用www 测试
            if( filter_var($_SERVER['HTTP_HOST'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) $secondDomain = 'www';
            else return NULL;
        }else if(count($domainArray)<3){       
            $secondDomain = 'www';      
        }
        else{          
            $secondDomain = addslashes($domainArray[0]);//获取域名的前缀       
        }        
        return $secondDomain;	
    }
	
	 /**
     * 获取根域名
     * @return string
     */
	public static function getRootDomain() {
       $domain = C('DOMAIN');	
	   $domain = explode('.', trim( preg_replace('/^(http:\/\/)|(https:\/\/)/','',$domain),'/'));
	   unset($domain[0]);
	   return implode('.',$domain );
	
    }
    /*
    * 该函数用来捕获警告，记录打印警告和致命错误
    * 如果你在入口文件中定义了 APP_DEBUG 为TRUE 那么 该函数会打印错误报告
    * 否则会将错误信息保存到日志中
    * $errno string 错误类型
    * $errstr string 错误消息
    * $errfile string 错误发生文件
    * $errline int 错误产生的行数
    */   
    public static function errorHandler($errno,$errstr,$errfile,$errline){  

             //if(APP_DEBUG == true ) print '发生了一个警告：位于<font color="red">'.$errfile.'</font>文件，第'.$errline.'行；<br > '.$errstr."\r\n";    
             self::save('警告',$errstr,$errfile,$errline);           
    }
    /*
    * 该函数用来捕获处理致命错误 fatalError
    * 将获得的错误信息交给 errorHandler 处理  
    */   
    public static function fatalErrorHandler(){
             $e = error_get_last();
             switch($e['type']){
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                     ob_end_clean();                  
                     self::save('致命错误',$e['message'],$e['file'],$e['line']);
                     APP_DEBUG == false  ?  self::notShowError(404) : '';                   
                     break;         
            }        
           APP_DEBUG == true  ?  (!empty(self::$error) ? include self::$errorTpl : '') : '';   
    }
    /*  
    * 将错误信息保存到日志中
    * $errno string 错误类型
    * $errstr string 错误消息
    * $errfile string 错误发生文件
    * $errline int 错误产生的行数
    */
    public static function save($errno,$errstr,$errfile,$errline){
              $arr = array(
                            '['.date('Y-m-d h-i-s').']',
                            $errstr,
                            $errfile,
                            'line:'.$errline,
                            'URL:'. $_SERVER["REQUEST_URI"],                            
                        );
             self::$error[] = '发生了一个'.$errno.';位于<font color="red">'.$errfile.'</font>文件，第'.$errline."行；<br >\r\n ".$errstr;
             //写入错误日志
             //格式 ：  时间  错误消息 文件位置 第几行
             error_log(implode(' ',$arr)."\r\n",3,BASE_PATH .'/temp/errlog/'.date("Y-m-d").'.log','extra');
    }
    //显示友好错误页面   
    public static function notShowError($errno = 404,$text=''){  
         send_http_status($errno); 
         $text = ($text == '') ?  send_http_status($errno,2) : $text;    
         include (dirname(__file__).'/Tpl/notShowError.tpl');
    }    
    public static function show404($msg=''){
        self::notShowError(404,$msg);
        die;
    }
	//获取客户端类型
     public static function getUserAgent(){
        if(!isset($_SERVER['HTTP_USER_AGENT'])) {
          return  isMobile() ? 'mobile' : 'pc';            
        }
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
		
        if (strpos($user_agent, 'Appcan') !== false)  return 'appcan';            
        if (strpos($user_agent, 'MicroMessenger') !== false)  return 'weixin';
        if (strpos($user_agent, 'MQQBrowser') !== false)  return 'qq';  
        
        if(!isMobile()) return 'pc';      
        if (strpos($user_agent, 'BIDUBrowser') !== false)  return 'baidu';   
        if (strpos($user_agent, 'SogouMSE') !== false)  return 'sougou';   
        if (strpos($user_agent, 'Mb2345Browser') !== false)  return '2345';   
        if (strpos($user_agent, 'Maxthon') !== false)  return 'aoyou';
        
        if (strpos($user_agent, '360chrome') !== false)  return '360';        
        if (strpos($user_agent, 'UCBrowser') !== false)  return 'uc'; 
        if (strpos($user_agent, 'Opera') !== false)  return 'Opera';   
        
        if ((strpos($user_agent, 'iPhone') !== false) && (strpos($user_agent, 'Safari') !== false))  return 'iPhone';        
            
        if (strpos($user_agent, 'Firefox') !== false)  return 'Firefox';
        if (strpos($user_agent, 'Chrome') !== false)  return 'Chrome';
        
        
         /*             
        if (strpos($user_agent, 'Safari') !== false)  return 'Safari';   
        */     
              
        return 'other';
    }
}