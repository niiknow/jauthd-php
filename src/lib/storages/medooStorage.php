<?php
namespace MyAPI\Storages;

class MedooStorage implements iStorage {
	private static $tables = [];

	/**
	 * constructor, expect
	 */
	function __construct($dbinfo) {
		$this->util = new \MyAPI\Helpers\Util();
		$this->myDB = new \medoo($dbinfo);
	}

	/**
	 * get Tenant table name
	 * @param  $tenantCode tenant code
	 * @return the tenant table name
	 */
	public function getTenantTable($tenantCode) {
		$tenantCode = isset($tenantCode) ? $tenantCode : '';
		$tableName = $tenantCode . '_user';

		if (!isset(self::$tables[$tableName])) {
			// create the table
			self::$tables[$tableName] = time();
			$sql = "CREATE TABLE IF NOT EXISTS " . $tableName
				. "(
  id          char(36) NOT NULL,
  email       varchar(50) NOT NULL,
  emailVerifyAt varchar(20) NULL,
  password    varchar(255) NOT NULL,
  searchName  varchar(255) NOT NULL,
  roles       varchar(1000) NOT NULL,
  profile     varchar(20000) NOT NULL,
  social      varchar(20000) NOT NULL,
  secure      varchar(20000) NOT NULL,
  INDEX (id),
  UNIQUE (email)
)";
			$this->myDB->query($sql);
		}

		return $tableName;
	}

	/**
	 * getUser by id
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @return the user object
	 */
	public function getUser($tenantCode, $id) {
		$rst = $this->myDB->get($this->getTenantTable($tenantCode), '*');
		return $rst;
	}

	/**
	 * determine of user exists
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @return true if exists
	 */
	public function exists($tenantCode, $id) {
		$exists = $this->myDB->has($this->getTenantTable($tenantCode), ['id' => $id]);
		return $exists;
	}

	/**
	 * insert user
	 * @param  $tenantCode tenant code
	 * @param  $user the user object
	 * @return user id or null
	 */
	public function insertUser($tenantCode, $user) {
		// make email
		// echo json_encode($user);
		$user['email'] = $this->util->strtolower($user['email']);
		$user['id'] = $this->util->oid($user['email']);
		$user['password'] = $this->util->hashPassword($user['password']);
		$profile = json_decode(isset($user['profile']) ? $user['profile'] : '{}', true);
		$profile['lastName'] = isset($profile['lastName']) ? $profile['lastName'] : '';
		$addUser = $this->myDB->insert($this->getTenantTable($tenantCode),
			[
				'id' => $user['id'],
				'email' => $user['email'],
				'password' => $user['password'],
				'roles' => '',
				'searchName' => isset($user['searchName']) ? $profile['searchName'] : $profile['lastName'],
				'profile' => isset($user['profile']) ? $user['profile'] : '{}',
				'social' => isset($user['social']) ? $user['social'] : '{}',
				'secure' => isset($user['secure']) ? $user['secure'] : '{}',
			]);
		return $user['id'];
	}

	/**
	 * delete user
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @return userid for success
	 */
	public function deleteUser($tenantCode, $id) {
		// do nothing for now
		return $this;
	}

	/**
	 * reset password
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $baseUrl the site base url
	 * @param  $forgotPasswordToken email verification token
	 */
	public function forgotPassword($tenantCode, $id, $baseUrl, $forgotPasswordToken) {
		// do nothing
		return $this;
	}

	/**
	 * update user to record of email verification
	 * @param  $tenantCode tenant code
	 * @param  $id
	 * @param  $baseUrl the site base url
	 * @param  $emailVerifyToken email verification token
	 * @return the storage object
	 */
	public function updateEmailVerification($tenantCode, $id, $baseUrl, $emailVerifyToken) {
		// do nothing
		return $this;
	}

	/**
	 * update user profile
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $profile the profile json string
	 * @return the storage object
	 */
	public function updateProfile($tenantCode, $id, $profile) {
		// update profile
		$this->myDB->update($this->getTenantTable($tenantCode), ['profile' => $profile],
			['AND' => ['id' => $id]]);
		return $this;
	}

	/**
	 * update user password
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $password the user password
	 * @param  $baseUrl the site base url
	 * @return the storage object
	 */
	public function updatePassword($tenantCode, $id, $password, $baseUrl) {
		// update password
		$this->myDB->update($this->getTenantTable($tenantCode), ['password' => $password],
			['AND' => ['id' => $id]]);
		return $this;
	}

	/**
	 * update user social
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $social the social json string
	 * @return the result user object
	 * @return the storage object
	 */
	public function updateSocial($tenantCode, $id, $social) {
		// update social
		$this->myDB->update($this->getTenantTable($tenantCode), ['social' => $social],
			['AND' => ['id' => $id]]);
		return $this;
	}

	/**
	 * update user security QA
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $secure the secure json string
	 * @return the result user object
	 * @return the storage object
	 */
	public function updateSecure($tenantCode, $id, $secure) {
		// update secure
		$this->myDB->update($this->getTenantTable($tenantCode), ['secure' => $secure],
			['AND' => ['id' => $id]]);
	}

	/**
	 * update user roles
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $roles the roles csv string
	 * @return the result user object
	 * @return the storage object
	 */
	public function updateRoles($tenantCode, $id, $roles) {
		// update roles
		$this->myDB->update($this->getTenantTable($tenantCode), ['roles' => $roles],
			['AND' => ['id' => $id]]);
	}

	/**
	 * update search name
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $searchName
	 * @return the storage object
	 */
	public function updateSearchName($tenantCode, $id, $searchName) {
		// update search Name
		$this->myDB->update($this->getTenantTable($tenantCode), ['searchName' => $searchName],
			['AND' => ['id' => $id]]);
	}

	/**
	 * search Users by searchName
	 * - this app was designed for authentication only and defer user management to the UI
	 * - this method allow for UI to easily search and filter data from any storage
	 * -
	 * - searchName is use to allow for a single column index and filtering, it's not the best
	 * - solution but it is better than nothing
	 *
	 * @param  $tenantCode tenant code
	 * @param  $searchName
	 * @param  $pageSize how may to get
	 * @param  $offset how many to skip
	 * @return the storage object
	 */
	public function searchUsers($tenantCode, $searchName, $pageSize, $offset = 0) {
		$this->myDB->select($this->getTenantTable($tenantCode),
			['id', 'email', 'password', 'roles', 'searchName', 'profile', 'social'],
			['searchName[~]' => $searchName],
			["ORDER" => "searchName ASC", "LIMIT" => [$pageSize, $offset]]
		);
	}
}