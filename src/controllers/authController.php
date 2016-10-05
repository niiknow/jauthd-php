<?php

namespace MyAPI\Controllers;

use Respect\Validation\Validator as Respect;

class AuthController extends Controller {
	/**
	 * login to the API
	 * @return jwt token
	 */
	public function postUserLogin() {
		$validation = $this->validator->validate($this->request, [
			'email' => Respect::notEmpty()->noWhitespace()->email(),
			'password' => Respect::stringType()->length(8, 100),
		]);

		if ($validation->failed()) {
			return $this->apiError(304, $validation->errors);
		}

		$params = $this->request->getParsedBody();

		$id = $this->tokenUtil->oid($params['email']);
		$user = $this->authStorage->getUser($this->tenantCode(), $id);

		// validate password
		$isValid = $this->tokenUtil->comparePassword($params['password'], $user['passwd']);
		if (!$isValid) {
			return $this->apiError(1003);
		}

		$profile = json_decode($user['userprofile']);
		$payload = [
			'userid' => $id,
			'roles' => $user['roles'],
		];

		$access_type = isset($params['access_type']) ? $params['access_type'] : 'offline';
		$token = $this->tokenUtil->generateLoginToken($payload, null, $access_type);

		$this->authStorage->updateLogin($this->tenantCode(), $id, json_encode($this->request->getHeaders()));

		// return token
		setcookie(getenv('JWT_COOKIE'), $token['access_token'], time() + $token['expires_in'], '/');
		return $this->apiSuccess($token);
	}

	/**
	 * logout of the API
	 */
	public function getUserLogout() {
		$token = $this->request->getAttribute('jwt');
		$id = $token->sub;
		setcookie(getenv('JWT_COOKIE'), null, -1, '/');
		return $this->apiSuccess($id);
	}

	/**
	 * send reset password token
	 */
	public function postForgotPassword() {
		$validation = $this->validator->validate($this->request, [
			'email' => Respect::notEmpty()->noWhitespace()->email(),
		]);

		if ($validation->failed()) {
			return $this->apiError(304, $validation->errors);
		}

		$email = $this->param('email');
		$id = $this->tokenUtil->oid($email);
		$user = $this->authStorage->getUser($this->tenantCode(), $id);
		if (isset($user['userid'])) {
			$token = $this->tokenUtil->generateForgotPasswordToken($id);

			// send reset email
			$emailResetTemplate = getenv('MAIL_PASSWORD_RESET');
			if ($emailResetTemplate) {
				$uri = $this->request->getUri();
				$profile = json_decode($user['userprofile']);

				// send registration email
				$this->mail()->send($emailResetTemplate,
					['user' => $user, 'userprofile' => $profile, 'token' => urlencode($token['token']), 'uri' => $uri],
					function ($message) use ($user) {
						$message->to($user->email);
					});
				$this->authStorage->forgotPassword($this->tenantCode(), $id, $uri->getBaseUrl(), $token);
			}

			return $this->apiSuccess($user);
		}
		return $this->apiError(1002);
	}

	/**
	 * get user details
	 */
	public function getMe() {
		$token = $this->request->getAttribute('jwt');
		$id = $token->sub;

		$user = $this->authStorage->getUser($this->tenantCode(), $id);
		if (isset($user['userid'])) {
			return $this->apiSuccess($user);
		}

		return $this->apiError(1002);
	}

