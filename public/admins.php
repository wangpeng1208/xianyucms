<?php
@session_start();
//$config = require '../runtime/conf/config.php';
$_SESSION['xianyucms_login']=md5(md5($config['xianyucms_login']).'hanju');
header("Location: ./index.php?s=/admin/login.html");
?>