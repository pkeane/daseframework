<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','../lib');
define('BASE_PATH',dirname(__FILE__).'/..');
include('../inc/bootstrap.php');
$db = new Dase_DB($config);

