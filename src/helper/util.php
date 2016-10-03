<?php

require 'bottomline/bottomline.php';

namespace JAuth\Helper;

use JAuth\Storage

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

/**
 * Util
 */
class Util {
	private $store;
	public config;
	/**
	 * Utility functions
	 */
	function __construct() {
		// create storage base on configuration
    $db_storage = getenv('DB_STORAGE') or 'MedooStorage';
    $dbinfo = $app->getContainer()->get('settings')['dbinfo'];
    $this->$store = new $db_storage($dbinfo);
    $this->PasswordHasher = new PasswordHash(12, false);
	}

  /**
   * hash a password
   * @param  $password unencrypted password
   * @return the hashed result
   */
  public function hashPassword($password)
  {
    return $this->PasswordHasher->HashPassword($password),
  }

  /*
   * compare regular password to existing/hashed password
   * @param  $password       
   * @param  $hashedPassword 
   * @return true if valid
   */
  public function comparePassword($password, $hashedPassword) 
  {
    return $this->PasswordHasher->CheckPassword($password, $hashedPassword);
  }
  
	/**
	 * Get backend storage
	 * @return object backend storage
	 */
	public function getStorage() {
		return $this->$store;
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
    $emailslug = __::slug($email);
    $uuid5 = Uuid::uuid5(Uuid::NAMESPACE_OID, $emailslug);
  }
}