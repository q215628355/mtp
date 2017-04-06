<?php
/** 
 * Pdo_Mysql查询类
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: Pdo_Mysql.class.php 2016年3月17日 13:01:41Z $  
 */ 
 
class Pdo_Mysql{
    protected $pdo;
    protected $res;
    protected $config;
    public $consistent=array(); 
    protected $lastsql; 
    protected $redis= null;
    protected $redis_key= null;
    protected $redis_expire= null; 
    protected $redis_open  = null;  
    protected $preFix = null;// 表前缀占位符
    protected $_unset = true;//不可修改此参数
    /*构造函数*/
    function __construct($_config=array()){ 
        
        if(empty($_config)) $_config = C('DB_CONFIG')['default'];   

        $this->preFix = C('DB_SEICE_PREFIX') ? :  '##__';       
   
        $this->Config = $_config;     
        $this->connect();
        if(C('REDIS_OPEN')=== true){
            //redis没有开启并不会抛出异常
            $this->redis_open = true;        
            $this->redis = new Redis();
            $ret = $this->redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
            $ret  === false && $this->redis_open=false;
			      $AUTH = C('REDIS_AUTH');
            if(!empty($AUTH)){
                $ret = $this->redis->auth($AUTH);
                $ret  === false && $this->redis_open=false;                    
            }
        }
        register_shutdown_function(array(&$this,"destruct"));
    }
    //重连
    public function reload(){
        $this->pdo = null;
        $this->pdo = new PDO($this->Config['DB_DSN'], $this->Config['DB_USER'], $this->Config['DB_PWD']/*,array(PDO::ATTR_PERSISTENT => true)*/);
        $this->pdo->query('set names utf8;');
        
    }
     
