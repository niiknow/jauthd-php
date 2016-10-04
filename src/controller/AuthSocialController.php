<?php

namespace MyAPI\Controllers;

class AuthSocialController extends Controller {
	public function postGoogle() {

	}

	public function postTwitter() {

	}

	public function postFacebook() {

	}

	public function postInstagram() {

	}
}

$app->group('/auth', function () {
	$this->route(['POST'], '/google', \MyAPI\Controllers\AuthSocialController::class, 'Google')->setName('auth.google');
	$this->route(['POST'], '/twitter', \MyAPI\Controllers\AuthSocialController::class, 'Twitter')->setName('auth.twitter');
	$this->route(['POST'], '/facebook', \MyAPI\Controllers\AuthSocialController::class, 'Facebook')->setName('auth.facebook');
	$this->route(['POST'], '/github', \MyAPI\Controllers\AuthSocialController::class, 'Github')->setName('auth.github');
});