	/**
	 * send reset password token
	 */
	public function postResetPassword() {
		$validation = $this->validator->validate($this->request, [
			'password' => Respect::stringType()->length(8, 100),
			'passwordConfirm' => Respect::passwordConfirmation($request->getParam('password')),
		]);
		if ($validation->failed()) {
			return $this->apiError(304, $validation->errors);
		}

		$params = $this->request->getParsedBody();

		$rtoken = $this->queryParam('rtoken');
		$token = $this->tokenUtil->verifyForgotPasswordToken($rtoken);
		$id = $token->sub;
		$uri = $this->request->getUri();
		$this->authStorage->updatePassword($this->tenantCode(), $id, $params['password'], $uri->getBaseUrl());

		$user = $this->authStorage->getUser($this->tenantCode(), $id);
		if (isset($user['userid'])) {
			// send email
			$emailChangeTemplate = getenv('MAIL_PASSWORD_CHANGE');
			if ($emailChangeTemplate) {
				$uri = $this->request->getUri();
				$profile = json_decode($user['userprofile']);

				// send registration email
				$this->mail()->send($emailChangeTemplate,
					['user' => $user, 'userprofile' => $profile, 'uri' => $uri],
					function ($message) use ($user) {
						$message->to($user->email);
					});
			}

			return $this->apiSuccess($user);
		}

		return $this->apiSuccess($id);
	}

	/**
	 * token verification
	 */
	public function getTokenInfo() {
		$token = $this->request->getAttribute('jwt');
		return $this->apiSuccess($token);
	}

	/**
	 * email verification
	 */
	public function getConfirmEmail() {
		$etoken = $this->queryParam('etoken');

		$token = $this->tokenUtil->verifyEmailConfirmationToken($etoken);
		$id = $token->sub;
		$uri = $this->request->getUri();
		$this->authStorage->updateEmailVerification($this->tenantCode(), $id, $uri->getBaseUrl(), $token);
		return $this->apiSuccess($id);
	}

	/**
	 * signup or register
	 */
	public function postSignUp() {
		$validation = $this->validator->validate($this->request, [
			'email' => Respect::notEmpty()->noWhitespace()->email(),
			'password' => Respect::stringType()->length(8, 100),
			'passwordConfirm' => Respect::passwordConfirmation($request->getParam('password')),
		]);
		if ($validation->failed()) {
			return $this->apiError(304, $validation->errors);
		}

		$params = $this->request->getParsedBody();

		// do insert
		$user = $this->authStorage->insertUser($this->tenantCode(), $params);

		if (isset($user['userid'])) {
			$emailVerifyTemplate = getenv('MAIL_VERIFY');
			if ($emailVerifyTemplate) {
				$token = $this->tokenUtil->generateEmailConfirmationToken($user['userid']);
				$uri = $this->request->getUri();
				$profile = json_decode($user['userprofile']);

				// send registration email
				$this->mail()->send($emailVerifyTemplate,
					['user' => $user, 'userprofile' => $profile, 'token' => $token['token'], 'uri' => $uri],
					function ($message) use ($user) {
						$message->to($user['email']);
					});
			}

			return $this->apiSuccess($user['userid']);
		} else {
			return $this->apiError(1006);
		}
	}
}

$app->group('/api/auth', function () {
	$this->route(['POST'], '/forgotpassword', \MyAPI\Controllers\AuthController::class, 'postForgotPassword')->setName('auth.password.forgot');
	$this->route(['POST'], '/login', \MyAPI\Controllers\AuthController::class, 'UserLogin')->setName('auth.login');
	$this->route(['POST'], '/resetpassword', \MyAPI\Controllers\AuthController::class, 'postResetPassword')->setName('auth.password.reset');
	$this->route(['POST'], '/signup', \MyAPI\Controllers\AuthController::class, 'SignUp')->setName('auth.signup');
	$this->route(['GET'], '/emailconfirm', \MyAPI\Controllers\AuthController::class, 'ConfirmEmail')->setName('auth.email.confirm');
});

$app->group('/api/auth', function () {
	$this->route(['GET'], '/me', \MyAPI\Controllers\AuthController::class, 'Me')->setName('auth.me');
	$this->route(['GET'], '/logout', \MyAPI\Controllers\AuthController::class, 'UserLogout')->setName('auth.logout');
	$this->route(['GET'], '/tokeninfo', \MyAPI\Controllers\AuthController::class, 'TokenInfo')->setName('auth.password.reset');
});