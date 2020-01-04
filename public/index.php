<?php

require dirname(__DIR__).'/vendor/autoload.php';

if (is_readable(dirname(__DIR__).'/.env')) {
    $_ENV = $_ENV + parse_ini_file(dirname(__DIR__).'/.env');
}

$config = include dirname(__DIR__).'/config/config.php';
$config['parameters'] += $_ENV;

$app = new SlimPlatform\App($config);
$app->run();
