<?php

return [
	'settings' => [
		'determineRouteBeforeAppMiddleware' => true,
		'displayErrorDetails' => true,
		'viewTemplateDirectory' => '/views',
		'dbinfo' => [
			'database_type' => getenv('DB_TYPE') or 'mysql',
			'database_name' => getenv('DB_DATABASE') or 'mydb',
			'server' => getenv('DB_HOST') or '127.0.0.1',
			'username' => getenv('DB_USERNAME') or 'root',
			'password' => getenv('DB_PASSWORD'),
			'charset' => getenv('DB_CHARSET') or 'utf8',
		],
		'excludedRoutes' => [],
	],
	'twig' => function ($container) {
		$twig = new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__ . $container['settings']['viewTemplateDirectory']));
		return $twig;
	},
	'view' => function ($container) {
		$view = new \Slim\Views\Twig($container['settings']['viewTemplateDirectory'], [
			'debug' => true,
		]);
		$view->addExtension(new \Slim\Views\TwigExtension($container['router'], $container['request']->getUri()));
		return $view;
	},
	'logger' => function($container) {
		$settings = $container->get('settings');
		$logger = new Logger($settings['logger']['name']);
		$rotating = new RotatingFileHandler(__DIR__ . $settings['logger']['path'], 0, Logger::DEBUG);
		$logger->pushHandler($rotating);
		return $logger;
	},
	'errorHandler' => function ($container) {
		return function ($request, $response, $exception) use ($container) {
			$response = $response->withStatus(500);
			return $container->view->render($response, 'errors/500.twig', [
				'error' => $exception->getMessage(),
			]);
		};
	},
	'notFoundHandler' => function ($container) {
		return function ($request, $response) use ($container) {
			$response = $response->withStatus(404);
			return $container->view->render($response, 'errors/404.twig', [
				'request_uri' => urldecode($_SERVER['REQUEST_URI']),
			]);
		};
	},
	'notAllowedHandler' => function ($container) {
		return function ($request, $response, $methods) use ($container) {
			$response = $response->withStatus(405);
			return $container->view->render($response, 'errors/405.twig', [
				'request_uri' => $_SERVER['REQUEST_URI'],
				'method' => $_SERVER['REQUEST_METHOD'],
				'methods' => implode(', ', $methods),
			]);
		};
	},
	'config' => [
		'mail' => [
		'type' => 'smtp',
		'host' => getenv('MAIL_HOST'),
		'port' => getenv('MAIL_PORT'),
		'username' => getenv('MAIL_USERNAME'),
		'password' => getenv('MAIL_PASSWORD'),
		'auth' => getenv('MAIL_USERNAME') or false,
		'TLS' => false,
		'from' => [
			'name' => 'friends',
			'email' => getenv('MAIL_FROM'),
		],
	],
	'mail' => function ($container) {
		$mailer = new PHPMailer();
		$mailer->isSMTP();
		$mailer->Host = $container['config']['mail']['host'];
		$mailer->SMTPAuth = $container['config']['mail']['auth'];
		$mailer->SMTPSecure = $container['config']['mail']['TLS'];
		$mailer->Port = $container['config']['mail']['port'];
		$mailer->Username = $container['config']['mail']['username'];
		$mailer->Password = $container['config']['mail']['password'];
		$mailer->FromName = $container['config']['mail']['from']['name'];
		$mailer->From = $container['config']['mail']['from']['email'];
		$mailer->isHTML(true);
		return new \JAuth\Mail\Mailer($mailer, $container);
	},
];