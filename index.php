<?php

//apc_clear_cache();

//PHP ERROR REPORTING -- turn off for production
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

define('BASE_PATH',dirname(__FILE__));

include 'inc/autoload.php';

$config = new Dase_Config(BASE_PATH);
$config->load('inc/config.php');
$config->load('inc/local_config.php');

define('LOG_FILE',$config->getLogDir().'/dase.log');
define('LOG_LEVEL',$config->getLogLevel());
define('CONVERT',$config->getAppSettings('convert'));
define('MEDIA_DIR',$config->getMediaDir());
define('TABLE_PREFIX',$config->getDb('table_prefix'));
define('CACHE_TYPE',$config->getCacheType());
define('TEMPLATE_CACHE_DIR',$config->getCacheDir());
define('TEMPLATE_AUTO_RELOAD',$config->getAppSettings('template_auto_reload'));
define('MAIN_TITLE',$config->getAppSettings('main_title'));
define('START_TIME',Dase_Util::getTime());

$db = new Dase_DB($config);
$r = new Dase_Request();
$template = new Dase_Template($r);

$r->init($db,$config,$template);
$r->getHandlerObject()->dispatch($r);
