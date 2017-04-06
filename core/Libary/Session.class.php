<?php
/** 
 * SESSION
 * @copyright  Copyright (c) 2015-2016  
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: Session.class.php 2016年3月17日 13:01:41Z $  
 */ 

class Session
{ 
    private  $session_table  = '';

    private  $max_life_time  = 86400;

    private  $session_name   = '';
    private  $session_id     = '';

    private  $_ip   = '';

    public function __construct($session_table, $session_name = 'ECS_ID', $session_id = '')
    {   
        $GLOBALS['_SESSION'] = array();   
        $this->session_name  = $session_name;
        $this->_ip           = get_client_ip();    
        $this->gen_session_id($session_id);           
        $this->load_session();       
    }
    function gen_session_id($session_id='')
    {
        $this->session_id =  $session_id ? $session_id :  cookie($this->session_name);
        if(!$this->session_id || !preg_match('/^[a-z0-9]{32}$/',$this->session_id ))
        {
           $this->session_id  =   md5(uniqid(mt_rand(), true).$this->_ip);
        } 
        cookie($this->session_name, $this->session_id ,array('expire'=>$this->max_life_time));//自动续约        
        return $this->session_id;                
    } 
    public function insert_session()
    {
        
        return RD()->aset('session_'.$this->session_id,[],$this->max_life_time);
    }

    public function load_session()
    {
        $session = $GLOBALS['_SESSION']  =  RD()->aget('session_'.$this->session_id);
        if (empty($session))
        {
            $GLOBALS['_SESSION'] = [];
            $this->insert_session();  
        }
    }
    public function update_session()
    {      
        $data = empty($GLOBALS['_SESSION']) ? [] : $GLOBALS['_SESSION'];                 
        return  RD()->aset('session_'.$this->session_id,$data,$this->max_life_time);
    }
 
    
    public function destroy_session()
    {  
        $GLOBALS['_SESSION'] = array();
        cookie($this->session_name, null);         
        return RD()->remove('session_'.$this->session_id);      
    }

    public function get_session_id()
    {
        return $this->session_id;
    }  
    public function get_users_count()
    {
        $max = RD()->keys('session_*');
        return count($max);
    }
    function __destruct () 
    {
         try{
           $this->update_session();    
         }catch(Exception $e){             
            echo 'session update error!';
         }   
    }    
}

