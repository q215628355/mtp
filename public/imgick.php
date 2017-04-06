<?php
$info = isset($_GET['s']) ? $_GET['s'] : ''; 
if(!$info) die();

$info2 = preg_replace('/^(.*?)-([\d]+)x([\d]+).(.*?)$/','\\2 \\3',$info);
list($width,$height) = explode(" ",$info2);
$info = preg_replace('/^(.*?)-([\d]+)x([\d]+).(.*?)$/','\\1.\\4',$info);
$info = dirname(__file__).'/'.$info;
$rand = 0;
if( !file_exists($info) ){
    $rand = time();
    $info = dirname(__file__).'/Public/images/nopic.png';     
}
//echo $info.'---w:'.$width.'--h:'.$height; die;
if( !file_exists($info) || ($width > 800) || ($height > 800)){
    header('Content-type: image/png');
    header('HTTP/1.1 404 Not Found');
       // 确保FastCGI模式下正常
    header('Status:404 Not Found');
    //file_put_contents(dirname(__file__).'/mobile/temp/errlog/err.txt',$info,FILE_APPEND);
    die();
}
///304缓存配置


$tmp_time = strtotime(date("Y-m-d H"));
$md5 = md5($info.$tmp_time.$rand);
$etag = '"' . $md5 . '"';
header('Last-Modified: '.gmdate('D, d M Y H:i:s',$tmp_time ).' GMT');
header("ETag: $etag");
if((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $tmp_time) || (isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) < $tmp_time) || (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag)){
   header("HTTP/1.1 304 Not Modified"); 
   exit(0);
}  

$image = new Imagick($info);
$mimeType = $image->getImageMimeType();
header('Content-type: '.$mimeType);
//读取原始宽高
$myWidth  =  $image->getImageWidth(); 
$myHeight = $image->getImageHeight();
if($width > $myWidth)  $width   =  $myWidth;
if($height> $myHeight) $height  =  $myHeight;
$image->thumbnailImage($width,$height);
echo $image;
