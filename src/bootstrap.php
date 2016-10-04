<?php
define('INC_ROOT', dirname(__DIR__));

require_once INC_ROOT . '/vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(INC_ROOT);
$dotenv->load();

require 'site.php';
use MyAPI\Site as MyApp;
use Slim\Container;

$app = new MyApp(new Container(
	include INC_ROOT . '/src/config.php'
));

$container = $app->getContainer();

require 'routes.php'
?>