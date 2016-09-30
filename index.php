<?php 
	
	if(PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__ ){
		return false;
	}


	require __DIR__ . '/vendor/autoload.php';

	require __DIR__ . '/app/Config/constants.php';

	$settings = require __DIR__ . '/app/Config/settings.php';

	$app = new \Slim\App( $settings );

	require __DIR__ . '/app/Config/dependencies.php';
	require __DIR__ . '/app/Module/Middleware/Auth.php';

	require __DIR__ . '/app/Module/Routes/routes.php';

	$app->run();