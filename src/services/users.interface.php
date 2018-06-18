<?php
/**
* This interface provide all accessible methods on users module
*
* @author Didelot Guillaume <gdidelot@live.fr>
* @version 1.0
* @package Core\CoreComponents
* @subpackage UsersManager
*/
namespace Core\CoreComponents\UsersManager;

/**
* This interface provide all accessible methods on users module
*
* @method ServiceResponse authenticate($mail, $password)
* @method ServiceResponse getUsers()
* @method ServiceResponse updateUserPassword($id, $newPassword)
* @method ServiceResponse updateUser($id, $firstname, $lastname, $email, $phonenumber, $webaddress, $profile, $type, $addressid, $addressnumber, $addressstreet, $addresszipcode, $addresscity, $points, $lastquizzid)
* @method ServiceResponse getUsers()
* @method ServiceResponse getAdministrators()
* @method ServiceResponse addUser($firstname, $lastname, $email, $phonenumber, $webaddress, $profile, $type)
* @method ServiceResponse sendContact($firstname, $lastname, $email, $object, $message)
* @method ServiceResponse validUserEmail($password)
* @method ServiceResponse getProfiles()
* @method ServiceResponse deleteUser($id)
* @method ServiceResponse resetUserPassword($userid)
* @method ServiceResponse getUserByEmail($email)
* @method ServiceResponse getUser($id)
* @method ServiceResponse generateNewPassword($mail)
* @method ServiceResponse updateUserPosition($userId, $latitude, $longitude)
*/
interface IUsers
{
    /**
    * Authenticate an user
    *
    * @param string $mail The user's mail
    * @param string $password The user's password
    *
    * @return This response contains the use object
    */
    public function authenticate($mail, $password);

    /**
    * Update users's informations
    *
    * @param integer $id The unique identifier of a user
    * @param string $newPassword The user's new password
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the updated user
    */
    public function updateUserPassword($id, $newPassword);
    
    /**
    * Update an user
    *
    * @param integer $id The unique identifier of a user
    * @param string $firstname The user's firstname
    * @param string $lastname The user's lastname
    * @param string $email The user's email
    * @param string $phonenumber The user's phonenumber
    * @param string $webaddress The user's web address
    * @param integer $profile The user's profile
    * @param integer $type The user's type
    * @param integer $addressid The user's address identifier
    * @param string $addressnumber The user's address number
    * @param string $addressstreet The user's address street
    * @param string $addresszipcode The user's address zip code
    * @param string $addresscity The user's address city
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the updated user
    */
    public function updateUser($id, $firstname, $lastname, $email, $city, $pseudo, $latitude, $longitude, $profile, $password);
    
    /**
    * Get all users
    *
    * @return This response contains all users
    */
    public function getUsers();
    
    /**
    * Get all administrators
    *
    * @return This response contains all administrators
    */
    public function getAdministrators();
    
    /**
    * Add an user
    *
    * @param string $firstname The user's firstname
    * @param string $lastname The user's lastname
    * @param string $email The user's email
    * @param string $city The user's city
    * @param string $zipcode The user's zipcode
    * @param string $pseudo The user's pseudo
    * @param string $latitude The user's latitude
    * @param string $longitude The user's longitude
    * @param integer $profile The user's profile
    * @param string $password The user's password
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the added user
    */
    public function addUser($firstname, $lastname, $email, $city, $zipcode, $pseudo, $latitude, $longitude, $profile, $password);
    
    /**
    * Send a contact mail
    *
    * @param string $name The user's lastname
    * @param string $email The user's email
    * @param string $object The user's object
    * @param string $message The user's message
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the added user
    */
    public function sendContact($name, $email, $object, $message);
    
    /**
    * valid an user
    *
    * @param string $password The user's password
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the validate user
    */
    public function validUserEmail($password);
    
    /**
    * Get all profiles
    *
    * @return This response contains all profiles
    */
    public function getProfiles();
    
    /**
    * Delete an user
    *
    * @param integer $id The user's identifier
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the added user
    */
    public function deleteUser($id);
    
    /**
    * Reset user password
    *
    * @param integer $userid The user unique identifier
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the user object or null
    */
    public function resetUserPassword($userid);
    
    /**
    * Get an user by his email
    *
    * @param string $email The user's email address
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
    public function getUserByEmail($email);
    
    /**
    * Get an user
    *
    * @param integer $id The user's identifier
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
    public function getUser($id);
    
    /**
    * Generate a new password for an user
    *
    * @param string $mail The user's mail
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
    public function generateNewPassword($mail);
	
	/**
    * Set the user position
    *
    * @param integer $userId The user identifier
    * @param float $latitude The latitude
    * @param float $longitude The longitude
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
	public function updateUserPosition($userId, $latitude, $longitude);
	
	/**
    * Logout an user
    *
    * @param integer $userId The user's identifier
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
    public function logout($userId);
	
	/**
    * Get user states
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
    public function getUserStates();
	
	/**
    * Send Validation Mail
    *
	* @param integer $userId The user identifier
	*
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
    public function sendValidationMail($userId);
	
	/**
    * Unban an user
    *
	* @param integer $userId The user identifier
	*
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
    public function unBanUser($userId);
}

?>