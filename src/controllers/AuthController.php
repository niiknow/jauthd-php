<?php

namespace JAuth\Controllers;

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
		$isValid = $this->util->comparePassword($this->util->hashPassword($params['password']), $user['password']);
		if (!$isValid) {
			return $this->apiError(1003);
		}

		$profile = json_decode($user['profile']);
		$payload = [
		  'roles' = $user['roles']
		];

		// return token
		$token = $this->authHelper->generateLoginToken($payload, null, $params['access_type']);
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
				$this->storage->forgotPassword()
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
     * [getResetPassword description]
     * @return [type] [description]
     */
	public function getResetPassword() {
		return $this->render('auth/resetpassword');
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

		$ftoken = $this->args['ftoken'];
		$isValid = $this->authHelper->verifyForgotPasswordToken($ftoken);
		if ($isValid) {
			return $this->apiError(1002);
		}

		$token = $this->authHelper->decodeToken($ftoken);
		$id = $token->getClaim('sub');
		$uri = $this->request->getUri();
		$this->storage->updatePassword($this->queryParam('tenantCode'), $id, $params['password'], $uri->getBaseUrl());
		return $this->apiSuccess($id);
	}

	/**
	 * token verification
	 */
	public function getTokenInfo() {
		// must call tokeninfo with Bearer header
		$token = $this->request->getAttribute('jwt');
		return $this->apiSuccess($token);
	}

	/**
	 * email verification
	 */
	public function getVerifyEmail() {
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
			"profile" => "required|valid_json_string",
			"social" => "valid_json_string",
			"secure" => "valid_json_string",
		]);

		if ($isValid !== true) {
			return $this->apiError(500, $isValid);
		}

		// do insert
		$userId = $this->storage->insertUser($this->queryParam('tenantCode'), $params);

		if ($userId) {
			$token = $this->authHelper->generateEmailConfirmationToken($userId);
			$emailVerifyTemplate = getenv('MAIL_VERIFY');
			if ($emailVerifyTemplate) {
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