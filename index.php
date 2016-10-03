<?php

if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
	return false;
}

require 'src/bootstrap.php';
$app->run();