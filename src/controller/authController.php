<?php

namespace App\Controller;

use Hautelook\Phpass\PasswordHash;
use Helper\Message as Message;
use Helper\Tool as Tool;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Login {

	protected $eventDB;

	function __construct($app) {
		$this->eventDB = $app->eventDB;
		$this->PasswordHasher = new PasswordHash(12, false);
	}

	function userLogin(Request $req, Response $res) {
		$params = $req->getParsedBody();

		$isValid = \GUMP::is_valid($params, [
			'email' => 'required|valid_email',
			'password' => 'required|max_len,100|min_len,8',
		]);

		if ($isValid === true) {
			return $res->withJson(SELF::checkAuthentications($params));
		} else {
			return $res->withJson(Message::setMessage(500, $isValid));
		}
	}

	function checkAuthentications($params) {
		if (isset($_COOKIE['token'])) {
			$check = $this->eventDB->get('user_authentication', ['user_id'], ['token' => $_COOKIE['token']]);

			if ($check) {
				return Message::setMessage(1001);
			} else {
				SELF::setCookie('', '');
				return Message::setMessage(1002);
			}
		} else {
			$userLoginStatus = SELF::getuserInfo($params);
			return $userLoginStatus;
		}
	}

	function getuserInfo($params) {
		$userinfo = $this->eventDB->get('user',
			['[><]user_information' => 'user_id'],
			['user.user_id', 'email', 'password', 'type'],
			['AND' => ['email' => $params['email']]]);

		if (!$userinfo) {
			return Message::setMessage(1003);
		} else {

			$currentUser = $userinfo;
			$isMatch = $this->PasswordHasher->CheckPassword($params['password'], $currentUser['password']);

			if ($isMatch) {
				$requestToken = Tool::generateToken();

				$generateUserToken = Tool::generateUserToken([
					'user_id' => $currentUser['user_id'],
					'email' => $currentUser['email'],
					'token' => $requestToken,
					'type' => $currentUser['type'],
				]);

				SELF::createAuthentication($currentUser['user_id'], $generateUserToken, 1);
				SELF::setCookie($generateUserToken, $currentUser['user_id']);

				$response = [
					"user_id" => $currentUser['user_id'],
					"user_token" => $generateUserToken,
				];

				return Message::setmessage(1004) + $response;

			} else {
				return Message::setMessage(1005);
			}
		}

	}

	function createAuthentication($user_id, $token, $staylogged) {

		$auth = $this->eventDB->insert('user_authentication', [
			"token" => $token,
			"user_id" => $user_id,
			"is_stay_login" => $staylogged,
			"status" => 1,
		]);

		return $auth;

	}

	function setCookie($token, $user_id) {
		setcookie('token', $token, time() + (86400 * 7), '/');
		setcookie('user_id', $user_id, time() + (86400 * 7), '/');
	}

	function userLogout(Request $req, Response $res) {

		$this->eventDB->update('user_authentication', ['status' => 0],
			['AND' => ['user_id' => $req->getAttribute('user_id'),
				'token' => $req->getAttribute('token')]]);

		$error = $this->eventDB->error();

		if (is_null($error[2])) {
			SELF::setCookie('', '');
			return $res->withJson(Message::setmessage(1008), Message::setmessage(1008)['status']);
		} else {
			return $res->withJson(Message::setmessage(500, $isValid), Message::setmessage(500, $isValid)['status']);
		}

	}

}