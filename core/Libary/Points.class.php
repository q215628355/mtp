<?php
/** 
 * 坐标计算类
 * @copyright  Copyright (c) 2015-2016 河南猫果科技有限公司(http://www.55mao.com) 
 * @license    版权限制,未经许可不得转载和发布
 * @author     Huliangming<215628355@qq.com>
 * @version    $Id: Points.class.php 2017年1月4日 13:01:41Z $  
 */  
class Points{    
    //从百度地址获取当前IP的经纬度
    public function getLocation($ip=null){
        if(null===$ip) $ip = get_client_ip();        
        $data = file_get_contents("http://api.map.baidu.com/location/ip?ak=4mZG45nzSOyYrb4UKIBNvFry&coor=bd09ll&ip=".$ip);
        $data = json_decode($data,true);   
        if( $data['status'] == 0){            
            return  $data['content']['point'];
        } else{            
            return false;
        }       
    } 
    /*
    百度地图坐标转换API是一套以HTTP形式提供的坐标转换接口，
    用于将常用的非百度坐标（目前支持GPS设备获取的坐标、google地图坐标、soso地图坐标、amap地图坐标、mapbar地图坐标）
    转换成百度地图中使用的坐标，
    并可将转化后的坐标在百度地图JavaScript API、
    车联网API、静态图API、web服务API等产品中使用。
    注意Android SDK、iOS SDK、定位SDK和导航SDK坐标转换服务需单独申请 。 

    from取值为如下：
    1：GPS设备获取的角度坐标，wgs84坐标;
    2：GPS获取的米制坐标、sogou地图所用坐标;
    3：google地图、soso地图、aliyun地图、mapabc地图和amap地图所用坐标，国测局坐标;
    4：3中列表地图坐标对应的米制坐标;
    5：百度地图采用的经纬度坐标;
    6：百度地图采用的米制坐标;
    7：mapbar地图坐标;
    8：51地图坐标
    
    */
    
    //将非百度地图坐标，转换为百度地图坐标
    public function toBaiduLocation($x,$y,$from=1){        
        $data = file_get_contents("http://api.map.baidu.com/geoconv/v1/?ak=4mZG45nzSOyYrb4UKIBNvFry&coords={$x},{$y}&from={$from}");
        $data = json_decode($data,true);   
        if( $data['status'] == 0){            
            return  $data['result'][0];
        } else{            
            return false;
        }   
    }
    //获取用户所在坐标点。
    //cookiePoint,存在cookie中的坐标点
    //ip,用户IP
    public function getUserLocation($cookiePoint='0,0',$ip=null){        
        $cookiePoint = explode(',',$cookiePoint); 
        $data = [];        
        $data['x'] = isset($cookiePoint[0]) ? $cookiePoint[0] : 0;
        $data['y'] = isset($cookiePoint[1]) ? $cookiePoint[1] : 0;
        if($data['x'] && $data['y']){            
            return $this->toBaiduLocation($data['x'], $data['y'],1);              
        }        
        return $this->getLocation($ip);
    }
    //获取坐标的地址
    public function getPointAddress($x,$y,$keys='country,province,city'){
        $data = file_get_contents("http://api.map.baidu.com/geocoder/v2/?ak=4mZG45nzSOyYrb4UKIBNvFry&location={$y},{$x}&coordtype=bd09ll&output=json");
        $data = json_decode($data,true);  
        if( $data['status'] == 0){    
            $keys = explode(',',$keys);
            $output =[];
            $add = $data['result']['addressComponent'];
            foreach($keys as $key){
                $output[$key]= isset($add[$key]) ? $add[$key] : '';
            }            
            return  $output;
        } else{            
            return false;
        }  
        
    }
   //获取周围坐标
   //$x经度lng 113,$y 纬度 lat 34, $distance 公里数
    public function returnSquarePoint( $x,$y,$distance = 0.5){
        $earthRadius = 6378138;
        $dlng =  2 * asin(sin($distance / (2 * $earthRadius)) / cos(deg2rad($x)));
        $dlng = rad2deg($dlng);
        $dlat = $distance/$earthRadius;
        $dlat = rad2deg($dlat);
        return array(
            'left-top'=>array('x'=>$x + $dlat,'y'=>$y-$dlng),
            'right-top'=>array('x'=>$x + $dlat, 'y'=>$y + $dlng),
            'left-bottom'=>array('x'=>$x - $dlat, 'y'=>$y - $dlng),
            'right-bottom'=>array('x'=>$x - $dlat, 'y'=>$y + $dlng)
        );
    }
   //计算两个坐标的直线距离  y1,x1,y2,x2  
    public function getDistance($lat1, $lng1, $lat2, $lng2){      
        $earthRadius = 6378138; //近似地球半径米
        // 转换为弧度
        $lat1 = ($lat1 * pi()) / 180;
        $lng1 = ($lng1 * pi()) / 180;
        $lat2 = ($lat2 * pi()) / 180;
        $lng2 = ($lng2 * pi()) / 180;
          // 使用半正矢公式  用尺规来计算
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }
}