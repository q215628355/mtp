/*! fileUpload v1.0 | (c) 2016 huliangming<215628355@qq.com>*/
/*
*    //DEMO 必须引入JQuery
*    //  <input type="file" class="input" capture="camera" accept="image/*" />
*    getInputBase64(".input",400,400,function(image_base64){
*          //$(this) 就是当前 .input 对象  
*           alert(image_base64);   *                
*    });
*/


/*
*  fileUpload H5前端上传压缩插件。
*  图片上传先经过压缩，会返回压缩好的图片base64 格式为jpeg
*  2016年5月7日 09:11:32
* 
* @param inputId id|class 上传的input file的 ID或这CLASS ID前带# class前带.
* @param max_width int      最大宽度
* @param max_height int     最大高度
* @param callback function(result){}     回调函数 result 是处理后的base64 this为当前操作input对象
* @return function
*/


function getInputBase64(inputId,max_width,max_height,callback,type){  
    var tofiletype = type||'jpeg';  

    $(document).on('change',inputId,function(){
        var obj = $(this);
		$.each( this.files,function(i,file){
            //判断类型是不是图片  
            if(!/image\/\w+/.test(file.type)){     
                    alert("请确保文件为图像类型");   
                    return false;   
            }   
            max_width =  $(obj).data('width') ?  $(obj).data('width') : max_width; 
            max_height =  $(obj).data('height') ?  $(obj).data('height') : max_height; 
            var reader = new FileReader();   
            reader.readAsDataURL(file);  
            reader.onload = function(e){
               //获取图片base64
               image_base64=this.result;   
               //压缩图片
               var image = new Image();
               image.src = image_base64;
               image.onload =  function(){       
                  //压缩好的              
                  image_base64 = _imageresizeMe(image, max_width,max_height,tofiletype);               
                  callback.call(obj,image_base64);
               }          
            }
		})
    });    
}
/*
*  获取Buffer 用于socket上传
*  2016年5月7日 09:11:32
* 
* @param inputId id|class 上传的input file的 ID或这CLASS ID前带# class前带.
* @param callback function(result){}     回调函数 result 是处理后的base64 this为当前操作input对象
* @return function
*/

function getInputBuffer(inputId,callback){    
     $(document).on('change',inputId,function(){
            var obj = $(this);
            var file = this.files[0];          
            var reader = new FileReader();   
            reader.readAsArrayBuffer(file);  
            reader.onload = function(e){
               //获取图片Buffer
               var  Buffer=this.result;   
               callback.call(obj,Buffer);
                     
            }
     });    
}

//压缩图片
function _imageresizeMe(img, max_width, max_height,tofiletype) {
        var canvas = document.createElement('canvas');
        var width = img.width;
        var height = img.height;
        if (width > height) {
            if (width > max_width) {
                height = Math.round(height *= max_width / width);
                width = max_width;
            }
        } else {
            if (height > max_height) {
                width = Math.round(width *= max_height / height);
                height = max_height;
            }
        }
        canvas.width = width;
        canvas.height = height;
        var ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, width, height);
        if(tofiletype==undefined) tofiletype ='jpeg';
        if(tofiletype=='jpeg'){
             return canvas.toDataURL("image/jpeg",0.7);            
        }else{
            return canvas.toDataURL("image/"+tofiletype);
        }
}