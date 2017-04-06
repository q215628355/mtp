<?php			
/** 
 * 核心入口文件
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: init.php 2016年3月17日 13:01:41Z $  
 */ 

header("Content-type:text/html;charset=utf-8");
// 检测PHP环境
if(version_compare(PHP_VERSION,'7.0.0','<'))  die('require PHP > 7.0.0 !');

/* 取得当前根目录 */
if(!defined('IS_CLI')){
    define('IS_CLI',(substr(PHP_SAPI, 0, 3) == 'cli'));    
}
if (__FILE__ == '')
{
    die('Fatal error code: 0');
}
 /* app目录 */


defined('APP_DEBUG') or  define('APP_DEBUG',false);  

defined('BASE_PATH') or  define('BASE_PATH',str_replace('\\', '/',dirname(dirname(__file__))));//设置根目录 

//app
defined('APP_PATH') or  define('APP_PATH',BASE_PATH.'/App'); 

//includes目录
defined('CODE_PATH') or define('CODE_PATH',BASE_PATH.'/core');  

defined('ROOT_PATH') or  define('ROOT_PATH',BASE_PATH.'/public');//设置站点根目录 
 
defined('DATA_PATH') or  define('DATA_PATH',ROOT_PATH.'/data');//附件保存目录



//报告错误?
true === APP_DEBUG ?  error_reporting(E_ALL) : error_reporting(0);


if(!IS_CLI){
        //设置失败反馈邮箱
    $_SERVER['SERVER_ADMIN'] = 'andyhlm@163.com';
    define('PHP_SELF','http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]);    
}


/* 初始化设置 */
@ini_set('memory_limit',          '512M');
@ini_set('session.cache_expire',  18000);
@ini_set('session.use_trans_sid', 1);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    1);
@ini_set('display_errors',        1);

require(CODE_PATH . '/config.php');
require(CODE_PATH . '/constant.php');

if (PHP_VERSION >= '5.1')
{
    date_default_timezone_set("PRC");
}

require(CODE_PATH . '/common.php');
if(C('REQUEST_VARS_FILTER')){
	// 全局安全过滤
	array_walk_recursive($_GET,		'_filter');
	array_walk_recursive($_POST,	'_filter');
	array_walk_recursive($_REQUEST,	'_filter');
}
$_SERVER['HTTP_HOST'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : parse_url(C('DOMAIN'),PHP_URL_HOST);
if(IS_CLI){
    require(CODE_PATH . '/CliApp.class.php');
    CliApp::init();
}else{
	 
    require(CODE_PATH . '/App.class.php');
    App::init();
}
 

