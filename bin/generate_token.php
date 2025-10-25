<?php

$config = parse_ini_file(dirname(__DIR__).'/.env');
$key = $config['AUTH_ENCRYPTION_KEY'];

$payload = uniqid();
$signature = sha1($payload.$key);

echo $payload.$signature.PHP_EOL;
