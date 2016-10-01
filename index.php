<?php

if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
	return false;
}

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/src/config/constants.php';

$settings = require __DIR__ . '/src/config/settings.php';

$app = new \Slim\App($settings);

require __DIR__ . '/src/config/dependencies.php';
require __DIR__ . '/src/middleware/Auth.php';

require __DIR__ . '/src/routes.php';

$app->run();