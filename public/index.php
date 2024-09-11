<?php

require dirname(__DIR__).'/vendor/autoload.php';

if (is_readable(dirname(__DIR__).'/.env')) {
    $_ENV = $_ENV + parse_ini_file(dirname(__DIR__).'/.env');
}

$app = new SlimPlatform\App();
$app->addMiddleware(new Middlewares\ResponseTime());
$app->run();
