<?php
/* Home */
$app->route(['GET'], '/', \JAuth\Controllers\HomeController::class)->setName('home');

/* Auth */
$app->group('/auth', function () {\
	$this->route(['POST'], '/forgotpassword', \JAuth\Controllers\AuthController::class, 'postForgotPassword')->setName('auth.password.forgot');
	$this->route(['POST'], '/login', \JAuth\Controllers\AuthController::class, 'postUserLogin')->setName('auth.login');
	$this->route(['POST'], '/resetpassword', \JAuth\Controllers\AuthController::class, 'postResetPassword')->setName('auth.password.reset');
	$this->route(['POST'], '/signup', \JAuth\Controllers\AuthController::class, 'postSignUp')->setName('auth.signup');
	$this->route(['GET'], '/verifyemail/{etoken}', \JAuth\Controllers\AuthController::class, 'getVerifyEmail')->setName('auth.email.verify');
});

$app->group('/auth', function () {
	$this->route(['GET'], '/me', \JAuth\Controllers\AuthController::class, 'getMe')->setName('auth.me');
	$this->route(['GET'], '/tokeninfo', \JAuth\Controllers\AuthController::class, 'getTokenInfo')->setName('auth.password.reset');
})->add(new \Slim\Middleware\JwtAuthentication([
	"attribute" => "jwt",
	"secret" => getenv('JWT_SECRET'),
]));

/* Auth Social */
$app->group('/auth', function () {\
	$this->route(['POST'], '/google', \JAuth\Controllers\AuthSocialController::class, 'postGoogle')->setName('auth.google');
	$this->route(['POST'], '/twitter', \JAuth\Controllers\AuthSocialController::class, 'postTwitter')->setName('auth.twitter');
	$this->route(['POST'], '/facebook', \JAuth\Controllers\AuthSocialController::class, 'postFacebook')->setName('auth.facebook');
	$this->route(['POST'], '/github', \JAuth\Controllers\AuthSocialController::class, 'postGithub')->setName('auth.github');
});

/* User */
$app->group('/user', function () {\
	$this->route(['POST'], '/profile/update', \JAuth\Controllers\UserController::class, 'postUpdateProfile')->setName('user.profile.update');
	$this->route(['POST'], '/changepassword', \JAuth\Controllers\UserController::class, 'postTwitter')->setName('user.password.change');
	$this->route(['POST'], '/role/add', \JAuth\Controllers\UserController::class, 'postAddRole')->setName('user.role.add');
	$this->route(['POST'], '/role/delete', \JAuth\Controllers\UserController::class, 'deleteRole')->setName('user.role.delete');
});