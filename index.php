<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
	return false;
}

require 'src/bootstrap.php';
$app->run();