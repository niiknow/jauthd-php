<?php
namespace MyAPI\Controllers;

use MyAPI\Lib\Util as Util;

/**
 * Base Controller class, all other controllers should extend this class.
 */
class Controller {
	protected $request, $response, $args, $container;
	public function __construct($request, $response, $args, $container) {
		$this->request = $request;
		$this->response = $response;
		$this->args = $args;
		$this->container = $container;
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
		$messages = json_decode(file_get_contents(INC_ROOT . "/src/data/language/message.json"), true);

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

	private function getTenantByHost() {
		$uri = $this->request->getUri();
		$host = strtolower($uri->getHost());
		$host = str_replace('www.', '', $host);
		$hosts = explode('.', $host);
		if (count($hosts) > 1) {
			return $hosts[0];
		}
		return '';
	}
	public function tenantCode() {
		// use configuration to get single tenant setup
		$tenantCode = getenv('APP_TENANT');
		if (!isset($tenantCode)) {
			$tenantCode = $this->queryParam('tenantCode');
			if (!isset($tenantCode)) {
				$tenantCode = $this->getTenantByHost();
			}
		}

		// replace all non-alphanumeric to be underscore
		$tenant = preg_replace("[^a-z0-9_]", "_", $tenantCode);

		// underscore are later handled by individual storage
		return $tenant;
	}
	public function getIPs() {
		$ips = [];
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
			$ip = $this->request->getHeader($key);
			if (isset($ip)) {
				$ips[$key] = $ip;
			}
		}
	}
}