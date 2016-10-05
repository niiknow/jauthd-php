<?php
namespace MyAPI\Lib;

use Ramsey\Uuid\Uuid;

/**
 * Util
 */
class Util {
	/**
	 * hash a password
	 * @param  $password unencrypted password
	 * @return the hashed result
	 */
	public function hashPassword($password) {
		return password_hash($password, PASSWORD_DEFAULT); // $this->PasswordHasher->HashPassword($password);
	}

	/*
		   * compare regular password to existing/hashed password
		   * @param  $password
		   * @param  $hashedPassword
		   * @return true if valid
	*/
	public function comparePassword($password, $hashedPassword) {
		return password_verify($password, $hashedPassword); //$this->PasswordHasher->CheckPassword($password, $hashedPassword);
	}

	/**
	 * convert string to lower case
	 * @param  $str the string
	 * @return the lowered string
	 */
	public function strtolower($str) {
		return mb_strtolower($str);
	}

	/**
	 * get the object id
	 * @param  $email email to get the user id
	 * @return the id
	 */
	public function oid($email) {
		$emailslug = \__::slug($email);
		$uuid5 = Uuid::uuid5(Uuid::NAMESPACE_OID, $emailslug);
		return $uuid5->toString();
	}

	/**
	 * current date in ISO8601 format
	 * @return current date
	 */
	public function now() {
		$now = new \DateTime();
		$nowFormatted = $now->format(\DateTime::ATOM); // ISO 8601
		return $nowFormatted;
	}
}