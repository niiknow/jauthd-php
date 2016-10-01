<?php

namespace Helper;

class Message {

	public function setMessage($code, $mes = []) {

		$headers = apache_request_headers();

		if (isset($headers['lang']) and $headers['lang'] != "") {
			$lang = $headers["lang"];
		} else {
			$lang = 'en';
		}

		$messages = json_decode(file_get_contents(dirname(__FILE__) . "/language/messages.json"), true);

		if (empty($mes)) {

			$response = [
				'code' => $code,
				'message' => $messages[$code][$lang . '_message'],
				'status' => $messages[$code]['status'],
			];

		} else {

			$response = [
				'code' => $code,
				'message' => (is_array($mes) ? array_map('strip_tags', $mes) : $mes),
				'status' => $messages[$code]['status'],
			];
		}

		return $response;
	}

}