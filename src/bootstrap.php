<?php

use JAuth\Site as MyApp;
use Slim\Container;
define('INC_ROOT', dirname(__DIR__));

require_once INC_ROOT . '/vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

$app = new MyApp(new Container(
    include INC_ROOT . 'config.php'
));

$container = $app->getContainer();

require 'routes.php'