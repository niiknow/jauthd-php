<?php
namespace MyAPI\Storage;

// Declare the interface 'iTemplate'
interface iStorage {
	/**
	 * get Tenant table name
	 * @param  $tenantCode tenant code
	 * @return the tenant table name
	 */
	public function getTenantTable($tenantCode);

	/**
	 * getUser by id
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @return the user object
	 */
	public function getUser($tenantCode, $id);

	/**
	 * determine of user exists
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @return true if exists
	 */
	public function exists($tenantCode, $id);

	/**
	 * insert user
	 * - allow storage to handle email verification event
	 * @param  $tenantCode tenant code
	 * @param  $user the user object
	 * @param  $baseUrl the site base url
	 * @param  $emailVerifyToken email verification token
	 * @return userid for success
	 */
	public function insertUser($tenantCode, $user, $baseUrl, $emailVerifyToken);

	/**
	 * delete user
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @return userid for success
	 */
	public function deleteUser($tenantCode, $id);

	/**
	 * reset password
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $baseUrl the site base url
	 * @param  $forgotPasswordToken email verification token
	 */
	public function forgotPassword($tenantCode, $id, $baseUrl, $forgotPasswordToken);

	/**
	 * update user to record of email verification
	 * @param  $tenantCode tenant code
	 * @param  $id
	 * @param  $baseUrl the site base url
	 * @param  $emailVerifyToken email verification token
	 * @return the storage object
	 */
	public function updateEmailVerification($tenantCode, $id, $baseUrl, $emailVerifyToken);

	/**
	 * update user profile
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $profile the profile json string
	 * @return the storage object
	 */
	public function updateProfile($tenantCode, $id, $profile);

	/**
	 * update user password
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $password the user password
	 * @param  $baseUrl the site base url
	 * @return the storage object
	 */
	public function updatePassword($tenantCode, $id, $password, $baseUrl);

	/**
	 * update user social
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $social the social json string
	 * @return the storage object
	 */
	public function updateSocial($tenantCode, $id, $social);

	/**
	 * update user secure
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $secure the secure json string
	 * @return the storage object
	 */
	public function updateSecure($tenantCode, $id, $secure);

	/**
	 * update user roles
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $roles the roles csv string
	 * @return the storage object
	 */
	public function updateRoles($tenantCode, $id, $roles);

	/**
	 * update search name
	 * @param  $tenantCode tenant code
	 * @param  $id the user id
	 * @param  $searchName
	 * @return the storage object
	 */
	public function updateSearchName($tenantCode, $id, $searchName);

	/**
	 * search Users
	 * @param  $tenantCode tenant code
	 * @param  $searchName
	 * @param  $offset how many to skip
	 * @param  $pageSize how may to get
	 * @return the storage object
	 */
	public function searchUsers($tenantCode, $searchName, $offset, $pageSize);
}