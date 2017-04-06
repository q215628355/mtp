$(function(){
     function getMeta(name){         
         var meta = $("meta[name="+name+"]");
         return meta.attr('content');
     }    
     wx.config({
        debug: false, 
        appId: getMeta('wx-appid'), 
        timestamp: getMeta('wx-timestamp'), 
        nonceStr:  getMeta('wx-noncestr'), 
        signature: getMeta('wx-signature'),
        jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone']
	});  
    var shareConfig = {
            title: getMeta('seotitle'), // 分享标题
            desc: getMeta('description'), // 分享描述
            link: getMeta('url'),
            imgUrl: getMeta('imageurl'), // 分享图标
            type: 'link', // 分享类型,music、video或link，不填默认为link
            dataUrl: '',
            success: function () { 
               // 用户确认分享后执行的回调函数
              // alert('ok');
            },
            fail: function () { 
                // 用户取消分享后执行的回调函数
               // alert('no');
            },
            complete: function(){
               // alert('complete');
            },
            cancel: function(){
               // alert('cancel');
            }
    } 
    wx.ready(function(){  
        wx.onMenuShareAppMessage(shareConfig);            
        wx.onMenuShareTimeline(shareConfig); 
        wx.onMenuShareQQ(shareConfig);  
        wx.onMenuShareQZone(shareConfig);
        wx.onMenuShareWeibo(shareConfig);
    })
   $(".share-open").click(function(){       
       $(".share-window").show()       
   })
   $(".share-close").click(function(){       
       $(".share-window").hide()       
   })
})

