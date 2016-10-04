<?php

namespace MyAPI\Controllers;

class UserController extends Controller {
	public function postChangePassword() {

	}

	public function postUpdateProfile() {

	}

	public function postAddRole() {

	}

	public function PostDeleteRole() {

	}
}

$app->group('/user', function () {
	$this->route(['POST'], '/profile/update', \MyAPI\Controllers\UserController::class, 'UpdateProfile')->setName('user.profile.update');
	$this->route(['POST'], '/changepassword', \MyAPI\Controllers\UserController::class, 'ChangePassword')->setName('user.password.change');
	$this->route(['POST'], '/role/add', \MyAPI\Controllers\UserController::class, 'AddRole')->setName('user.role.add');
	$this->route(['POST'], '/role/delete', \MyAPI\Controllers\UserController::class, 'DeleteRole')->setName('user.role.delete');
});