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