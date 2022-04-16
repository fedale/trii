<?php
error_reporting(0);
#ini_set('display_errors',false);
ini_set('display_errors',true);
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../../../yii2/vendor/autoload.php');
require(__DIR__ . '/../../../yii2/vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../../yii2/common/config/bootstrap.php');
require(__DIR__ . '/../../../yii2/common/config/globals.php');
require(__DIR__ . '/../config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../yii2/common/config/main.php'),
    require(__DIR__ . '/../../../yii2/common/config/main-local.php'),
    require(__DIR__ . '/../config/main.php'),
    require(__DIR__ . '/../config/main-local.php')
);
//var_dump($config);
$application = new yii\web\Application($config);
$application->run();

