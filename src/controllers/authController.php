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
		$user = $this->storage->getUser($this->queryParam('tenantCode'), $id);

		// validate password
		$isValid = $this->util->comparePassword($params['password'], $user['password']);
		if (!$isValid) {
			return $this->apiError(1003);
		}

		$profile = json_decode($user['profile']);
		$payload = [
			'id' => $id,
			'roles' => $user['roles'],
		];

		// return token
		$access_type = isset($params['access_type']) ? $params['access_type'] : 'offline';
		$token = $this->authHelper->generateLoginToken($payload, null, $access_type);
		setcookie("myapi", $token['access_token'], time() + 3600);
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
		$user = $this->storage->getUser($this->queryParam('tenantCode'), $id);
		if (isset($user['id'])) {
			$token = $this->authHelper->generateForgotPasswordToken($id);
			// send reset email
			$emailResetTemplate = getenv('MAIL_PASSWORD_RESET');
			if ($emailResetTemplate) {
				$uri = $this->request->getUri();
				// send registration email
				$this->mail()->send($emailResetTemplate,
					['user' => $user, 'token' => $token, 'uri' => $uri],
					function ($message) use ($user) {
						$message->to($user->email);
					});
				$this->storage->forgotPassword();
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
		$id = $token['sub'];

		$user = $this->storage->getUser($this->queryParam('tenantCode'), $id);
		if (isset($user['id'])) {
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

		$ftoken = $this->args['rtoken'];
		$isValid = $this->authHelper->verifyForgotPasswordToken($ftoken);
		if ($isValid) {
			return $this->apiError(1002);
		}

		$token = $this->authHelper->decodeToken($ftoken);
		$id = $token['sub'];
		$uri = $this->request->getUri();
		$this->storage->updatePassword($this->queryParam('tenantCode'), $id, $params['password'], $uri->getBaseUrl());
		return $this->apiSuccess($id);
	}

	/**
	 * token verification
	 */
	public function getTokenInfo() {
		// must call tokeninfo with Bearer header
		//$token = $this->container['jwt'];
		$token = 'hi';
		return $this->apiSuccess($token);
	}

	/**
	 * email verification
	 */
	public function getConfirmEmail() {
		$etoken = $this->args['etoken'];

		$isValid = $this->authHelper->verifyEmailConfirmationToken($etoken);
		if ($isValid) {
			$token = $this->authHelper->decodeToken($etoken);
			$id = $token->getClaim('sub');
			$uri = $this->request->getUri();
			$this->storage->updateEmailVerification($this->queryParam('tenantCode'), $id, $uri->getBaseUrl(), $token);
			return $this->apiSuccess($id);
		}

		return $this->apiError(1002);
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
		$userId = $this->storage->insertUser($this->queryParam('tenantCode'), $params);

		if ($userId) {
			$emailVerifyTemplate = getenv('MAIL_VERIFY');
			if ($emailVerifyTemplate) {
				$token = $this->authHelper->generateEmailConfirmationToken($userId);
				$uri = $this->request->getUri();

				// send registration email
				$this->mail()->send($emailVerifyTemplate,
					['user' => $user, 'token' => $token, 'uri' => $uri],
					function ($message) use ($user) {
						$message->to($user->email);
					});
			}

			return $this->apiSuccess($userId);
		} else {
			return $this->apiError(1006);
		}
	}
}

$app->group('/api/auth', function () {
	$this->route(['POST'], '/forgotpassword', \MyAPI\Controllers\AuthController::class, 'postForgotPassword')->setName('auth.password.forgot');
	$this->route(['POST'], '/login', \MyAPI\Controllers\AuthController::class, 'UserLogin')->setName('auth.login');
	$this->route(['POST'], '/resetpassword/{rtoken}', \MyAPI\Controllers\AuthController::class, 'postResetPassword')->setName('auth.password.reset');
	$this->route(['POST'], '/signup', \MyAPI\Controllers\AuthController::class, 'SignUp')->setName('auth.signup');
	$this->route(['GET'], '/emailconfirm/{etoken}', \MyAPI\Controllers\AuthController::class, 'getConfirmEmail')->setName('auth.email.confirm');
});

$app->group('/api/auth', function () {
	$this->route(['GET'], '/me', \MyAPI\Controllers\AuthController::class, 'Me')->setName('auth.me');
	$this->route(['GET'], '/tokeninfo', \MyAPI\Controllers\AuthController::class, 'TokenInfo')->setName('auth.password.reset');
});