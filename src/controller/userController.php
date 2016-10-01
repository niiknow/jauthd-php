<?php

namespace App\Controller;
use Hautelook\Phpass\PasswordHash;
use Helper\Message as Message;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class User {

	protected $eventDB;
	function __construct($app) {
		$this->eventDB = $app->eventDB;
		$this->PasswordHasher = new PasswordHash(12, false);
		$this->logger = $app->logger;
	}

	function addUser(Request $req, Response $res) {
		$params = $req->getParsedBody();

		$isValid = \Gump::is_valid($params, [
			"profile" => "required|valid_json_string",
			"email" => "required|valid_email",
			"password" => "required|max_len,100|min_len,8",
			"type" => "required",
			"lang" => "required",
		]);

		if ($isValid === true) {

			$profile = json_decode($params['profile'], true);
			$isEmailExist = $this->eventDB->has('user', ['email' => $params['email']]);

			if (!$isEmailExist) {
				$addUser = $this->eventDB->insert('user',
					[
						'email' => $params['email'],
						'password' => $this->PasswordHasher->HashPassword($params['password']),
						'type' => $params['type'],
					]);

				$addUserInfo = $this->eventDB->insert('user_information', [
					'user_id' => $addUser,
					'profile' => $params['profile'],
					'language' => $params['lang'],
				]);

				$this->logger->addInfo('new User', ['user_id' => $addUser]);

			} else {
				return $res->withJson(Message::setMessage(1006), Message::setMessage(1006)['status']);
			}

		} else {
			return $res->withJson(Message::setMessage(500, $isValid), Message::setMessage(500, $isValid)['status']);
		}

	}

}