    /*数据库连接*/
    public function connect(){    
        try {
           $this->pdo = new PDO($this->Config['DB_DSN'], $this->Config['DB_USER'], $this->Config['DB_PWD']/*,array(PDO::ATTR_PERSISTENT => true)*/);
           $this->pdo->query('set names utf8;');
        }
        catch (PDOException $e)
        {
           echo 'Connection failed: ' . $e->getMessage();
        }
       
        //把结果序列化成stdClass
        //$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        //自己写代码捕获Exception
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    //捕获错误，实现断线重连
    public function destruct(){
        $errstr = '';        
        $e = error_get_last();                   
       //如果是MYSQL连接超时           
        if(false !== strpos($e['message'],'MySQL server has gone away')){  
            ob_end_clean();
            $this->reload();           
        }     
     }
    /*数据库关闭*/
    public function close(){
        $this->pdo = null;
    }
    //查询前统一调用预处理语句
    private function executeSQL($sql){
        $preFix = $this->consistent['preFix'] ?? $this->preFix;
        $this->lastsql = $sql = str_replace($preFix,$this->Config['DB_PREFIX'],$sql);
        $res = $this->pdo->prepare($sql);       
        if(!empty($this->consistent['bindParam'])){
            $res->execute($this->consistent['bindParam']); 
        }else{
            $res->execute(); 
        }                     
        ob_start();
        $res->debugDumpParams();
        $this->lastsql = ob_get_contents();
        ob_end_clean();        
        if($this->_unset){
            $this->clear();
        }  
        $this->_unset = true;        
        return $res;
    }
	//执行SQL语句,返回true,false,结果集
    public function query($sql){  
        $res = $this->executeSQL($sql);       
      
        //尝试取多行结果集，若存在则为多行查询
        $data = false;
        //捕获影响条数
        try {
            $data = $res->fetchAll(PDO::FETCH_ASSOC );		 
        }
        catch (Exception $e)
        {
            $data = false;
        }	
        if ($data) {        
           return $data;	 
            
        }
             
        //捕获errcode	
        $errcode = 0;			 
        try {
            $errcode = $this->pdo->errorCode();			 
        }
        catch (Exception $e)
        {        
            $errcode = 0;
        }
        //判断成功与失败
        if( $errcode == 0){
            $number = 0;          
            try {
              $number =  $res->rowCount();            
            }
             catch (Exception $e)
            {
                $number = 0;
            }
           return $number; 
        } 
        return false;				
    } 
    //取多条结果集 支持redis
    public function fetchAll($sql){  
        //取redis        
        $data = $this->redisread($sql); 
        if($data !==null ) return $data;
//        echo $sql;die;
        $res = $this->executeSQL($sql);

        if($res) {			
			 $data = $res->fetchAll(PDO::FETCH_ASSOC );			
			 return $this->redissave($sql,$data);
		}
        return false;	        
    }
  
    //取单条结果集 支持redis
    public function fetch($sql=''){		
        //取redis        
        $data = $this->redisread($sql); 
        if($data !==null ) return $data;
//        echo $sql;die;
        $res = $this->executeSQL($sql);
        if($res) { 
			 $data = $res->fetch(PDO::FETCH_ASSOC );      
			 return $this->redissave($sql,$data);
		}
        return false;	
    }  
    
    //获取影响条数
    public function fetchColumn(){
        return $this->pdo->fetchColumn();
    }
    //获取最后插入的ID
    public function lastInsertId(){
        return $this->pdo->lastInsertId();
    }
    
     //获取最后执行的SQL
    public function getLastSql(){
        return $this->lastsql;
    }
     //设置表名
    public function tb($tablename){        
        return '`'.$this->Config['DB_PREFIX'].$tablename.'` ';
    }
    /*
    * 连贯操作实现         
    */
     //设置表名称,重置查询条件
    public function table($tablename){ 
        $this->consistent['table'] = $this->Config['DB_PREFIX'].$tablename;
        return $this;   
    }
    //redis
    public function redis($key='',$expire=0){
        if($this->redis_open===true){           
            $this->redis_key= $key;
            $this->redis_expire= $expire;  
        }           
        return $this;
    }
   
   //设置表别名
    public function _as($as){
        $this->consistent['as'] = $as;
        return $this;   
    }    
     //设置查询字段
    public function field($field){
        if(is_array($field))$this->consistent['field'] = implode(', ', $field);
        else $this->consistent['field'] =  $field;
        return $this;   
    } 
    
    //设置join
 
    public function join($join){   
        if(empty($this->consistent['join'])){
          $this->consistent['join'] = $join;
        }    
        else{
          $this->consistent['join'] .= ' '.$join;  
        } 
        return $this;   
    } 
     //设置groupby
    public function groupby($groupby){
        $this->consistent['groupby'] = $groupby;
        return $this;   
    }
    
    public function having($having){        
        $this->consistent['having'] = $having;
        return $this;   
    }
      //设置查询条件
    public function where($where){
        $this->consistent['where'] = '';
        if(!empty($where)){
            if(is_array($where)){
             foreach($where as $k=>$val){                 
                 if(preg_match('/^\d+$/',$k)) {
                    $this->consistent['where'] .= isset($val)  ? ' and '.$val : ''; 
                 }
                 else {
                     
                    $k = explode('.',$k);
                    if(isset($k[1])) $k =  $k[0].'.'.'`'.$k[1].'`';
                    else $k = '`'.$k[0].'`';
                    
                    $this->consistent['where'] .= isset($val)  ? ' and '.$k."='".$this->getValue($val)."'" : '';  
                     
                 } 
              }  
            }
            else if(is_string($where)){
               $this->consistent['where'] = "AND " . $where;    
            }            
        } 
        return $this;   
    } 
       //设置排序
    public function orderby($orderby){
        $this->consistent['orderby'] = empty($orderby) ? '' : 'ORDER BY '. $orderby;
        return $this;   
    } 
        //设置分页查询的limit 
    public function page($page,$size){
        if($page<1)$page = 1;
        $start = ($page-1)*$size;
        $this->consistent['limit'] = 'LIMIT '. $start.','.$size;
        return $this;   
    }
       //设置limit
    public function limit($start,$max=null){
        if($max==null)$this->consistent['limit'] = 'LIMIT '. $start;
        else $this->consistent['limit'] = 'LIMIT '. $start.','.$max;
        return $this;   
    }
    //设置唯一性插入的操作方法
    public function unique($name="IGNORE"){        
        $this->consistent['unique'] = $name;  
        return $this;         
    }
    //邦定参数
    public function bindParam($key,$value,$type=''){
        if($type=='like'){
            $value = '%'.$value.'%';
        }
        $this->consistent['bindParam'][$key] = $value;     
        return $this;
        
    }
    
    //设置表前缀占位符
    public function preFix($preFix){        
        $this->consistent['preFix'] = $preFix;
        return $this;
    }
        //查询条数   
    public function count($returnField='*'){    
        if(empty( $this->consistent['table'])) {
                die('please set table name!');
            } 
              else 
            {
                $table = $this->consistent['table'];
        }  
        $as = (empty($this->consistent['as'])) ? '' : ''. 'as '. $this->consistent['as']; 
            
        $join = (empty($this->consistent['join'])) ? '' : $this->consistent['join'];   
        $groupby = (empty($this->consistent['groupby'])) ? '' :  'group by '.$this->consistent['groupby'];    
        $alits =  (empty($this->consistent['groupby'])) ? '*' :  $this->consistent['groupby'];
        $having =  (empty($this->consistent['having'])) ? '' :  'HAVING '. $this->consistent['having'];         
        $where = (empty($this->consistent['where'])) ? '' : $this->consistent['where'];         
        if(!empty($groupby)) $sql =  "select count(*) as count from (SELECT $alits FROM $table $as $join where 1=1 $where $groupby $having ) a";
        else  $sql = "select count($returnField) as count from $table $as $join where 1=1 $where limit 1";  
        $data =  $this->fetch($sql);              
        return $data['count'];
    }
    //查询单条数据    
     public function find($returnField=null){    
        if(empty( $this->consistent['table'])) {
                die('please set table name!');
            } 
              else 
            {
                $table = $this->consistent['table'];
        }
        if($returnField ){
           $field = $returnField;
        }else{
           $field = (empty( $this->consistent['field'])) ? '*' : $this->consistent['field'];  
        }         
        $as = (empty($this->consistent['as'])) ? '' : ''. 'as '. $this->consistent['as'];  
        $join = (empty($this->consistent['join'])) ? '' : $this->consistent['join'];   
        $groupby = (empty($this->consistent['groupby'])) ? '' :  'group by '.$this->consistent['groupby'];
        $having =  (empty($this->consistent['having'])) ? '' :  'HAVING '. $this->consistent['having'];         
        $where = (empty($this->consistent['where'])) ? '' : $this->consistent['where'];    
        $orderby = (empty($this->consistent['orderby'])) ? '' : $this->consistent['orderby'];
        $data = $this->fetch("select $field from $table  $as $join where 1=1 $where $groupby $having $orderby limit 1"); 
        if($returnField) return isset($data[$returnField]) ? $data[$returnField] : false;
        return $data; 
    } 
      //查询多条数据    
     public function select($unset=true){   
        if(empty( $this->consistent['table'])) {
                die('please set table name!');
            } 
              else 
            {
                $table = $this->consistent['table'];
        }
        $field = (empty( $this->consistent['field'])) ? '*' : $this->consistent['field']; 
        $as = (empty($this->consistent['as'])) ? '' : ''. 'as '. $this->consistent['as'];  
        $join = (empty($this->consistent['join'])) ? '' : $this->consistent['join'];   
        $groupby = (empty($this->consistent['groupby'])) ? '' :  'group by '.$this->consistent['groupby']; 
        $having =  (empty($this->consistent['having'])) ? '' : 'HAVING '. $this->consistent['having'];  
        $where = (empty($this->consistent['where'])) ? '' : $this->consistent['where'];    
        $orderby = (empty($this->consistent['orderby'])) ? '' : $this->consistent['orderby'];
        $limit = (empty($this->consistent['limit'])) ? '' : $this->consistent['limit'];             
        return $this->fetchAll("select $field from $table $as $join where 1=1 $where $groupby $having $orderby $limit");                 
    }
      //分页查询
     public function selectLimit($sql, $num, $start = 0){
        if ($start == 0)
        {
            $sql .= ' LIMIT ' . $num;
        }
        else
        {
            $sql .= ' LIMIT ' . $start . ', ' . $num;
        }

        return $this->fetchAll($sql);
     }
    
      //插入数据   
     public function add($data=array()){    
        if(empty( $this->consistent['table'])) {
                die('please set table name!');
            } 
              else 
            {
                $table = $this->consistent['table'];
        }  
        $set = array();
        if(!empty($data)){
            if(is_array($data)){
             foreach($data as $k=>$val){                 
                 if(preg_match('/^\d+$/',$k)) 
                   $set[]= empty($val) ? '' : $val;
                 else 
                   $set[]=   '`'.$k."`='".$this->getValue($val)."'";            
              }  
               $set =  implode(', ', $set);
            }
            else if(is_string($data)){
               $set = $data;    
            }            
        }else{            
            return false;
        }
        $unique = '';
        if(!empty( $this->consistent['unique'])){            
            $unique = $this->consistent['unique'];
        } 
        $res = $this->executeSQL("insert $unique into $table set $set");         
        if( $res) return $this->pdo->lastInsertId();
        return false;        
    } 
    
    
      //批量插入数据 ,$data必须是二维数组  
     public function addd($data=array()){    
        if(empty( $this->consistent['table'])) {
                die('please set table name!');
            } 
              else 
            {
                $table = $this->consistent['table'];
        }  
        $fields = array();
        $set = array();
                
        if(is_array($data)){
            foreach($data as $k=>$val){
               if(is_array($val)) {   
                  $fields = array();
                  $set2 = array();                  
                  foreach($val as $kk=>$vv){
                    $fields[]='`'.$kk.'`';              
                    $set2[]= "'".$this->getValue($vv)."'";                      
                  }            
                  $fields = "(".implode(',',$fields).")";                  
                  $set[] = "(".implode(",",$set2).")";
               }       
           }
        
           if(empty($set)) return false;
           $set = implode(",", $set);  
           $unique = '';
           if(!empty( $this->consistent['unique'])){            
                $unique = $this->consistent['unique'];
           }           
           $res = $this->executeSQL("insert $unique into $table $fields values $set");
           return $res->rowCount();
                      
        }         
        return false;        
    } 
     //更新数据   
     public function save($data=array()){    
        if(empty( $this->consistent['table'])) {
                die('please set table name!');
            } 
              else 
            {
                $table = $this->consistent['table'];
        }  
        $where = (empty($this->consistent['where'])) ? '' : $this->consistent['where']; 
        $set = array();    
        
         if(!empty($data)){
            if(is_array($data)){
             foreach($data as $k=>$val){
                 if(preg_match('/^\d+$/',$k)) 
                   $set[]= empty($val) ? '' : $val;
                 else 
                   $set[]=   '`'.$k."`='".$this->getValue($val) ."'";            
              }  
               $set =  implode(', ', $set);
            }
            else if(is_string($data)){
               $set = $data;    
            }            
        }else{            
            return false;
        }
        $unique = '';
        if(!empty( $this->consistent['unique'])){            
            $unique = $this->consistent['unique'];
        }      
        $res = $this->executeSQL("update $unique $table set $set where 1=1 $where");         
        return $res->rowCount();
            
    }   
//更新数据   
     public function setInc($data=array(),$unset=true){    
        if(empty( $this->consistent['table'])) {
                die('please set table name!');
            } 
              else 
            {
                $table = $this->consistent['table'];
        }  
        $where = (empty($this->consistent['where'])) ? '' : $this->consistent['where'];   
        $set = [];        
        foreach($data as $k=>$val){  
           if($val){               
               $set[] = " `{$k}` = `{$k}` + '".$this->getValue($val) ."' ";  
           }          
        }  
        if(empty($set)) return false;
        $set = implode(',',$set) ;           
        $res = $this->executeSQL("update $table set $set where 1=1 $where");         
        return $res->rowCount();
            
    }   
//更新数据   
     public function setDec($data=array()){    
        if(empty( $this->consistent['table'])) {
                die('please set table name!');
            } 
              else 
            {
                $table = $this->consistent['table'];
        }  
        $where = (empty($this->consistent['where'])) ? '' : $this->consistent['where'];  
        
        $set = [];        
        foreach($data as $k=>$val){  
           if($val){         
               $set[] = " `{$k}` = `{$k}` - '".$this->getValue($val) ."' ";  
           }          
        }  
        if(empty($set)) return false;
        $set = implode(',',$set) ;           
        $res = $this->executeSQL("update $table set $set where 1=1 $where");
        return $res->rowCount();
            
    }     
    //删除数据   
     public function delete(){    
        if(empty( $this->consistent['table'])) {
                die('please set table name!');
            } 
              else 
            {
                $table = $this->consistent['table'];
        }  
        $where = (empty($this->consistent['where'])) ? '' : $this->consistent['where'];  
        $res = $this->executeSQL("delete from $table where 1=1 $where"); 
        return $res->rowCount();
    } 
    //清除
    public function clear(){
        $this->consistent = array();    
    }
   //是否清除
   public function isunset($isunset=true){
       $this->_unset = $isunset;
       return $this;
   }
      /****连贯操作结束****/
     //私有方法 取redis      
    protected function redisread($sql){          
            if(null!==$this->redis_key){
                    $this->redis_key = empty($this->redis_key) ? md5($sql) : $this->redis_key;
                    $out = $this->redis->get($this->redis_key);            
                    if($out) {$this->redis_key = $this->redis_expire = null;  return unserialize($out); } 
            };                 
        return null;   
    }
     //私有方法 存redis   
    protected function redissave($sql,$data){          
            if(null!==$this->redis_key){
                    $this->redis_key = empty($this->redis_key) ? md5($sql) : $this->redis_key;                    
                    if($data){
                        if($this->redis_expire== 0)
                            $this->redis->set($this->redis_key,serialize($data)); 
                        else 
                            $this->redis->setex($this->redis_key,$this->redis_expire,serialize($data)); 
                    }
            };  
        $this->redis_key = $this->redis_expire = null;            
        return $data;   
    }
    //在预定义字符前加上转义字符串
    protected  function getValue($value){
          //$value = addcslashes($value,"'");
          $value = addslashes($value);
          return $value;         
    }
    
    
    
}