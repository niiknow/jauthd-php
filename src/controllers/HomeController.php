<?php
namespace JAuth\Controllers;
/**
 * HomeController handles our '/' route.
 */
class HomeController extends Controller {
	public function get() {
		return $this->render('home');
	}
}