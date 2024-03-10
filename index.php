<?php
/*******************************************

 ***********************************************************/

define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_ENABLE_EXCEPTION_HANDLER', false);
ini_set("display_errors",false);
error_reporting(E_ALL & ~E_NOTICE);
/*
define('YII_ENABLE_ERROR_HANDLER', true);
define('YII_ENABLE_EXCEPTION_HANDLER', true);
ini_set("display_errors",true);
error_reporting(E_ALL & ~E_NOTICE); */

header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');


// include Yii bootstrap file
require_once(dirname(__FILE__).'/yiiframework/yii.php');
$config=dirname(__FILE__).'/protected/config/main.php';

// create a Web application instance and run
Yii::createWebApplication($config)->run();