<?php
/** 
 * 控制器基类
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: Action.class.php 2016年3月17日 13:01:41Z $  
 */  
abstract class Action {
    //子类不可用      
    private $thme_name = null;
	private $style_patch = null;
	private $styles = null;
	private $consoleLog = '';
    
    //子类可用 
    protected $nocache = false; 
	protected $data = array(); 
    public function __construct($theme_name = ''){
    	 $this->data = array();//解决内存溢出问题
         $this->theme_name = $theme_name ? : C('DEFAULT_THEME');	
         if(!in_array($this->theme_name,C('THEME_LIST'))) $this->theme_name =  C('DEFAULT_THEME');
         if(!defined('THIS_THEME'))define('THIS_THEME',$this->theme_name);
         $this->style_patch  = C('STYLE_PATCH') ? : 'statics';
		 if(C('GROUP_LIST')){
			$this->styles = implode('/',array(ROOT,$this->style_patch,App::$group,$this->theme_name));   
		 }else{
			$this->styles = implode('/',array(ROOT,$this->style_patch,$this->theme_name));  
		 }
         if(true === $this->nocache){
            noCchche(); 
         }   
         if(C('TPL_CACHE') == true){
             $this->_loadcache();
         }
    }
    
    /***********以下是共有方法，子类可用***************/
    /**
    *  渲染模板输出
    *  @param  string $tpl 模板名称 theme:Appname:Action
    *  @return void
    */
    protected function display($tpl=''){

       $file = $this->_SetTpl($tpl);

       if(!defined('STATICS_ROOT'))define('STATICS_ROOT',C('DOMAIN').$this->styles);     
       if(!file_exists( $file)){
            print  "<script> console.log(" .json_encode(array('tpl'=>$file)) . ')</script>'; 
            die('not a valid  Tpl file!');    
       } 
       extract($this->data,EXTR_SKIP);    
       ob_start(); 
       //是否开启模板引擎
       if(C('TEMPLATE_COMPILE_OPEN')) {       
           $TEMPLATE = new template($file);
           $TEMPLATE->thme_name = &$this->thme_name;
           $file = $TEMPLATE->compile(); 
       }    
   
       include  $file;   
       if(C('TPL_CACHE') == true){ 
            $this->_addcache();
       }
       
       if(!empty($this->consoleLog))print  "<script> console.log(" .json_encode($this->consoleLog) . ')</script>'; 
    }  
    
   /**
    *  向模板中写入数据
    *  @param  string $key   
    *  @param  string $value 
    *  @return void
    */
   protected function assign($key,$value){

       $this->data[$key]= $value;        
    }
	
	/**
    *  获取写入的数据
    *  @param  string $key   
    *  @param  string $value 
    *  @return void
    */
   protected function getValue($key){

       return isset( $this->data[$key]) ?  $this->data[$key] : null;       
    }
    
     /*
    *  子类引用加载跳转模板     
    *  @param  string $url
    *  @param  int    $timeout   
    *  @param  string $msg     
    *  @param  string $tpl 
    *  @return void
    */   

    protected function showSuccess($url='',$msg='SUCCESS',$timeout=3,$status='success'){
        if(empty($url)) $url = '/';
        $js  = '';        
        if(is_int($url)){
            $js =  'history.go('.$url.')';            
            $url = 'javascript:history.go('.$url.')';               
        }
        $this->assign('msg',$msg);
        $this->assign('url',$url);
        $this->assign('js' ,$js);         
        $this->assign('timeout',$timeout); 
        $this->assign('status',$status);
        $this->display(C('ERROR_TPL_NAME'));
        die;
    }
    
    /*
    *  子类引用加载错误模板     
    *  @param  string $url
    *  @param  int    $timeout   
    *  @param  string $msg     
    *  @param  string $tpl 
    *  @return void
    */   

    protected function showError($url='',$msg='ERROR',$timeout=3){
        $this->showSuccess($url,$msg,$timeout,'error');
    }  
	
	/**
    *  向模板中写入调试信息
    *  @param  string $key   
    *  @param  string $value 
    *  @return void
    */
    protected function consoleLog($key,$value){  
 	
       $this->consoleLog[$key]  = $value; 
         
    }
    /*************以下是私有方法 子类不可用******************/
    /**
    *  设置模板
    *  @param  string $tpl 模板名称 theme:Appname:Action
    *  @return string
    */
    private function _SetTpl($tpl=''){
       return  template::viewPatch($tpl);       
    }
    /*
    *  载入缓存   
    */
    private function _loadcache(){  
      $cacheFile = C('TPL_CACHE_PATCH').'/' .md5(PHP_SELF).'.php';
      if(file_exists( $cacheFile)){        
           $t= filemtime($cacheFile);           
           $t2 = C('TPL_CACHE_TIME');
           if(($t + $t2) > time()) {   
               include $cacheFile;  
               die;
           }else{
               unlink($cacheFile);            
           }
      }  
      return;      
    }
    /*
    *  写入缓存   
    */
    private function _addcache(){  
      $cacheFile = C('TPL_CACHE_PATCH').'/' .md5(PHP_SELF).'.php';
      if(file_exists( $cacheFile)){       
         unlink($cacheFile);        
      }  
      $info = ob_get_contents();
      return file_put_contents($cacheFile,"<php if(!defined('ROOT_PATH')) die;?>\r\n".$info);      
    }
 
    
}