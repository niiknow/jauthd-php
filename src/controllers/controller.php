<?php
namespace MyAPI\Controllers;

use MyAPI\Lib\Util as Util;

/**
 * Base Controller class, all other controllers should extend this class.
 */
class Controller {
	protected $request, $response, $args, $container;
	
    /**
     * base controller
     * @param $request   the request object
     * @param $response  the response object
     * @param $args      the route args
     * @param $container the container
     */
	public function __construct($request, $response, $args, $container) {
		$this->request = $request;
		$this->response = $response;
		$this->args = $args;
		$this->container = $container;
	}

	/**
	 * Allow for dependency injection defined in container.
	 * @param  $property property name
	 * @return the object on the $container if found
	 */
	public function __get($property) {
		if ($this->container->{$property}) {
			return $this->container->{$property};
		}
	}

	/**
	 * Shortcut method for rendering a view.
	 * @param  string $name view name
	 * @param  array  $args view params
	 */
	public function render($name, array $args = []) {
		return $this->container->view->render($this->response, $name . '.twig', $args);
	}

	/**
	 * shortcut method to redirect by name
	 * @param  string $path the path
	 */
	public function redirect($path = null) {
		$path = $path != null ? $path : 'home';
		return $this->response->withRedirect($this->router->pathFor($path));
	}

	/**
	 * get a body parameter
	 * @param  string $param name of the parameter
	 * @return the data
	 */
	public function param($param) {
		return $this->request->getParam($param);
	}

	/**
	 * get a query parameter
	 * @param  string $param name of the parameter
	 * @return the data
	 */
	public function queryParam($param) {
		return $this->request->getQueryParam($param);
	}

	/**
	 * return an API success message
	 * @param  $data the data to return
	 */
	public function apiSuccess($data) {
		return $this->response->withJson(['data' => $data, 'status' => 200], 200);
	}

	/**
	 * return an API error message
	 * @param  number $code the status code
	 * @param  array  $mes  error message array
	 * @param  string $lang the lang
	 */
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

    /**
     * get tenant with current host with pattern
     * lowercase of: www.(tenantCode).blah.blah.com
     * @return string parsed tenant
     */
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

	/**
	 * get the tenant code
	 * use APP_TENANT environment variable for single tenant
	 * fallback to querystring of tenantCode
	 * fallback to hostname
	 * @return string  the sanitized tenant code
	 */
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

	/**
	 * Get all  possible client IPs
	 * @return array all client IP data found in header
	 */
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