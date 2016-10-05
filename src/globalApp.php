<?php
namespace MyAPI;

/**
 * GlobalApp is used to hook everything together.
 * This is like global.asax in .NET
 */
class GlobalApp extends \Slim\App {
	/**
	 * Customized route mapping.
	 */
	public function route(array $methods, $uri, $controller, $func = null) {
		if ($func) {
			return $this->map($methods, $uri, function ($request, $response, $args) use ($controller, $func) {
				$callable = new $controller($request, $response, $args, $this);
				return call_user_func_array([$callable, $request->getMethod() . ucfirst($func)], $args);
			});
		}
		return $this->map($methods, $uri, function ($request, $response, $args) use ($controller) {
			$callable = new $controller($request, $response, $args, $this);
			return call_user_func_array([$callable, $request->getMethod()], $args);
		});
	}
}

?>