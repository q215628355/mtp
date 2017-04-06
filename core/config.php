<?php
/** 
 * 核心配置文件
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: config.php 2016年3月17日 13:01:41Z $  
 */  
return array(
     //本站域名
    'DOMAIN'  =>'http://www.test.com',
    
    //图片域名
    'IMGURL'  =>'http://www.test.com/',
    
    //项目路径域名 后面不带/
    'WEBURL' => '',
    //
    'URLGROUPNAME'=>'grp', //URL中传递的分组名称
    'URLMOUDENAME'=>'mdl', //URL中传递的模型名称
    'URLACTION'=>'act', //URL中传递的方法名称

    
    'DB_CONFIG' =>array(    
        'default'=> array(
            'DB_DSN'  => 'mysql:host=127.0.0.1;port=3306;dbname=ccp_object;',// 192.168.31.20
            'DB_USER'  => 'root',
            'DB_PWD'  => 'root',//  
            'DB_PREFIX'  => 'pos_'
        ),        
    ),
    
    
    'DB_SEICE_PREFIX'=>'##__', //表前缀替代符，在SQL查询中用到，默认为##__
    //Redis
    'REDIS_OPEN' => true,
    'REDIS_HOST' => '127.0.0.1',
    'REDIS_PORT' => 6379,
    'REDIS_AUTH' => '',   
    
    'REQUEST_VARS_FILTER' => true,
     //rewrite
    'REWRITE' => true,
	'REWRITE_SUFFIX'=>'.html', //url地址后缀
	
    //默认过滤方法 可自定义
    'DEFAULT_FILTER' =>'htmlspecialchars',
    
    //是否开启模板缓存，开启后可能影响部分业务,可在控制器头部使用 C('TPL_CACHE',false); 单独关闭
    
    'TPL_CACHE'=>false,
    'TPL_CACHE_PATCH'=>'./temp/cache',//缓存保存的目录    
    'TPL_CACHE_TIME'=>60,//缓存失效时间，默认为60秒
    'OUTPUT_ENCODE' =>true,
    'ERROR_TPL_NAME'=>'default:public:error',//前台错误显示页的模板，非逻辑。指定 view下的 default/public/error.html
    
    //COOKIE
    
    'COOKIE_PATCH' => '/',
    'COOKIE_DOMAIN' => 'test.com',  
      'THEME_LIST' => array(
       0=>'default'         
    ),
    //默认模板
   'DEFAULT_THEME' => 'default',
   //模板文件名后缀
   'HTML_SUFFIX'   => '.html',
   //模板保存目录
   'TPL_PATCH'     => 'View',
   //样式保存目录
   'STYLE_PATCH'   => 'statics',   
   
   //项目分组设置
   'GROUP_LIST'    =>'Home,Mobile',
   'DEFAULT_GROUP' =>'Home'
);