<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport">
<meta content="yes" name="apple-mobile-web-app-capable">
<meta content="black" name="apple-mobile-web-app-status-bar-style">
<meta content="telephone=no" name="format-detection">
<meta content="email=no" name="format-detection">
<link rel="stylesheet" href="https://act.weixin.qq.com/static/cdn/css/wepayui/0.1.1/wepayui.min.css">
<title><?php echo $errno;?> <?php echo $text;?></title>
<style>
<!--
body {background-color: #FFF;}
.info-area {
    position: relative; 
    padding: 0 18px;
}
.info-area .totle {
    position: relative;
    padding: 25px 0 21px;
    text-align: center;
}
.info-area .totle .totle-title {
    font-size: 15px;
    color: #000;
}
.info-area .totle .totle-num {
    font-size: 50px;
    padding-top: 7px;
    text-align: center;
}
.hide{display:none}
-->
</style>
</head>
<body ontouchstart >
<div   class="info-area">
    <dl class="totle">
        <dt class="totle-title">
            <img src="https://act.weixin.qq.com/static/cdn/img/wepayui/0.1.1/icon_warn_red_186x186.png" alt="" width="93" height="93">				
        </dt>
        <dd class="totle-num">
            <strong><?php echo $errno;?> </strong>	
        </dd>
        <dd style="color:#888888">
           <?php echo $text;?>	
        </dd>
        <dd class="hide">         
          The resource requested could not be found on this server!
          <br />
          当前页面可能发生了一个错误，请等待管理员修复.			
        </dd>
    </dl>	
</div>
</body>
</html>