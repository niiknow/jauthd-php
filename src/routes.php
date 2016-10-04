<?php
$jwtAuthenticator = new \Slim\Middleware\JwtAuthentication([
	"secure" => false,
	"cookie" => "myapi",
	//"attribute" => "jwt",
	"secret" => getenv('JWT_SECRET'),
	"callback" => function ($request, $response, $arguments) use ($container) {
		echo $arguments["decoded"];
		$container["jwt"] = $arguments["decoded"];
	},
	"rules" => array(
		new \Slim\Middleware\JwtAuthentication\RequestPathRule(array(
			"path" => "/",
			"passthrough" => array("/",
				"/login",
				"/auth/forgotpassword",
				"/auth/login",
				"/auth/resetpassword/{rtoken}",
				"/auth/signup",
				"/auth/emailconfirm/{etoken}",
				"/auth/google",
				"/auth/facebook",
				"/auth/twitter",
				"/auth/github"),
		)),
		new \Slim\Middleware\JwtAuthentication\RequestMethodRule(array(
			"passthrough" => array("OPTIONS"),
		))),
	"error" => function ($arguments) use ($app) {
		$response["status"] = "error";
		$response["message"] = $arguments["message"];
		$app->response->write(json_encode($response, JSON_UNESCAPED_SLASHES));
	},
]);
$app->add($jwtAuthenticator);
$app->add(new \Tuupola\Middleware\Cors([
	"origin" => ["*"],
	"methods" => ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
	"headers.allow" => ["Authorization", "If-Match", "If-Unmodified-Since"],
	"headers.expose" => ["Etag"],
	"credentials" => true,
	"cache" => 86400,
]));

/* Home */
$app->route(['GET'], '/', \MyAPI\Controllers\HomeController::class)->setName('home');
$app->route(['GET'], '/login', \MyAPI\Controllers\HomeController::class, 'Login')->setName('home.login');
$app->route(['GET'], '/forgotpassword', \MyAPI\Controllers\HomeController::class, 'ForgotPassword')->setName('home.password.forgot');
$app->route(['GET'], '/signup', \MyAPI\Controllers\HomeController::class, 'SignUp')->setName('home.signup');
$app->route(['GET'], '/resetpassword', \MyAPI\Controllers\HomeController::class, 'ResetPassword')->setName('home.password.reset');

/* Auth */
$app->group('/auth', function () {
	$this->route(['POST'], '/forgotpassword', \MyAPI\Controllers\AuthController::class, 'postForgotPassword')->setName('auth.password.forgot');
	$this->route(['POST'], '/login', \MyAPI\Controllers\AuthController::class, 'UserLogin')->setName('auth.login');
	$this->route(['POST'], '/resetpassword/{rtoken}', \MyAPI\Controllers\AuthController::class, 'postResetPassword')->setName('auth.password.reset');
	$this->route(['POST'], '/signup', \MyAPI\Controllers\AuthController::class, 'SignUp')->setName('auth.signup');
	$this->route(['GET'], '/emailconfirm/{etoken}', \MyAPI\Controllers\AuthController::class, 'getConfirmEmail')->setName('auth.email.confirm');
});

$app->group('/auth', function () {
	$this->route(['GET'], '/me', \MyAPI\Controllers\AuthController::class, 'Me')->setName('auth.me');
	$this->route(['GET'], '/tokeninfo', \MyAPI\Controllers\AuthController::class, 'TokenInfo')->setName('auth.password.reset');
});

/* Auth Social */
$app->group('/auth', function () {
	$this->route(['POST'], '/google', \MyAPI\Controllers\AuthSocialController::class, 'Google')->setName('auth.google');
	$this->route(['POST'], '/twitter', \MyAPI\Controllers\AuthSocialController::class, 'Twitter')->setName('auth.twitter');
	$this->route(['POST'], '/facebook', \MyAPI\Controllers\AuthSocialController::class, 'Facebook')->setName('auth.facebook');
	$this->route(['POST'], '/github', \MyAPI\Controllers\AuthSocialController::class, 'Github')->setName('auth.github');
});

/* User */
$app->group('/user', function () {
	$this->route(['POST'], '/profile/update', \MyAPI\Controllers\UserController::class, 'UpdateProfile')->setName('user.profile.update');
	$this->route(['POST'], '/changepassword', \MyAPI\Controllers\UserController::class, 'ChangePassword')->setName('user.password.change');
	$this->route(['POST'], '/role/add', \MyAPI\Controllers\UserController::class, 'AddRole')->setName('user.role.add');
	$this->route(['POST'], '/role/delete', \MyAPI\Controllers\UserController::class, 'DeleteRole')->setName('user.role.delete');
});