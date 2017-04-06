<?php 
/** 
 * Api调度文件,该API只会载入指定的外部类,用于CLI模式
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: CliApp.class.php 2016年5月17日 13:01:41Z $  
 */  
if(!IS_CLI) die('please to cli run');
final class CliApp{
    public static $appname = null; 
    public static $action = null;
    public static $classname = null;  

    /**
    *  初始化
    *  @return void
    */
    public static function init(){       
        //注册核心类库自动载入方式
        spl_autoload_register('CliApp::autoload');      
        define('ROOT', C('WEBURL'));
        define('ROOTDOMAIN', self::getRootDomain());	
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
        //$lo === false  &&   exit("not a valid 'class::".$classname."' file!".$file);   
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
}