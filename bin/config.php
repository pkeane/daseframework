<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','../lib');
define('BASE_PATH',dirname(__FILE__).'/..');
include('../inc/autoload.php');

$config = new Dase_Config(BASE_PATH);
$config->load('inc/config.php');
$config->load('inc/local_config.php');
$db = new Dase_DB($config);

