<?php
namespace MyAPI\Controllers;

use MyAPI\Lib\Util as Util;

/**
 * Base Controller class, all other controllers should extend this class.
 */
class Controller {
	protected $request, $response, $args, $container;
	public function __construct($request, $response, $args, $container) {
		// create storage base on configuration
		$db_storage = getenv('DB_STORAGE') ? getenv('DB_STORAGE') : '\MyAPI\Lib\Storages\MedooStorage';
		$dbinfo = $container->get('settings')['dbinfo'];
		$this->storage = new $db_storage($dbinfo);
		$this->request = $request;
		$this->response = $response;
		$this->args = $args;
		$this->container = $container;
		$this->util = new Util();
		$this->authHelper = new \MyAPI\Lib\AuthHelper();
		$app->env['MYAPP_HOSTNAME'] = $request->getUri()->getHost();
	}
	public function __get($property) {
		if ($this->container->{$property}) {
			return $this->container->{$property};
		}
	}
	public function render($name, array $args = []) {
		return $this->container->view->render($this->response, $name . '.twig', $args);
	}
	public function redirect($path = null) {
		$path = $path != null ? $path : 'home';
		return $this->response->withRedirect($this->router()->pathFor($path));
	}
	public function validator() {
		return new \GUMP();
	}
	public function router() {
		return $this->container->router;
	}
	public function param($param) {
		return $this->request->getParam($param);
	}
	public function queryParam($param) {
		return $this->request->getQueryParam($param);
	}
	public function mail() {
		return $this->container->mail;
	}
	public function apiSuccess($data) {
		return $this->response->withJson(['data' => $data, 'status' => 200], 200);
	}
	public function apiError($code, $mes = [], $lang = 'en') {
		$messages = json_decode(file_get_contents(INC_ROOT . "/src/lib/language/message.json"), true);

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

		return $this->response->withJson($response, $response['status']);
	}
}