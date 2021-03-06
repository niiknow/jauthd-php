<?php
// this link all the individual configurations together
// Slim, DB, Email, View, etc....
// think of it like an index for all configs

return [

/* SlimApp Settings */
	'settings' => [
		'determineRouteBeforeAppMiddleware' => false,
		'displayErrorDetails' => true,
		'viewTemplateDirectory' => '/views',
		'logger' => [
			'name' => 'myapi',
			'path' => '/data/logs/myapi.log',
		],
		'dbinfo' => [
			'database_type' => getenv('DB_TYPE'),
			'database_name' => getenv('DB_DATABASE'),
			'server' => getenv('DB_HOST'),
			'username' => getenv('DB_USERNAME'),
			'password' => getenv('DB_PASSWORD'),
			'charset' => getenv('DB_CHARSET'),
		],
		'excludedRoutes' => [],
	],
/* Custom configuration */
	'config' => [
		'azureStorageConnection' => getenv('AZURE_STORAGE_CONNECTION'),
		'mail' => [
			'type' => 'smtp',
			'host' => getenv('MAIL_HOST'),
			'port' => getenv('MAIL_PORT'),
			'username' => getenv('MAIL_USERNAME'),
			'password' => getenv('MAIL_PASSWORD'),
			'auth' => getenv('MAIL_USERNAME') ? true : false,
			'TLS' => false,
			'from' => [
				'name' => getenv('MAIL_FROM_NAME'),
				'email' => getenv('MAIL_FROM'),
			],
		]],
/* Various container injection: middlewares, logger, mailer, etc... */
	'twig' => function ($container) {
		$twig = new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__ . $container['settings']['viewTemplateDirectory']));
		return $twig;
	},
	'view' => function ($container) {
		$view = new \Slim\Views\Twig(__DIR__ . $container['settings']['viewTemplateDirectory'], [
			'debug' => true,
		]);
		$view->addExtension(new \Slim\Views\TwigExtension($container['router'], $container['request']->getUri()));
		return $view;
	},
	'logger' => function ($container) {
		$settings = $container->get('settings');
		$logger = new \Monolog\Logger($settings['logger']['name']);
		$rotating = new \Monolog\Handler\RotatingFileHandler(__DIR__ . $settings['logger']['path'], 0, \Monolog\Logger::DEBUG);
		$logger->pushHandler($rotating);
		return $logger;
	},
	'validator' => function ($container) {
		return new \MyAPI\Lib\Validator();
	},
	'tokenUtil' => function($container) {
		return new \MyAPI\Lib\TokenUtil();
	},
	'authStorage' => function($container) {
		$db_storage = getenv('DB_STORAGE') ? getenv('DB_STORAGE') : '\MyAPI\Lib\Storages\MedooStorage';
		$this->storage = new $db_storage($container);
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
	'JwtAuthentication' => function ($container) {
		$jwtAuth = new \Slim\Middleware\JwtAuthentication([
			"path" => "/api",
			"passthrough" => [
				"/api/auth/forgotpassword",
				"/api/auth/login",
				"/api/auth/resetpassword/",
				"/api/auth/signup",
				"/api/auth/emailconfirm/",
				"/api/auth/google",
				"/api/auth/facebook",
				"/api/auth/twitter",
				"/api/auth/github"],
			"secret" => getenv("JWT_SECRET"),
			"logger" => $container["logger"],
			"cookie" => getenv('JWT_COOKIE'),
			"algorithm" => ["HS256"],
			"attribute" => "jwt",
			"error" => function ($request, $response, $arguments) {
				$data["status"] = "error";
				$data["message"] = $arguments["message"];
				return $response
					->withHeader("Content-Type", "application/json")
					->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
			},
			"callback" => function ($request, $response, $arguments) use ($container) {
				// $container["token"]->hydrate($arguments["decoded"]);
			},
		]);

		return $jwtAuth;
	},
	'Cors' => function ($container) {
		return new \Tuupola\Middleware\Cors([
			"logger" => $container["logger"],
			"origin" => ["*"],
			"methods" => ["GET", "POST", "OPTIONS"],
			"headers.allow" => ["Authorization", "If-Match", "If-Unmodified-Since"],
			"headers.expose" => ["Authorization", "Etag"],
			"credentials" => true,
			"cache" => 60,
			"error" => function ($request, $response, $arguments) {
				$data["status"] = "error";
				$data["message"] = $arguments["message"];
				return $response
					->withHeader("Content-Type", "application/json")
					->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
			},
		]);
	},
	'mailer' => function ($container) {
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
		return new \MyAPI\Lib\Mail\Mailer($mailer, $container);
	},
];

?>