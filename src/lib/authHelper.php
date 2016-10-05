<?php
namespace MyAPI\Lib;

use Ramsey\Uuid\Uuid;
use \Firebase\JWT\JWT;

/**
 * Auth Helper
 */
class AuthHelper {
	/**
	 * determine of email confirmation token is valid
	 * @param  $token the token
	 * @return validation result
	 */
	public function verifyEmailConfirmationToken($token) {
		return $this->verifyToken($token, 'emailConfirm');
	}

	/**
	 * generate an email confirmation token to be sent with sub
	 * @param  $sub              the sub
	 * @param  $expiresIn        optional - default 2 days in seconds
	 * @return the token
	 */
	public function generateEmailConfirmationToken($sub, $expiresIn = null) {
		return $this->generateToken(['sub' => $sub], 'emailConfirm', $expiresIn);
	}

	/**
	 * verify forgot password token
	 * @param  $token
	 * @return true if valid
	 */
	public function verifyForgotPasswordToken($token) {
		return $this->verifyToken($token, 'forgotPassword');
	}

	/**
	 * generate a forgot password token to be sent with sub
	 * @param  $sub              the sub
	 * @param  $expiresIn        optional - default other token
	 * @return the token
	 */
	public function generateForgotPasswordToken($sub, $expiresIn = null) {
		return $this->generateToken(['sub' => $sub], 'forgotPassword', $expiresIn);
	}

	/**
	 * verify a token
	 * @param  $token     the token
	 * @param  $tokenType optional - token type
	 * @return a promise
	 */
	public function verifyToken($tokenString, $tokenType) {
		$tokenType = !isset($tokenType) ? '' : $tokenType;
		$key = getenv('JWT_SECRET') . $tokenType;

		return $this->decodeToken($tokenString, $key);
	}

	/**
	 * generate login token
	 * @param  $user the user object
	 * @param  $expiresIn expire in second
	 * @param  $access_type 'offline' or not
	 */
	public function generateLoginToken($user, $expiresIn, $access_type, $isPayload = true) {
		$tokenPayload = $isPayload ? $user : __::Pick($user, getenv('JWT_INCLUDES'));
		$access_token = $this->generateToken($tokenPayload, '', $expiresIn);
		$result = [
			'userprofile' => $tokenPayload,
			'access_token' => $access_token['token'],
			'expires_in' => $access_token['expires_in'],
		];

		if ($access_type === 'offline') {
			$result['refresh_token'] = $this->generateToken([
				'sub' => $user['userid'],
				'userprofile' => $tokenPayload,
			], 'refresh', getenv('JWT_REFRESH_AGE'))['token'];
		}

		return $result;
	}

	/**
	 * generate a token with the payload json
	 * @param  $payload          the token data
	 * @param  $tokenType        optional token type
	 * @param  $expiresIn        override expiration
	 * @return the token
	 */
	public function generateToken($payload, $tokenType, $expiresIn = null) {
		$tokenType = !isset($tokenType) ? '' : $tokenType;
		$key = getenv('JWT_SECRET') . $tokenType;

		$maxExpires = getenv('JWT_AUTH_AGE');
		$pl = array_merge(['jti' => Uuid::uuid4()], $payload);

		if (!empty($tokenType)) {
			$maxExpires = getenv('JWT_OTHER_AGE');
		}

		if ($expiresIn) {
			if ($expiresIn > $maxExpires) {
				$expiresIn = $maxExpires;
			}
		} else {
			$expiresIn = $maxExpires;
		}

		/*
			    $pl2 = __::pluck($pl, 'password');
			    foreach($pl2 as $key => $value){
			      $token->set($key, $value);
		*/

		$token = array(
			"iss" => 'test',
			"jti" => $pl['jti'],
			"sub" => isset($pl['userid']) ? $pl['userid'] : $pl['sub'],
			"exp" => time() + $expiresIn, // or 'ttl' => 60
			"iat" => time(),
			"nbf" => time());

		if (isset($pl['roles'])) {
			$token['roles'] = $pl['roles'];
		}

		$jwt = \Firebase\JWT\JWT::encode($token, $key, 'HS256');

		$result = [
			'expires_in' => $expiresIn,
			'token' => $jwt,
		];

		return $result;
	}

	/**
	 * allow for decoding of jwt
	 */
	private function decodeToken($token, $key) {
		return \Firebase\JWT\JWT::decode($token, $key, array('HS256'));
	}
}