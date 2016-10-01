<?php 
	
	use Monolog\Logger;
	use Monolog\handler\StreamHandler;

	$container = $app->getContainer();

	/*for twig Templating*/

	$container['twigview'] = function($c){
		$settings = $c->get('settings');
		$loader = new Twig_loader_Filesystem($settings['view']['template_path']);
		$twig = new Twig_Environment($loader);
		return $twig;
	};



	/*app logger*/
	$container['logger'] = function($c){
		$settings = $c->get('settings');
		$logger = new Logger( $settings['logger']['name'] );
		$logger->pushHandler( new StreamHandler( $settings['logger']['path'] ),Logger::DEBUG  );
		return $logger;
	};

	/*Middleware*/
	$container['Middleware\Auth'] = function($c) use ($app){
		return new Middleware\Auth($app,$c->get('jauthDB'));
	};

	/*Event MNGMNT*/

	$container['eventDB'] = function($c){
		$settings = $c->get('settings');
		return new medoo( $settings['jauthDB'] );
	};


	/*controller*/
	$container['Controller\Login'] = function($c){
		return new Controller\Login($c);
	};
	
	$container['Controller\User'] = function($c){
		return new Controller\User($c);
	};


	
