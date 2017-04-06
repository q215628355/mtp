<?php
/** 
 * 前台页面
 * @copyright  Copyright (c) 2015-2016
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: IndexController.class.php 2016年12月30日 10:13:29Z $  
 */  

class IndexController extends  Action{
    

    function index(){     
    	//跳到后台
        $content = TestMdl::hello();
        $this->assign('content',$content);
        $this->display();
    }  
   
}