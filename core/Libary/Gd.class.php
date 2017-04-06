<?php
/*
    图片处理类：缩略，裁剪，圆角，倾斜
*/



class Gd
{
   //将LOGO和二维码重叠
   
    public static function getQrcodeLogo($file,$logo){
          $f = file_get_contents($file);
          try {
               $logoFile = file_get_contents($logo);
           } catch (Exception $e){         
               $logoFile = false;
          }
          if($logoFile===false){              
              return imagecreatefromstring($f);
          }   
          $file = imagecreatefromstring($f); 
          $logo = imagecreatefromstring($logoFile);  
          $QR_width = imagesx($file); 
          $QR_height = imagesy($file); 
          $logo_width = imagesx($logo); 
          $logo_height = imagesy($logo); 
          $logo_qr_width = $QR_width / 6; 
          $scale = $logo_width / $logo_qr_width; 
          $logo_qr_height = $logo_height / $scale; 
          $from_width = ($QR_width - $logo_qr_width) / 2; 
          
          //创建一个图像存储LOGO
          $bwidth  = $logo_qr_width + 10;
          $bheight  = $logo_qr_height + 10;
          $im = imagecreatetruecolor($bwidth, $bheight );
          $color = imagecolorallocate($im,219,219, 219);
          imagefilledrectangle($im,0,$bwidth,$bheight,0,$color);                  
          imagecopyresampled($im, $logo, 5,5, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);  
          imagecopyresampled($file, $im, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height,$bwidth,$bheight);           
          return $file;
   }
   
   public static function jpeg2png($file,$tofile=''){   
        if(empty($tofile)) $tofile = $file;  
        if(!file_exists($file)) return false;
        $file = file_get_contents($file);
        $im = imagecreatefromstring($file);
        if($im !==false){
           $ret = imagepng($im,$tofile);
           imagedestroy($im);
           return $ret;
        }
        return false;
   }
   
    /**
   * desription 判断是否gif动画
   * @param sting $image_file图片路径
   * @return boolean t 是 f 否
   */
   public static  function check_gifcartoon($image_file){
    $fp = fopen($image_file,'rb');
    $image_head = fread($fp,1024);
    fclose($fp);
    return preg_match("/".chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0'."/",$image_head)?false:true;
  }
 
  /**
  * desription 压缩图片
  * @param sting $imgsrc 图片路径
  * @param string $imgdst 压缩后保存路径
  */
  public static function compressed_image($imgsrc,$imgdst,$max_width,$max_height){
    list($width,$height,$type)=getimagesize($imgsrc);	
	$new_width  = $width;
	$new_height = $height; 
    if ($width >$height) {
            if ($width > $max_width) {
                $new_height =round($new_height *= $max_width / $width);
                $new_width = $max_width;
            }
    } else {
            if ($height > $max_height) {
                $new_width = round($new_width *= $max_height / $height);
                $new_height =$max_height;
            }
    }
	$image_wp=imagecreatetruecolor($new_width, $new_height);
    switch($type){
      case 1:
        $giftype=self::check_gifcartoon($imgsrc);
        if($giftype){
         // header('Content-Type:image/gif');     
          $image = imagecreatefromgif($imgsrc);
          imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);       
          imagegif($image_wp, $imgdst);
        }
        break;
      case 2:
       // header('Content-Type:image/jpeg');       
        $image = imagecreatefromjpeg($imgsrc);
        imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagejpeg($image_wp, $imgdst,75); //75代表的是质量、压缩图片容量大小
        break;
      case 3:
        //header('Content-Type:image/png');       
        $image = imagecreatefrompng($imgsrc);
        imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);       
        imagepng($image_wp, $imgdst);        
        break;
    }
	imagedestroy($image_wp);
   }
}