/** 
 * ajaxStatus.js
 * @copyright  Copyright (c) 2015-2016
 * @license    Copyright Clarify,not be reproduced ways without permission.
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: ajaxStatus.js 2016年3月17日 13:01:41Z $  
 */  
//请求一个POST的AJAX 
// ajaxUrl     请求的URL
// ajaxData     请求的数据
// ajaxDatatype 返回数据的类型
// ajaxSuccess  回调函数
// 用法实例
//  ajaxPost('/ajax','a=1&b=2','json',function(result){     
//        alert(json.status)   
//  });
//
//
//
//
//


var __ROOT__ = '';
var __PUBLIC__ = __ROOT__ + '/Public';

//
function include(js){    
    document.write("<scr"+"ipt src=\"" +js + "\"></sc"+"ript>");    
}
function ajaxPost(ajaxUrl,ajaxData,ajaxDatatype,ajaxSuccess){   
		$.ajax({
		url: ajaxUrl,
		type:'POST',
		data:ajaxData,
        timeout: 30000, //超时时间：30秒        
		dataType:ajaxDatatype,	
		success:ajaxSuccess,
        error:function (XMLHttpRequest, textStatus, errorThrown) {      
             opentext2('请求失败');  
             $(".ajaxLoading").hide();             
        }
   
       });	    
}
//绑定ajaxStart 以及 ajaxStorp事件 
$(function (){    
      var $div = $("<div />");
      $div.addClass('ajaxLoading');
      var css = "height:100%;width:100%;position:fixed;padding:10px;top:0px;left: 0px;line-height:20px;font-size:14px; "
               + "-moz-border-radius: 15px;-webkit-border-radius: 15px; border-radius:15px;  color:#ffffff;z-index:9999999;text-align: center;";               
      $div.attr('style',css);
      $div.append("<img />");
      $div.find("img").attr('src', __PUBLIC__ + '/images/loading.gif') ;
      $div.find("img").attr('style','width:80px;height:80px;');  
      $("body").append($div);
      $img= $div.find("img");      
      if($(parent.window).height()){     
            $img.css('marginTop', ($(parent.window).height() - $img.height())/2);             
       }else{
            $img.css('marginTop', ($(window).height() - $img.height())/2); 
       }
       //ajaxStart, ajaxStop, ajaxSend, ajaxComplete, ajaxError, ajaxSuccess
      $(document).ajaxStart(function(){
           $div.show();        
       });
        $(document).ajaxStop(function(){
           $div.hide();          
       });  
       $(document).ajaxSend(function(){
           $div.show();        
       });    
      $(document).ajaxComplete(function(){
            $div.hide(); 
      });       
     $div.hide();//第一次关闭掉     
     $("noscript,.noscript").hide(); 
    //若想处理错误，请在图片加属性data-error-disable=0 若指定图片 则写 data-error-img=''
	  $("img").error(function(){
          $(this).unbind();
          var _open = $(this).data('error-disable');
          if(_open == '0') {
              var src = $(this).data('error-img') || __PUBLIC__ + "/images/nopic.png";
              $(this).attr('src', src);
          } 
	  });	 
});
//创建一个剧中的弹窗，装载HTML
function opentext(txt,title,_width){   
        var t = title || '点这关闭'; 
        $(".tcBox").remove();  
        var $tcBox = $("<div />"); 
        $tcBox.addClass('tcBox'); 
        var css = "height:100%;width:100%;position:fixed;z-index:99999999;";
        $tcBox.attr('style',css);
       
        var $div = $("<div />");   
        css = "height:auto;width:auto;margin:auto;line-height:20px;top:50%;font-size:14px;background:#ffffff; "
               + "-moz-border-radius: 5px;-webkit-border-radius: 5px; border-radius:5px;  color:#333333;border:1px #333 solid";
               
        $div.attr('style',css);
        var $title = $("<div />");
        $title.css('height','40px').css('lineHeight','20px').css('padding','10px').css('background','#333').css('color','#fff');
        var $close = $("<span />");
        $close.css('float','right');
        $close.html('x');
        $title.html(t).append($close);
        $div.append($title);    
        var $body = $("<div />");
        $body.css('padding','10px');
        $body.append(txt);        
        $div.append($body);  
        $tcBox.append($div);        
        $("body").append($tcBox);      
        //$div.css('marginLeft', - ($div.width()/2));  
        
        if($(parent.window).height()){     
            $tcBox.css('top', ($(parent.window).height() - $div.height())/2);             
        }else{
            $tcBox.css('top', ($(window).height() - $div.height())/2); 
        }
        if(_width!=undefined){
            $div.css('width',_width);  
        }        
        $title.click(function(){            
            $tcBox.remove();            
        })    
}
//弹窗
function opentext2 (txt,type) {
	 
	    $('.alertbox').remove();   
        var $div = $("<div />");
        $div.addClass('alertbox');
        var css = "height:auto;width:auto;position:fixed;left:50%;padding:10px;line-height:20px;top:50%;font-size:14px;background:#000000; "
               + "-moz-border-radius: 15px;-webkit-border-radius: 15px; border-radius:15px;  color:#ffffff;z-index:9999999";
               
        $div.attr('style',css);
        type = type==undefined ? 'txt' : type;
        if(type=='loading'){
            $div.append("<img />");
            $div.find("img").attr('src',__PUBLIC__ + '/images/loading.gif') ; 
            $div.find("img").attr('style','width:80px');             
         }else{
            $div.html(txt);
        } 
        $("body").append($div);
        $div.css('marginLeft', - ($div.width()/2));  
        if($(parent.window).height()){     
            $div.css('top', ($(parent.window).height() - $div.height())/2);             
        }else{
            $div.css('top', ($(window).height() - $div.height())/2); 
        }
        setTimeout(function(){
             $div.hide();  
             $div.remove();              
        },2000);
}
//myAlert是别名
function myAlert(txt){    
    opentext2 (txt);
}
//模拟window.open
var _windowOpen_scrollTop = [];
function windowOpen(url){    
    $window = $("<iframe />");    
    var id = Date.parse(new Date())/1000;
    id = 'id'+id;    
    $window.attr('src',url).attr('id',id).attr('frameborder',0).css("z-index",99999999999).css('position','absolute').css('left',0).css('top',0).css('background','#ffffff');
    //始终填满
    $window.width($(document).width());    
    $window.height($(document).height());    
    $("body").append($window);    

    $window.load(function(){
        //var myBody = $(this).contents().find("body");
        _windowOpen_scrollTop[id] = $(document).scrollTop();
        $("html,body").scrollTop(0);
    });   
    return id;        
}
function windowClose(id){    
    $("#"+id).remove();   
    $("html,body").scrollTop(_windowOpen_scrollTop[id]);    
    _windowOpen_scrollTop[id]= null;
}
//模拟 Confirm
function windowConfirm(title, txt, callback,errcallback,obj) {
        var t = title || '点这关闭';
        var success =  '确定';
        var _error  = '取消';    
        if($.type(obj)=='object'){
            success = obj.hasOwnProperty('success') ?  obj.success : success;
            _error = obj.hasOwnProperty('error') ?  obj.error : _error;            
        } 
        $(".tcBox").remove();  
        $(".bakdiv").remove();
        var $bakdiv = $("<div class='bakdiv' style='height:100%;width:100%;position:fixed;z-index:99999998;top:0;left:0;background:#333;opacity:0.5;'></div>");
        var $tcBox = $("<div />");
        var success = obj != undefined ? obj.success: '确定';
        var _error = obj != undefined ? obj.error: '取消';
        $tcBox.addClass('tcBox'); 

        var css = "height:100%;width:100%;position:fixed;z-index:99999999;";
        $tcBox.attr('style',css);

        
        var $div = $("<div />"); 
       
        css = "height:auto;width:80%;max-width:720px;margin: auto;line-height:30px;top:50%;font-size:14px;background:#ffffff;"
               + "-moz-border-radius:3px;-webkit-border-radius:3px; border-radius:3px;  color:#646464;border:1px #ff8a9a solid;overflow:hidden";
        $div.attr('style',css);
        var $title = $("<div />");
        $title.css('height','40px').css('lineHeight','20px').css('padding','10px').css('background','#ff8a9a').css('color','#fff').css('font-size','20px');    
        $title.html(t);        
        $div.append($title);  
        var $body = $("<div class='boDy'/>");
        $body.css('padding','10px').css('font-size','20px');  
        $body.append(txt); 
        $div.append($body);         
        var $foot = $("<div />");
        $foot.attr('style',"margin-top:10px;text-align:center;font-size:20px;height:40px;border-top:1px #ff8a9a dotted");
        var $succ = $("<a />");
        $succ.html(success);
        var $err = $("<a />");
        $err.html(_error);
        $succ.attr('style',"padding:5px 10px; font-size:20px;display:inline-block;width:50%;color:#ff8a9a");
        $err.attr('style',"padding:5px 10px;margin-left:0px; border-left:1px #ff8a9a dotted ;font-size:20px;display:inline-block;width:50%;");
        $foot.append($succ).append($err);
        if(callback == undefined){
            $err.hide(); 
            $succ.css('width','100%');
        }
        if(errcallback === null){
            $err.hide(); 
            $succ.css('width','100%');
        }
        $div.append($foot);
        $tcBox.append($div);        
        $("body").append($bakdiv).append($tcBox);      
        //$div.css('marginLeft', - ($div.width()/2));          
        if($(parent.window).height()){     
            $tcBox.css('top', ($(parent.window).height() - $div.height())/2);             
        }else{
            $tcBox.css('top', ($(window).height() - $div.height())/2); 
        }             
        $succ.click(function(){            
            $tcBox.remove();   
            $bakdiv.remove();             
            //alert($.type(callback));
            if($.type(callback)=='function')callback();            
        })  
        $err.click(function(){            
            $tcBox.remove();   
            $bakdiv.remove();              
            if($.type(errcallback)=='function') errcallback();            
        })         
}
//修改历史记录中的值，使用户后退到指定页面

