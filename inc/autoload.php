<?php

ini_set('include_path',BASE_PATH.'/lib');

require_once BASE_PATH.'/lib/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony' => BASE_PATH.'/lib',
));
$loader->registerPrefixes(array(
    'Twig_Extensions_' => BASE_PATH.'/lib',
    'Twig_'            => BASE_PATH.'/lib',
    'Dase_'            => BASE_PATH.'/lib',
));

$loader->useIncludePath(true);
$loader->register();


