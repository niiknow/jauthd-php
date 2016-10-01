<?php
namespace App\Stroage;

class AzureTableStorage implements iStorage
{
    /**
     * getUser by id
     * @param  $id the user id
     * @return the user object
     */
    public function getUser($id);

    /**
     * determine of user exists
     * @param  $id the user id
     * @return true if exists
     */
    public function exists($id);

    /**
     * insert or update user
     * @param  $user the user object
     * @return the result user object
     */
    public function insertOrUpdateUser($user);
}