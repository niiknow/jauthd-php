<?php 
	
	if(PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__ ){
		return false;
	}


	require __DIR__ . '/vendor/autoload.php';

	require __DIR__ . '/src/Config/constants.php';

	$settings = require __DIR__ . '/src/Config/settings.php';

	$app = new \Slim\App( $settings );

	require __DIR__ . '/src/Config/dependencies.php';
	require __DIR__ . '/src/Middleware/Auth.php';

	require __DIR__ . '/src/Routes/routes.php';

	$app->run();