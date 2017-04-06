<pre>
当前GROUP : <?php echo App::$group;?> 
当前ACTION: <?php echo App::$appname;?>Controller.class.php
当前FUNCTION : <?php echo App::$action;?>


详细报告：

<?php foreach(App::$error as $k=> $val):?>

<?php echo $k+1;?>;<?php echo  $val;?>

<?php endforeach;?>


该报告由管理员生成

如果你不想看到此报告页。请到入口文件中将APP_DEBUG 设置为false
如果你不是开发人员，请不要理会

</pre>