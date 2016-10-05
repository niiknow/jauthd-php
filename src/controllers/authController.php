<?php

namespace MyAPI\Controllers;

class AuthController extends Controller {
	/**
	 * login to the API
	 * @return jwt token
	 */
	public function postUserLogin() {
		$params = $this->request->getParsedBody();

		$isValid = $this->validator()->is_valid($params, [
			'email' => 'required|valid_email',
			'password' => 'required|max_len,100|min_len,8',
		]);

		if ($isValid !== true) {
			return $this->apiError(1003);
		}

		$id = $this->util->oid($params['email']);
		$user = $this->storage->getUser($this->tenantCode(), $id);

		// validate password
		$isValid = $this->util->comparePassword($params['password'], $user['passwd']);
		if (!$isValid) {
			return $this->apiError(1003);
		}

		$profile = json_decode($user['userprofile']);
		$payload = [
			'userid' => $id,
			'roles' => $user['roles'],
		];

		$access_type = isset($params['access_type']) ? $params['access_type'] : 'offline';
		$token = $this->authHelper->generateLoginToken($payload, null, $access_type);

		$this->storage->updateLogin($this->tenantCode(), $id, json_encode($this->request->getHeaders()));

		// return token
		setcookie(getenv('JWT_COOKIE'), $token['access_token'], time() + $token['expires_in']);
		return $this->apiSuccess($token);
	}

	/**
	 * send reset password token
	 */
	public function postForgotPassword() {
		$email = $this->param('email');

		$isValid = $this->validator()->is_valid(['email' => $email], [
			'email' => 'required|valid_email',
		]);
		if ($isValid !== true) {
			return $this->apiError(500, $isValid);
		}

		$id = $this->util->oid($email);
		$user = $this->storage->getUser($this->tenantCode(), $id);
		if (isset($user['userid'])) {
			$token = $this->authHelper->generateForgotPasswordToken($id);

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
				$this->storage->forgotPassword($this->tenantCode(), $id, $uri->getBaseUrl(), $token);
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

		$user = $this->storage->getUser($this->tenantCode(), $id);
		if (isset($user['userid'])) {
			return $this->apiSuccess($user);
		}

		return $this->apiError(1002);
	}

	/**
	 * send reset password token
	 */
	public function postResetPassword() {
		$params = $this->request->getParsedBody();
		$isValid = $this->validator()->is_valid($params, [
			'password' => 'required|max_len,100|min_len,8',
			'confirm' => 'required|max_len,100|min_len,8',
		]);

		if ($isValid !== true) {
			return $this->apiError(500, $isValid);
		}

		$rtoken = $this->queryParam('rtoken');
		$token = $this->authHelper->verifyForgotPasswordToken($rtoken);
		$id = $token->sub;
		$uri = $this->request->getUri();
		$this->storage->updatePassword($this->tenantCode(), $id, $params['password'], $uri->getBaseUrl());

		$user = $this->storage->getUser($this->tenantCode(), $id);
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

		$token = $this->authHelper->verifyEmailConfirmationToken($etoken);
		$id = $token->sub;
		$uri = $this->request->getUri();
		$this->storage->updateEmailVerification($this->tenantCode(), $id, $uri->getBaseUrl(), $token);
		return $this->apiSuccess($id);
	}

	/**
	 * signup or register
	 */
	public function postSignUp() {
		$params = $this->request->getParsedBody();
		$validator = $this->validator();

		$isValid = $validator->validate($params, [
			"email" => "required|valid_email",
			"password" => "required|max_len,100|min_len,8",
			"profile" => "valid_json_string",
			"social" => "valid_json_string",
			"secure" => "valid_json_string",
		]);

		if ($isValid !== true) {
			return $this->apiError(500, $isValid);
		}

		// do insert
		$user = $this->storage->insertUser($this->tenantCode(), $params);

		if (isset($user['userid'])) {
			$emailVerifyTemplate = getenv('MAIL_VERIFY');
			if ($emailVerifyTemplate) {
				$token = $this->authHelper->generateEmailConfirmationToken($user['userid']);
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
	$this->route(['GET'], '/tokeninfo', \MyAPI\Controllers\AuthController::class, 'TokenInfo')->setName('auth.password.reset');
});