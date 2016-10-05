<?php
/* this bootstrap our API App, like of like App_Start */

define('INC_ROOT', dirname(__DIR__));

require_once INC_ROOT . '/vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(INC_ROOT);
$dotenv->load();

require 'globalApp.php';
use Slim\Container;

$app = new \MyAPI\GlobalApp(new Container(
	include INC_ROOT . '/src/config.php'
));
$container = $app->getContainer();

require 'routes.php'

?>