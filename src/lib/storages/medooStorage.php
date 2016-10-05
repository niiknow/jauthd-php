<?php
namespace MyAPI\Lib\Storages;

class MedooStorage implements iStorage {
	private static $tables = [];

	/**
	 * constructor, expect
	 */
	function __construct($dbinfo, $container) {
		$this->util = new \MyAPI\Lib\Util();
		$this->myDB = new \medoo($dbinfo);
		$this->container = $container;
	}

	/**
	 * get Tenant table name
	 * @param  $tenantCode tenant code
	 * @return the tenant table name
	 */
	public function getTenantTable($tenantCode) {
		$tenantCode = isset($tenantCode) ? $tenantCode : '';
		$tableName = $tenantCode . '_user';
		$logDir = dirname(INC_ROOT . '/src/' . $this->container->settings['logger']['path']);
		$file = $logDir . '/table_' . $tableName . '.log';

		if (!file_exists($file)) {
			// create the table
			$myfile = fopen($file, "w");
			fwrite($myfile, $this->util->now());
			fclose($myfile);

			$sql = "  CREATE TABLE " . $tableName . "(
    userid         char(36) NOT NULL,
    email          varchar(250) NOT NULL,
    passwd         varchar(250) NOT NULL,
    searchName     varchar(500) NOT NULL,
    roles          varchar(2000) NOT NULL,
    userprofile    varchar(20000) NOT NULL,
    social         varchar(20000) NOT NULL,
    secure         varchar(20000) NOT NULL,
    createAt       varchar(30) NOT NULL,
    emailConfirmAt varchar(30),
    updateAt       varchar(30),
    loginAt        varchar(30),
    loginLog       text,
    UNIQUE (userid)
  )";
			$this->myDB->query($sql);
			echo $sql;
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
		$exists = $this->myDB->has($this->getTenantTable($tenantCode), ['userid' => $id]);
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
		$user['userid'] = $this->util->oid($user['email']);
		$user['passwd'] = $this->util->hashPassword($user['password']);
		$profile = json_decode(isset($user['userprofile']) ? $user['userprofile'] : '{}', true);
		$profile['lastName'] = isset($profile['lastName']) ? $profile['lastName'] : '';
		$user['userprofile'] = isset($user['userprofile']) ? $user['userprofile'] : '{}';
		$user['searchName'] = isset($user['searchName']) ? $profile['searchName'] : $profile['lastName'];
		$user['social'] = isset($user['social']) ? $user['social'] : '{}';
		$user['secure'] = isset($user['secure']) ? $user['secure'] : '{}';
		$addUser = $this->myDB->insert($this->getTenantTable($tenantCode),
			[
				'userid' => $user['userid'],
				'email' => $user['email'],
				'passwd' => $user['passwd'],
				'searchName' => !isset($user['searchName']) ? '' : $user['searchName'],
				'roles' => '',
				'userprofile' => $user['userprofile'],
				'social' => $user['social'],
				'secure' => $user['secure'],
				'createAt' => $this->util->now(),
			]);
		// echo $user['userid'];
		// var_dump($this->myDB->last_query());
		// var_dump($addUser);
		return $user;
	}

	/**
	 * delete user
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @return userid for success
	 */
	public function deleteUser($tenantCode, $id) {
		$this->myDB->delete($this->getTenantTable($tenantCode),
			['AND' => ['userid' => $id]]);
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
		// do nothing, maybe trigger email or api call?
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
		$this->myDB->update($this->getTenantTable($tenantCode), ['emailConfirmAt' => $this->util->now()],
			['AND' => ['userid' => $id]]);
		return $this;
	}

	/**
	 * update last login
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $loginLog data such as refererUrl or ipAddress
	 * @return the storage object
	 */
	public function updateLogin($tenantCode, $id, $loginLog) {
		$this->myDB->update($this->getTenantTable($tenantCode), ['loginAt' => $this->util->now(), 'loginLog' => $loginLog],
			['AND' => ['userid' => $id]]);
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
		$this->myDB->update($this->getTenantTable($tenantCode), ['userprofile' => $profile, 'updateAt' => $this->util->now()],
			['AND' => ['userid' => $id]]);
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
		$this->myDB->update($this->getTenantTable($tenantCode), ['passwd' => $password, 'updateAt' => $this->util->now()],
			['AND' => ['userid' => $id]]);
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
		$this->myDB->update($this->getTenantTable($tenantCode), ['social' => $social, 'updateAt' => $this->util->now()],
			['AND' => ['userid' => $id]]);
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
		$this->myDB->update($this->getTenantTable($tenantCode), ['secure' => $secure, 'updateAt' => $this->util->now()],
			['AND' => ['userid' => $id]]);
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
		$this->myDB->update($this->getTenantTable($tenantCode), ['roles' => $roles, 'updateAt' => $this->util->now()],
			['AND' => ['userid' => $id]]);
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
		$this->myDB->update($this->getTenantTable($tenantCode), ['searchName' => $searchName, 'updateAt' => $this->util->now()],
			['AND' => ['userid' => $id]]);
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
			['userid', 'email', 'passwd', 'roles', 'searchName', 'userprofile', 'social', 'updateAt', 'createAt', 'loginAt'],
			['searchName[~]' => $searchName],
			["ORDER" => "searchName ASC", "LIMIT" => [$pageSize, $offset]]
		);
	}
}