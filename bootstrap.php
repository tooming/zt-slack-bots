<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/AutoLoader.php';

$loader = new \ZtSlack\Psr4AutoloaderClass;
$loader->register();

$loader->addNamespace('ZtSlack', __DIR__.'/lib');
$loader->addNamespace('ZtSlack', __DIR__.'/tests');

require_once __DIR__.'/config/main.conf.php';

$db = new PDO(
    'mysql:host='.DBHOST.';dbname='.DBNAME.';charset=utf8mb4',
    DBUSER,
    DBPASS,
    array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => true
    )
);
