<?php
 /** 
 * 模板编译器
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: template.class.php 2016年4月10日 13:01:41Z $  
 */  
 class template {  
      
        public   $theme_nam = '';           
        public   $taglib_begin = '<';
        public   $taglib_end = '>';   
        public   $var_begin = '{';
        public   $var_end = '}';

        private   $caceFile = '';
        private   $_tpl;          
        //构造函数        
        public function __construct($_tplFile,$caceFile=''){               
            if(!$this->_tpl = file_get_contents($_tplFile)){  
                exit('ERROR：读取模板出错！');  
            }  
            $cacePatch =C('TEMPLATE_COMPILE_PATCH') ;
            if(!$cacePatch) $cacePatch = BASE_PATH.'/temp/Tplcache';
            if(empty($caceFile)) $caceFile = $cacePatch.'/'.md5($_tplFile).'cache.php';
            $this->caceFile =  $caceFile;    
        
        }
        //解析标签中的属性为数组
        private function parseSvrToArr($svr){  
            $xml        =   '<tpl><tag '.$svr.' /></tpl>';
            $xml        =   simplexml_load_string($xml);
            if( !$xml)  return false;
            $xml        =   (array)($xml->tag->attributes());
            return  array_change_key_case($xml['@attributes']);              
        }        
         // 解析模板中的include标签
        private function parInclude() {           
            $find = preg_match_all('/'.$this->taglib_begin.'include\s(.+?)\s*?\/'.$this->taglib_end.'/is',$this->_tpl,$matches);
            if($find) {
                 for($i=0;$i<$find;$i++) {
                     $include    =   $matches[1][$i];
                     $array = $this->parseSvrToArr($include); 
                     if($array){
                         $tpl = $this->SetTpl($array['file']);
                         if(file_exists( $tpl)){                             
                            $this->_tpl    =   str_replace($matches[0][$i],'<?php include("'.$tpl.'");?>',$this->_tpl);
                         }                           
                     }            
                 }
           }
       }           
        //解析普通变量  
        private function parVar(){  
        
           $find = preg_match_all('/'.$this->var_begin.'\$([\w]+)'.$this->var_end.'/is',$this->_tpl,$matches);
            if($find) {
                 for($i=0;$i<$find;$i++) {  
                    $this->_tpl    =   str_replace($matches[0][$i],'<?php echo $'.$matches[1][$i].';?>',$this->_tpl);   
                 }
           }             
        } 
       //解析函数 
        private function parFunc(){          
           $find = preg_match_all('/'.$this->var_begin.'\:(.+?)'.$this->var_end.'/is',$this->_tpl,$matches);
         
            if($find) {
                 for($i=0;$i<$find;$i++) {  
                    $this->_tpl    =   str_replace($matches[0][$i],'<?php echo '.$matches[1][$i].';?>',$this->_tpl);   
                 }
           }           
        }   
        //php标签
        private function parPhp() {
           $find = preg_match_all('/'.$this->taglib_begin.'(php|php(.+?))'.$this->taglib_end.'(.+?)'.$this->taglib_begin.'\/php'.$this->taglib_end.'/is',$this->_tpl,$matches);          
           if($find) {
                 for($i=0;$i<$find;$i++) {                       
                    
                     $this->_tpl    =   str_replace($matches[0][$i],'<?php '.$matches[3][$i].' ?>',$this->_tpl);   
                 }
           }          
        }        
        //解析IF条件语句  
        private function parIf(){  
            //开头if模式  
            $_patternIf = '/'.$this->taglib_begin.'if\s+(.+?)'.$this->taglib_end.'/is';  
            //结尾if模式  
            $_patternEnd = '/'.$this->taglib_begin.'\/if'.$this->taglib_end.'/';  
            //else模式  
            $_patternElse = '/'.$this->taglib_begin.'else\s(.+?)'.$this->taglib_end.'/is'; 
            
            $_patternElseif = '/'.$this->taglib_begin.'elseif\s(.+?)'.$this->taglib_end.'/is';              
         
            //判断if是否存在  
            if(preg_match($_patternIf, $this->_tpl)){  
                //判断是否有if结尾  
                if(preg_match($_patternEnd, $this->_tpl)){  
                    //替换开头IF  
                    $this->_tpl = preg_replace_callback($_patternIf, 
                    function($matches){   
                        $if    =   $matches[1];   
                        $array = $this->parseSvrToArr($if); 
               
                        $key = isset($array['key']) ? $array['key'] : 'null';
                        $value = isset($array['value']) ? $array['value'] : '';
                        if($conditions)return "<?php if ({$conditions} ):?>";
                        return  "<?php if ({$key} = {$value} ):?>";
                    
                    }, 
                    $this->_tpl);  
                    //替换结尾IF  
                    
                    //判断是否有else  
                    if(preg_match($_patternElse, $this->_tpl)){  
                        //替换else  
                        $this->_tpl = preg_replace_callback($_patternElse, 
                        function($matches){   
                            $if    =   $matches[1];   
                            $array = $this->parseSvrToArr($if); 
                            $key = isset($array['key']) ? $array['key'] : '';
                            $value = isset($array['value']) ? $array['value'] : '';
                            if($conditions)return "<?php else ({$conditions} ):?>";
                            if($key) return  "<?php else ({$key} = {$value} ):?>";
                            return "else:";
                        
                        }, 
                        $this->_tpl);    
                    } 
                   //判断是否有elseif                    
                    if(preg_match($_patternElseif, $this->_tpl)){  
                        //替换elseif  
                        $this->_tpl = preg_replace_callback($_patternElseif, 
                        function($matches){   
                            $if    =   $matches[1];   
                            $array = $this->parseSvrToArr($if); 
                            $key = isset($array['key']) ? $array['key'] : 'null';
                            $value = isset($array['value']) ? $array['value'] : '';
                            if($conditions)return "<?php elseif ({$conditions} ):?>";
                            return  "<?php elseif ({$key} = {$value} ):?>";
                           
                        
                        }, 
                        $this->_tpl);    
                    }  
                    
                    
                    $this->_tpl = preg_replace($_patternEnd, "<?php endif; ?>", $this->_tpl);  
                }
            }  
        }  
        //解析foreach  
        private function parForeach(){   
       
            $find = preg_match_all('/'.$this->taglib_begin.'foreach\s(.+?)'.$this->taglib_end.'(.+?)'.$this->taglib_begin.'\/foreach'.$this->taglib_end.'/is',$this->_tpl,$matches); 
            
             if($find) {
                 for($i=0;$i<$find;$i++) {  
                 
                    $foreach    =   $matches[1][$i];
                    $array = $this->parseSvrToArr($foreach); 
                    if( $array ){
                        $key = isset($array['key']) ? $array['key'] : 'key';
                        $item = isset($array['item ']) ? $array['item '] : 'item';
                        $name = $array['name'];
                        $this->_tpl    =   str_replace($matches[0][$i],'<?php foreach($'.$name.' as $'.$key.'=>$'.$item.'): ?>'.$matches[2][$i].'  <?php endforeach;?>',$this->_tpl); 
                    }
                 }
             }         
        } 
		
		//获取模版路径
		public static function viewPatch($tpl){
		 //模板文件名后缀        
			$HTML_SUFFIX = C('HTML_SUFFIX') ? C('HTML_SUFFIX') : '.html';
			//模板保存目录 
			$TPL_PATCH = VIEW_PATCH;
		   //默认模板目录
			$DEFAULT  = C('DEFAULT_THEME');   
			//当前模板
			if(defined('THIS_THEME')) {
				$THIS_THEME = THIS_THEME;            
			}else{
				$THIS_THEME = C('DEFAULT_THEME');            
			}      
		  
		   //设置DISPLAY 模板分割字符 默认为 : 可在config中重置
		   $DISPLAY_EXCISION   = C('DISPLAY_EXCISION') ?  C('DISPLAY_EXCISION')  : ':';     
		   if(!empty($tpl)){      
			   $tpl =  array_reverse(explode($DISPLAY_EXCISION,$tpl));
			   $group = '';   
			   $action = $tpl[0];           
			   $appname = isset($tpl[1]) ? $tpl[1] : App::$appname;           
			   $theme_name = isset($tpl[2]) ? $tpl[2] : $THIS_THEME;          
			   //如果存在分组
			   if(C('GROUP_LIST')){
				   $list = explode(',',C('GROUP_LIST'));
				   $group = ucfirst($tpl[0]);   
				   if(!in_array($group,$list)){  
					   $group = empty(App::$group) ? (C('DEFAULT_GROUP') ? C('DEFAULT_GROUP') :  $list[0]) : App::$group; 
				   }else{
					   $action = isset($tpl[1]) ? $tpl[1] : App::$action;            
					   $appname = isset($tpl[2]) ? $tpl[2] : App::$appname;           
					   $theme_name = isset($tpl[3]) ? $tpl[3] : $THIS_THEME; 
				   }  
			   } 
			   
		   }else{
			   $group =  App::$group; 
			   $action =  App::$action; 
			   $appname =  App::$appname;           
			   $theme_name = $THIS_THEME;
		   }
		   $data = array(
			   'tpl_patch'=>$TPL_PATCH, 
			   'theme_name'=>$theme_name,
			   'appname'=>ucfirst($appname),
			   'action'=>ucfirst($action)
		   );  
		   $file = implode('/',$data).$HTML_SUFFIX;  
		   if(file_exists($file)) return  $file ;			 
			//若设置模板没有找到，将尝试默认模板目录下的模板	
		   $defaultfile = implode('/',array_merge($data,array('theme_name'=>$DEFAULT))).$HTML_SUFFIX;
		   //echo   $defaultfile ; die;
		   return  $defaultfile; 
		}
        //设置模板
        private function SetTpl($tpl){                  
           return  self::viewPatch($tpl);        
        }        
        //生成编译文件  
        public function compile(){              
            //解析模板变量  
            $this->parVar(); 
            $this->parFunc();  
            $this->parPhp();             
            //解析IF  
            $this->parIf();  
         
            //解析Foreach  
            $this->parForeach();  
            //解析include  
            $this->parInclude();           
            //生成编译文件  
            
            if(!file_put_contents( $this->caceFile, $this->_tpl)){  
                exit('ERROR：编译文件生成失败！');  
            }  
            return $this->caceFile;
        }  
    }  
