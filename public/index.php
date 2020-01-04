<?php

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;

require dirname(__DIR__).'/vendor/autoload.php';

$app = AppFactory::create();
$app->get('/', function (Request $request, Response $response) {
    return $response;
});
$app->run();
