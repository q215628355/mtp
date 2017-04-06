<?php
/** 
 * 模型基类
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: Model.class.php 2016年3月17日 13:01:41Z $  
 */ 
abstract class Model{
    
    /**
    *  连接数据库操作,该方式仅限在模型中使用，不得在控制器中使用
    *  @param  string $table   设置一个表名 不带前缀
    *  @return class
    */
    protected static function db($table='',$config='default'){     
        static $db=array();    
        $dbConfig = C('DB_CONFIG');
        $config = isset($dbConfig[$config]) ? $config : 'default';
        if(!isset($db[$config])){        
            $db[$config] =  new Pdo_Mysql($dbConfig[$config]);  
        }
        if(!empty($table)) {
            $db[$config]->clear();
            $db[$config]->table($table);            
        }
        return $db[$config];
    }
  
}