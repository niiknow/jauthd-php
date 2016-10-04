<?php
namespace MyAPI\Controllers;

/**
 * HomeController handles our '/' route.
 */
class HomeController extends Controller {
	public function get() {
		return $this->render('home');
	}

	public function getLogin() {
		return $this->render('auth/login');
	}

	public function getForgotPassword() {
		return $this->render('auth/forgot');
	}

	public function getSignUp() {
		return $this->render('auth/signup');
	}

	public function getResetPassword() {
		return $this->render('auth/reset');
	}
}

$app->route(['GET'], '/', \MyAPI\Controllers\HomeController::class)->setName('home');
$app->route(['GET'], '/login', \MyAPI\Controllers\HomeController::class, 'Login')->setName('home.login');
$app->route(['GET'], '/forgotpassword', \MyAPI\Controllers\HomeController::class, 'ForgotPassword')->setName('home.password.forgot');
$app->route(['GET'], '/signup', \MyAPI\Controllers\HomeController::class, 'SignUp')->setName('home.signup');
$app->route(['GET'], '/resetpassword', \MyAPI\Controllers\HomeController::class, 'ResetPassword')->setName('home.password.reset');
