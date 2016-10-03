<?php
/* Home */
$app->route(['GET'], '/', \MyAPI\Controllers\HomeController::class)->setName('home');

/* Auth */
$app->group('/auth', function () {\
	$this->route(['POST'], '/forgotpassword', \MyAPI\Controllers\AuthController::class, 'postForgotPassword')->setName('auth.password.forgot');
	$this->route(['POST'], '/login', \MyAPI\Controllers\AuthController::class, 'postUserLogin')->setName('auth.login');
	$this->route(['POST'], '/resetpassword', \MyAPI\Controllers\AuthController::class, 'postResetPassword')->setName('auth.password.reset');
	$this->route(['POST'], '/signup', \MyAPI\Controllers\AuthController::class, 'postSignUp')->setName('auth.signup');
	$this->route(['GET'], '/emailconfirm/{etoken}', \MyAPI\Controllers\AuthController::class, 'getConfirmEmail')->setName('auth.email.confirm');
});

$app->group('/auth', function () {
	$this->route(['GET'], '/me', \MyAPI\Controllers\AuthController::class, 'getMe')->setName('auth.me');
	$this->route(['GET'], '/tokeninfo', \MyAPI\Controllers\AuthController::class, 'getTokenInfo')->setName('auth.password.reset');
})->add(new \Slim\Middleware\JwtAuthentication([
	"attribute" => "jwt",
	"secret" => getenv('JWT_SECRET'),
]));

/* Auth Social */
$app->group('/auth', function () {\
	$this->route(['POST'], '/google', \MyAPI\Controllers\AuthSocialController::class, 'postGoogle')->setName('auth.google');
	$this->route(['POST'], '/twitter', \MyAPI\Controllers\AuthSocialController::class, 'postTwitter')->setName('auth.twitter');
	$this->route(['POST'], '/facebook', \MyAPI\Controllers\AuthSocialController::class, 'postFacebook')->setName('auth.facebook');
	$this->route(['POST'], '/github', \MyAPI\Controllers\AuthSocialController::class, 'postGithub')->setName('auth.github');
});

/* User */
$app->group('/user', function () {\
	$this->route(['POST'], '/profile/update', \MyAPI\Controllers\UserController::class, 'postUpdateProfile')->setName('user.profile.update');
	$this->route(['POST'], '/changepassword', \MyAPI\Controllers\UserController::class, 'postTwitter')->setName('user.password.change');
	$this->route(['POST'], '/role/add', \MyAPI\Controllers\UserController::class, 'postAddRole')->setName('user.role.add');
	$this->route(['POST'], '/role/delete', \MyAPI\Controllers\UserController::class, 'deleteRole')->setName('user.role.delete');
});