function setBakUrl(url){    
  
     history.pushState({}, "", url);
}
//使用AJAX方式提交表单,callback 成功后的回调方法
function submitForm(formObj,ajaxData,ajaxDatatype,ajaxSuccess){ 
    $(formObj).submit(function(e){
        e.preventDefault();
        var ajaxUrl = $(this).attr('action');  
        var newData = ajaxData;
        var obj = this;
        if(ajaxData == 'serialize') newData = $(this).serialize();
    	$.ajax({
            url: ajaxUrl,
            type:'POST',
            data:newData,
            timeout: 30000, //超时时间：30秒        
            dataType:ajaxDatatype,	
            success:function(result){
                ajaxSuccess.call(obj,result);
            },
            error:function (XMLHttpRequest, textStatus, errorThrown) {      
                 opentext2('请求失败');  
                 $(".ajaxLoading").hide();             
            }   
       });	
       return false;        
    })
}
// 对Date的扩展，将 Date 转化为指定格式的String
// 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符， 
// 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字) 
// 例子： 
// (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423 
// (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18 

Date.prototype.Format = function (fmt) { //author: meizz 
    var o = {
        "M+": this.getMonth() + 1, //月份 
        "d+": this.getDate(), //日 
        "h+": this.getHours(), //小时 
        "m+": this.getMinutes(), //分 
        "s+": this.getSeconds(), //秒 
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度 
		"Q": "春夏秋冬".charAt(Math.floor((this.getMonth() + 3) / 3) -1), //季度 
        "S": this.getMilliseconds(), //毫秒 
		'w+':this.getDay(),//星期
		'W': "日一二三四五六".charAt(this.getDay())//星期
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}

//当滑动到页面底部时，对下一页加载并填充到指定区域中
// coentId, 当前页面 列表所在盒子的ID或者CLASS
//pageId 当前页面下一页URL所在a标签的ID。下一页链接必须在href上
//成功时回调函数
//mywindow指定 产生滚动条的DIV


function nextPageLoad(coentId,pageId,callback,mywindow){
    var  islaoding  = 0;    
    var  thiswindow = window; 
    if(mywindow != undefined) {
        thiswindow = mywindow;
    }else if($(parent.window).height()) {
        thiswindow = parent.window;   
    } 
    $(thiswindow).scroll(function () {   
        var scrollTop = $(this).scrollTop();//到顶部的距离
        var scrollHeight = $(this)[0].scrollHeight;
        if(!scrollHeight) scrollHeight = document.documentElement.scrollHeight;        
        var windowHeight = $(this).height() +10;  //可视高度
       // console.log(scrollTop +'+'+windowHeight +' >= '+ scrollHeight);
        if ((scrollTop + windowHeight ) >= scrollHeight) {  
            _init();              
          // console.log(scrollTop+'---'+scrollHeight+'----'+windowHeight);
        }  
    })        
    function _init(){     
   
        if(islaoding==1) return;
        islaoding  = 1;  
        var _url = $(pageId).attr('href');   
        if(!_url || _url == undefined || _url =='') {  
            islaoding = 0;         
            return;
        }         
        $.post(_url,'',function(html){   
                       
                $(".ajaxLoading").hide();
                $dom = $(html);
                $(coentId).append(  $dom.find(coentId).html());                
                var nexthref = $dom.find(pageId).attr('href');                       
                if( nexthref == undefined){
                    $(pageId).attr('href','');
                }else{                            
                    $(pageId).attr('href', nexthref);
                }
                 islaoding = 0; 
                if($.type(callback)=='function')callback($dom); 
            },'html')  
    }    
}
function redirect(url,time){  
    if(time==undefined || !time)time = 10;    
    setTimeout(function(){
        if(url==undefined || !url){
           window.location.reload(); 
           return;
        }
        if( /^(-)?\d+$/.test(url)){  
            history.go(url);
            return;
        }
        window.location.href= url;        
    },time)
}
