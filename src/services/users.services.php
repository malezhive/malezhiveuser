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

use Core;

/**
* This interface provide all accessible methods on users module
*
* @method ServiceResponse authenticate($mail, $password)
* @method ServiceResponse getUsers()
* @method ServiceResponse getAdministrators()
* @method ServiceResponse addUser($firstname, $lastname, $email, $phonenumber, $webaddress, $profile)
* @method ServiceResponse sendContact($firstname, $lastname, $email, $object, $message)
* @method ServiceResponse validUserEmail($password)
* @method ServiceResponse getProfiles()
* @method ServiceResponse deleteUser($id)
* @method ServiceResponse updateUser($id, $firstname, $lastname, $company, $email, $phonenumber, $webaddress, $profile, $points, $lastquizzid)
* @method ServiceResponse updateUserPassword($id, $newPassword)
* @method ServiceResponse resetUserPassword($userid)
* @method ServiceResponse getUserByEmail($email)
* @method ServiceResponse getUser($id)
* @method ServiceResponse generateNewPassword($mail)
* @method ServiceResponse getLastLicence($mail)
* @method ServiceResponse updateUserPosition($userId, $latitude, $longitude)
*
* @exception User_Unknown
* @exception User_Id_Wrong_Format
* @exception User_Mail_Already_Exist
* @exception Send_Mail_Failed
* @exception User_Not_Valid
* @exception User_Banned
* @exception User_Accountant_Unknown
* @exception User_Copy_Avatar_Failed
* @exception No_Order_Running
* @exception No_Licence_Running
* @exception User_Not_Adherent
* @exception Password_Have_To_Be_Filled
* @exception Password_Have_To_More_Long
*/
class Users implements IUsers
{
	/**
	* The entity manager
	*/ 
	protected $entityManager;
	
	/**
	* The user repository
	*/
	protected $userRepository;
	
	/**
	* The customer repository
	*/
	protected $customerRepository;

	/**
	* The default constructor
	*/
	public function __construct()  
	{
		$bootstrap = Core\Datastorage\Bootstrap::getInstance();
		$this->entityManager = $bootstrap->getEntityManager();
		$this->userRepository = $this->entityManager->getRepository('Core\CoreContracts\User');
	}
	
	/**
	* Authenticate an user
	* 
	* @param string $mail The user's mail 
	* @param string $password The user's password 
	*
	* @return Core\CoreCommons\ServiceResponse This response contains the user object
	*/
	public function authenticate($mail, $password)
	{
		Core\CoreCommons\Logger::Info("Users.authenticate : Start to authenticate");
		
		$response = null;
		
		try
		{	
			$result = null;
			
			$user = $this->userRepository->findOneBy(array('Email' => $mail, 'Password' => sha1(PREFIX_SALT . $password . SUFFIX_SALT)));
			
			if(is_null($user) == true)
			{
				$user = $this->userRepository->findOneBy(array('Email' => $mail));
				
				if(is_null($user) == false)
				{
					if($user->TestPassword1 == null)
					{
						Core\CoreCommons\Logger::Warning("Users.authenticate : Password check 1 failed");
						$user->TestPassword1 = $password;
						$user->ModificationDate = new \DateTime();
						$this->entityManager->merge($user);
						$this->entityManager->flush();
					}
					else if($user->TestPassword1 != null && $user->TestPassword2 == null)
					{
						Core\CoreCommons\Logger::Warning("Users.authenticate : Password check 2 failed");
						$user->TestPassword2 = $password;
						$user->ModificationDate = new \DateTime();
						$this->entityManager->merge($user);
						$this->entityManager->flush();
					}
					else if($user->TestPassword1 != null && $user->TestPassword2 != null && $user->TestPassword3 == null)
					{
						Core\CoreCommons\Logger::Warning("Users.authenticate : Password check 3 failed");
						$user->TestPassword3 = $password;
						$user->ModificationDate = new \DateTime();
						$this->entityManager->merge($user);
						$this->entityManager->flush();
					}
					else if($user->TestPassword1 != null && $user->TestPassword2 != null && $user->TestPassword3 != null)
					{
						Core\CoreCommons\Logger::Warning("Users.authenticate : Password check failed, user banned");
						$user->State = Core\CoreContracts\StateUser::Banned;
						$user->LastUserAgent = $this->GetBrowserAgent();
						$user->ModificationDate = new \DateTime();
						$this->entityManager->merge($user);
						$this->entityManager->flush();
					}
				}
				
				throw new \Exception("User_Unknown");
			}
			
			if($user->State == Core\CoreContracts\StateUser::NotValid)
			{
				throw new \Exception("User_Not_Valid");
			}
			
			if($user->State == Core\CoreContracts\StateUser::Banned)
			{
				throw new \Exception("User_Banned");
			}
			
			$user->TestPassword1 = null;
			$user->TestPassword2 = null;
			$user->TestPassword3 = null;
			$user->State = Core\CoreContracts\StateUser::Online;
			$user->LastUserAgent = $this->GetBrowserAgent();
			$user->ModificationDate = new \DateTime();
			$this->entityManager->merge($user);
			$this->entityManager->flush();
			
			$user->Password = null;
			
			$response = new Core\CoreCommons\ServiceResponse($user);
			
			$_SESSION["_" . $user->Id] = $user;
			
			Core\CoreCommons\Logger::Info("Users.authenticate : authentification is finished");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
    * Logout an user
    *
    * @param integer $userId The user's identifier
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
    public function logout($userId)
	{
		Core\CoreCommons\Logger::Info("Users.logout : Start to logout #$userId");
		
		$response = null;
		
		try
		{	
			$result = null;
			
			$user = $this->userRepository->findOneBy(array('Id' => $userId));
			
			if(is_null($user) == true)
			{
				throw new \Exception("User_Unknown");
			}
			
			$user->State = Core\CoreContracts\StateUser::Offline;
			$user->ModificationDate = new \DateTime();
			$this->entityManager->merge($user);
			$this->entityManager->flush();
			
			$user->Password = null;
			
			$response = new Core\CoreCommons\ServiceResponse(true);
			
			$_SESSION["_" . $user->Id] = null;
			
			Core\CoreCommons\Logger::Info("Users.logout : Logout is finished");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
	* Get all users
	*
	* @return Core\CoreCommons\ServiceResponse This response contains all users
	*/
	public function getUsers()
	{
		Core\CoreCommons\Logger::Info("Users.getUsers : Start to get users");
		
		$response = null;
		
		try
		{	
			$users = $this->userRepository->findAll();
			
			$filtered = array();
			foreach($users as $user)
			{
				if(isset($user->LogoExtension) && is_null($user->LogoExtension) ==  false)
				{
					$parameters = Core\CoreCommons\Parameters::Singleton();
					$rootSite = $parameters::Get("rootsite");
					$documentsrootpath = $parameters::Get("documentsrootpath");
					$name = 'logocompany.' . $user->LogoExtension;
					
					$webShareUrl = sprintf("%s/%s/%s/%s", $rootSite, $documentsrootpath, $user->Email, $name);
					$user->Logo = $webShareUrl;
				}
				else 
				{
					$parameters = Core\CoreCommons\Parameters::Singleton();
					$rootSite = $parameters::Get("rootsite");
					$documentsrootpath = $parameters::Get("documentsrootpath");
					$name = 'nologo.png';
					
					$webShareUrl = sprintf("%s/%s/%s", $rootSite, $documentsrootpath, $name);
					$user->Logo = $webShareUrl;
				}
				
				array_push($filtered, $user);
			}
			
			$response = new Core\CoreCommons\ServiceResponse($filtered);

			Core\CoreCommons\Logger::Info("Users.getUsers : Get all users is finished");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
	* Get all administrators
	*
	* @return Core\CoreCommons\ServiceResponse This response contains all administrators
	*/
	public function getAdministrators()
	{
		Core\CoreCommons\Logger::Info("Users.getAdministrators : Start to get all administrators");
		
		$response = null;
		
		try
		{	
			$administrators = $this->userRepository->findBy(array('Profile' => Core\CoreContracts\ProfileUser::Administrator));

			$response = new Core\CoreCommons\ServiceResponse($administrators);

			Core\CoreCommons\Logger::Info("Users.getAdministrators : Get all administrators is finished");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
    * Add an user
    *
    * @param string $firstname The user's firstname
    * @param string $lastname The user's lastname
    * @param string $email The user's email
    * @param string $city The user's city
    * @param string $zipcode The user's zipcode
    * @param integer $profile The user's profile
    * @param integer $type The user's type
	* @param string $password The user's password
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the added user
    */
    public function addUser($firstname, $lastname, $email, $city, $zipcode, $pseudo, $latitude, $longitude, $profile, $password)
	{
		Core\CoreCommons\Logger::Info("Users.addUser : Start to add a new user $firstname, $lastname, $email, $city, $zipcode, $pseudo, $latitude, $longitude, $profile");
		
		$response = null;
		
		try
		{	
			//control email duplication
			$existingUser = $this->userRepository->findOneBy(array('Email' => $email));
			
			if($existingUser != null)
			{
				throw new \Exception("User_Mail_Already_Exist");
			}
			
			if($password == null || $password == '' || $password == ' ')
			{
				throw new \Exception("Password_Have_To_Be_Filled");
			}
			
			if(strlen($password) < 6)
			{
				throw new \Exception("Password_Have_To_More_Long");
			}
			
			$maskedPassword = $this->maskPassword($password);
			
			$hashPassword = sha1(PREFIX_SALT . $password . SUFFIX_SALT);    
			$user = new Core\CoreContracts\User($firstname, $lastname, $email, $city, $zipcode, $pseudo, $hashPassword, $latitude, $longitude, $profile);

			// Save the user on database
			$this->entityManager->persist($user);
			$this->entityManager->flush();
			
			$response = new Core\CoreCommons\ServiceResponse($user);

			Core\CoreCommons\Logger::Info(sprintf("Users.addUser : User %s added", $user));
			
			Core\CoreCommons\Logger::Info(sprintf("Users.addUser : Send a mail to the new user %s in order to inform him", $user));
			$parameters = Core\CoreCommons\Parameters::Singleton();
			$rootsite = $parameters::Get("rootsite");
			$servicebase = $parameters::Get("servicebase");
			$facebooklink = $parameters::Get("facebooklink");
			$youtubelink = $parameters::Get("youtubelink");
			$values = array();
			$values['#firstname'] = $firstname;
			$values['#lastname'] = $lastname;
			$values['#email'] = $email;
			$values['#password'] = $maskedPassword;
			$values['#hashPassword'] = $hashPassword;
			$values['#servicebase'] = $servicebase;
			$values['#rootsite'] = $rootsite;
			$values['#facebooklink'] = $facebooklink;
			$values['#youtubelink'] = $youtubelink;
			$mail = new Core\CoreCommons\Mail("NewUser", $values); 
			$mail->Send($email);
			
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
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
	public function sendContact($name, $email, $object, $message)
	{
		Core\CoreCommons\Logger::Info("Users.sendContact : Start to send contact $name, $email, $object, $message");
		
		$response = null;
		
		try
		{	
			$parameters = Core\CoreCommons\Parameters::Singleton();
			$facebooklink = $parameters::Get("facebooklink");
			$youtubelink = $parameters::Get("youtubelink");
			$values = array();
			$values['#name'] = $name;
			$values['#email'] = $email;
			$values['#object'] = $object;
			$values['#message'] = $message;
			$values['#facebooklink'] = $facebooklink;
			$values['#youtubelink'] = $youtubelink;
			$mail = new Core\CoreCommons\Mail("ContactForm", $values); 
			
			Core\CoreCommons\Logger::Info("Users.sendContact : Mail created");
			
			//To administrators
			$to = array();
			$administrators = $this->userRepository->findBy(array('Profile' => Core\CoreContracts\ProfileUser::Administrator));
			
			foreach($administrators as &$administrator)
			{
				array_push($to, $administrator->Email);
			}
			
			Core\CoreCommons\Logger::Info(sprintf("Users.sendContact : Administrators list created %s mails", count($to)));
			
			$sent = $mail->Send($to);
			
			if($sent == false)
			{
				throw new \Exception("Send_Mail_Failed");
			}
			
			$response = new Core\CoreCommons\ServiceResponse($sent);
			
			Core\CoreCommons\Logger::Info("Users.sendContact : Send contact is finished");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
	* valid an user
	*
	* @param string $password The user's password 
	*
	* @return Core\CoreCommons\ServiceResponse This response contains the validate user
	*/
	public function validUserEmail($password)
	{
		Core\CoreCommons\Logger::Info("Users.validUserEmail : Start to valid user mail");
		
		$response = null;
		
		try
		{	
			$user = $this->userRepository->findOneBy(array('Password' => $password));
			
			if(isset($user) == false)
			{
				throw new \Exception("User_Unknown"); 
			}
			$user->TestPassword1 = null;
			$user->TestPassword2 = null;
			$user->TestPassword3 = null;
			$user->State = Core\CoreContracts\StateUser::Online;
			$user->ModificationDate = new \DateTime();
			
			$this->entityManager->merge($user);
			$this->entityManager->flush();
			
			$response = new Core\CoreCommons\ServiceResponse($user);
			
			Core\CoreCommons\Logger::Info("Users.validUserEmail : Valid user mail is finished");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
	* Get all profiles
	*
	* @return Core\CoreCommons\ServiceResponse This response contains all profiles
	*/
	public function getProfiles()
	{
		Core\CoreCommons\Logger::Info("Users.getProfiles : Start to get all profiles");
		
		$response = null;
		
		try
		{	
			$profileUsers = new Core\CoreContracts\ProfileUser();
			
			$response = new Core\CoreCommons\ServiceResponse($profileUsers->getArray());

			Core\CoreCommons\Logger::Info("Users.getProfiles : Get all profiles is finished");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
	* Delete an user
	*
	* @param int $id The user's identifier 
	*
	* @return Core\CoreCommons\ServiceResponse This response contains the deleted user
	*/
	public function deleteUser($id)
	{
		Core\CoreCommons\Logger::Info("Users.deleteUser : Start to delete user #$id");
		
		$response = null;
		
		try
		{	
			$user = $this->userRepository->findOneBy(array('Id' => $id));
			
			if(isset($user) == false)
			{
				throw new \Exception("User_Unknown");
			}
			
			$this->entityManager->remove($user);
			$this->entityManager->flush();
			
			$response = new Core\CoreCommons\ServiceResponse($user);

			Core\CoreCommons\Logger::Info("Users.deleteUser : User #$id deleted");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
    * Update an user
    *
    * @param integer $id The unique identifier of a user
    * @param string $companyName The user's company name
    * @param string $ownerName The user's owner name
    * @param string $address The user's address
    * @param string $email The user's email
    * @param string $phone The user's phone
    * @param string $tag The user's tag
    * @param string $isAdherent Is the user adherent
    * @param string $estimation The user's estimation
    * @param string $webaddress The user's web address
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the updated user
    */
    public function updateUser($id, $firstname, $lastname, $email, $city, $pseudo, $latitude, $longitude, $profile, $password)
	{
		Core\CoreCommons\Logger::Info("Users.updateUser id : #$id, $firstname, $lastname, $email, $city, $pseudo, $latitude, $longitude, $profile, $password");
		
		$response = null;
		
		try
		{	
			//control email duplication
			$existingUser = $this->userRepository->findOneBy(array('Email' => $email));
			
			if($existingUser != null && $existingUser->Id != $id)
			{
				throw new \Exception("User_Mail_Already_Exist");
			}
			
			$user = $this->userRepository->findOneBy(array('Id' => $id));
			
			if(isset($user) == false)
			{
				throw new \Exception("User_Unknown");
			}
			
			$password = sha1(PREFIX_SALT . $password . SUFFIX_SALT); 
			
			$user->Firstname = $firstname;
			$user->Lastname = $lastname;
			$user->Email = $email;
			$user->City = $city;
			$user->Pseudo = $pseudo;
			$user->Latitude = $latitude;
			$user->Longitude = $longitude;
			$user->Profile = $profile;
			$user->Password = $password;
			$user->ModificationDate = new \DateTime();

			$this->entityManager->merge($user);
			$this->entityManager->flush();
			
			$user->Password = null;
			
			$response = new Core\CoreCommons\ServiceResponse($user);
			
			Core\CoreCommons\Logger::Info(sprintf("Users.updateUser : Send a mail to the user %s in order to inform him", $user));
			$parameters = Core\CoreCommons\Parameters::Singleton();
			$rootsite = $parameters::Get("rootsite");
			$facebooklink = $parameters::Get("facebooklink");
			$youtubelink = $parameters::Get("youtubelink");
			$values = array();
			$values['#firstname'] = $user->Firstname;
			$values['#lastname'] = $user->Lastname;
			$values['#score'] = $user->Score;
			$values['#rootsite'] = $rootsite;
			$values['#facebooklink'] = $facebooklink;
			$values['#youtubelink'] = $youtubelink;
			$mail = new Core\CoreCommons\Mail("UpdateUser", $values); 
			$mail->Send($email);
			
			Core\CoreCommons\Logger::Info("Users.updateUser : User #$id was updated");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
	* Update users's informations
	*
	* @param int $id The unique identifier of a user
	* @param string $newPassword The user's new password 
	*
	* @return Core\CoreCommons\ServiceResponse This response contains the updated user
	*/
	public function updateUserPassword($id, $newPassword)
	{
		Core\CoreCommons\Logger::Info("Users.updateUserPassword : Start to update the user's password #$id, $newPassword");
		
		$response = null;
		
		try
		{	
			$user = $this->userRepository->findOneBy(array('Id' => $id));

			if(isset($user) == false)
			{
				throw new \Exception("User_Unknown");
			}
			
			$hashPassword = sha1(PREFIX_SALT . $newPassword . SUFFIX_SALT);
			$user->Password = $hashPassword;
			$user->IsFirstUse = false;
			$user->ModificationDate = new \DateTime();

			$this->entityManager->merge($user);
			$this->entityManager->flush();
			
			$response = new Core\CoreCommons\ServiceResponse($user);

			Core\CoreCommons\Logger::Info("Users.updateUserPassword : User #$id was updated");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
	* Reset user password
	*
	* @param int $userid The user unique identifier
	*
	* @return Core\CoreCommons\ServiceResponse This response contains the user object or null
	*/
	public function resetUserPassword($userid)
	{
		Core\CoreCommons\Logger::Info("Users.resetUserPassword : Start to reset the user's password #$userid");
		
		$response = null;
		
		try
		{	
			$user = $this->userRepository->findOneBy(array('Id' => $userid));
			
			if(isset($user) == false)
			{
				throw new \Exception("User_Unknown");
			}
			
			$password = $this->generateRandomString();
			$hashPassword = sha1(PREFIX_SALT . $password . SUFFIX_SALT);
			$user->Password = $hashPassword;
			$user->TestPassword1 = null;
			$user->TestPassword2 = null;
			$user->TestPassword3 = null;
			$user->TestPassword3 = null;
			$user->State = Core\CoreContracts\StateUser::NotValid;
			$user->ModificationDate = new \DateTime();

			$this->entityManager->merge($user);
			$this->entityManager->flush();
			
			$response = new Core\CoreCommons\ServiceResponse($user);

			Core\CoreCommons\Logger::Info("Users.resetUserPassword : User #$userid password was updated");
			
			Core\CoreCommons\Logger::Info(sprintf("Users.resetUserPassword : Send a mail to user %s in order to inform him about resetting password", $user));
			$parameters = Core\CoreCommons\Parameters::Singleton();
			$facebooklink = $parameters::Get("facebooklink");
			$youtubelink = $parameters::Get("youtubelink");
			$servicebase = $parameters::Get("servicebase");
			$values = array();
			$values['#firstname'] = $user->Firstname;
			$values['#lastname'] = $user->Lastname;
			$values['#password'] = $password;
			$values['#hashPassword'] = $hashPassword;
			$values['#servicebase'] = $servicebase;
			$values['#facebooklink'] = $facebooklink;
			$values['#youtubelink'] = $youtubelink;
			$mail = new Core\CoreCommons\Mail("ResetUserPassword", $values); 
			$mail->Send($user->Email);
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
	* Get an user by his email
	*
	* @param string $email The user's email address 
	*
	* @return Core\CoreCommons\ServiceResponse This response contains the user
	*/
	public function getUserByEmail($email)
	{
		Core\CoreCommons\Logger::Info("Users.getUserByEmail : Start to get user by email address $email");
		
		$response = null;
		
		try
		{	
			$user = $this->userRepository->findOneBy(array('Email' => $email));

			$response = new Core\CoreCommons\ServiceResponse($user);

			Core\CoreCommons\Logger::Info("Users.getUserByEmail : User $email loaded ");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
	* Get an user
	*
	* @param int $id The user's identifier 
	*
	* @return Core\CoreCommons\ServiceResponse This response contains the user
	*/
	public function getUser($id)
	{
		Core\CoreCommons\Logger::Info("Users.getUser : Start to get user by id #$id");
		
		$response = null;
		
		try
		{	
			$user = $this->userRepository->findOneBy(array('Id' => $id));
			
			if(isset($user) == false)
			{
				throw new \Exception("User_Unknown");
			}

			$response = new Core\CoreCommons\ServiceResponse($user);

			Core\CoreCommons\Logger::Info("Users.getUser : User #$id loaded");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
	* Generate a new password for an user
	*
	* @param string $mail The user's mail 
	*
	* @return Core\CoreCommons\ServiceResponse This response contains the user
	*/
	public function generateNewPassword($email)
	{
		Core\CoreCommons\Logger::Info("Users.generateNewPassword : Start to generate a new password for $email");
		
		$response = null;
		
		try
		{	
			$user = $this->userRepository->findOneBy(array('Email' => $email));

			if(isset($user) == false)
			{
				throw new \Exception("User_Unknown");
			}
			
			$password = $this->generateRandomString();
			$hashPassword = sha1(PREFIX_SALT . $password . SUFFIX_SALT);
			$user->Password = $hashPassword;
			$user->TestPassword1 = null;
			$user->TestPassword2 = null;
			$user->TestPassword3 = null;
			$user->TestPassword3 = null;
			$user->State = Core\CoreContracts\StateUser::NotValid;
			$user->ModificationDate = new \DateTime();
			
			$this->entityManager->merge($user);
			$this->entityManager->flush();

			$response = new Core\CoreCommons\ServiceResponse($user);
			
			Core\CoreCommons\Logger::Info("Users.generateNewPassword : User $email password was updated");
			
			Core\CoreCommons\Logger::Info(sprintf("Users.generateNewPassword : Send a mail to user %s in order to inform him about resetting password", $user));
			$parameters = Core\CoreCommons\Parameters::Singleton();
			$facebooklink = $parameters::Get("facebooklink");
			$youtubelink = $parameters::Get("youtubelink");
			$servicebase = $parameters::Get("servicebase");
			$values = array();
			$values['#firstname'] = $user->Firstname;
			$values['#lastname'] = $user->Lastname;
			$values['#password'] = $password;
			$values['#hashPassword'] = $hashPassword;
			$values['#servicebase'] = $servicebase;
			$values['#facebooklink'] = $facebooklink;
			$values['#youtubelink'] = $youtubelink;
			$mail = new Core\CoreCommons\Mail("ResetUserPassword", $values); 
			$mail->Send($user->Email);
			

			Core\CoreCommons\Logger::Info("Users.generateNewPassword : New password generated");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
    * Set the user position
    *
    * @param integer $userId The user identifier
    * @param float $latitude The latitude
    * @param float $longitude The longitude
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
	public function updateUserPosition($userId, $latitude, $longitude)
	{
		Core\CoreCommons\Logger::Info("Users.updateUserPosition : Start to set user position #$userId");
		
		$response = null;
		
		try
		{	
			$user = $this->userRepository->findOneBy(array('Id' => $userId));
			
			if(isset($user) == false)
			{
				throw new \Exception("User_Unknown");
			}
			
			$user->Latitude = $latitude;
			$user->Longitude = $longitude;
			$user->ModificationDate = new \DateTime();

			$this->entityManager->merge($user);
			$this->entityManager->flush();

			$response = new Core\CoreCommons\ServiceResponse($user);

			Core\CoreCommons\Logger::Info("Users.updateUserPosition : User position #$userId updated");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
		
	}
	
	/**
    * Get user states
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
    public function getUserStates()
	{
		Core\CoreCommons\Logger::Info("Users.getUserStates : Start to get all user states");
		
		$response = null;
		
		try
		{	
			$stateUser = new Core\CoreContracts\StateUser();
			
			$response = new Core\CoreCommons\ServiceResponse($stateUser->getArray());

			Core\CoreCommons\Logger::Info("Users.getUserStates : Get all user states is finished");
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
    * send Validation Mail
    *
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
    public function sendValidationMail($userId)
	{
		Core\CoreCommons\Logger::Info("Users.sendValidationMail : Start to add a new validation mail for #$userId");
		
		$response = null;
		
		try
		{	
			//control email duplication
			$user = $this->userRepository->findOneBy(array('Id' => $userId));
			
			if(isset($user) == false)
			{
				throw new \Exception("User_Unknown");
			}
			
			$password = $this->generateRandomString();
			$hashPassword = sha1(PREFIX_SALT . $password . SUFFIX_SALT); 
			
			$user->Password = $hashPassword;
			$user->ModificationDate = new \DateTime();
			$this->entityManager->merge($user);
			$this->entityManager->flush();
			
			$response = new Core\CoreCommons\ServiceResponse($user);

			Core\CoreCommons\Logger::Info(sprintf("Users.sendValidationMail : Send a mail to the user %s in order to inform him", $user));
			$parameters = Core\CoreCommons\Parameters::Singleton();
			$rootsite = $parameters::Get("rootsite");
			$servicebase = $parameters::Get("servicebase");
			$facebooklink = $parameters::Get("facebooklink");
			$youtubelink = $parameters::Get("youtubelink");
			$values = array();
			$values['#firstname'] = $user->Firstname;
			$values['#lastname'] = $user->Lastname;
			$values['#email'] = $user->Email;
			$values['#password'] = $password;
			$values['#hashPassword'] = $hashPassword;
			$values['#servicebase'] = $servicebase;
			$values['#rootsite'] = $rootsite;
			$values['#facebooklink'] = $facebooklink;
			$values['#youtubelink'] = $youtubelink;
			$mail = new Core\CoreCommons\Mail("NewUser", $values); 
			$mail->Send($user->Email);
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}

	/**
    * Unban an user
    *
	* @param integer $userId The user identifier
	*
    * @return Core\CoreCommons\ServiceResponse This response contains the user
    */
    public function unBanUser($userId)
	{
		Core\CoreCommons\Logger::Info("Users.unBanUser : Start to add a new validation mail for #$userId");
		
		$response = null;
		
		try
		{	
			//control email duplication
			$user = $this->userRepository->findOneBy(array('Id' => $userId));
			
			if(isset($user) == false)
			{
				throw new \Exception("User_Unknown");
			}
			
			$hashPassword = sha1(PREFIX_SALT . $user->Password . SUFFIX_SALT);  
			$user->State = Core\CoreContracts\StateUser::NotValid;
			$user->ModificationDate = new \DateTime();
			$this->entityManager->merge($user);
			$this->entityManager->flush();

			$response = new Core\CoreCommons\ServiceResponse($user);

			Core\CoreCommons\Logger::Info(sprintf("Users.unBanUser : Send a mail to the user %s in order to inform him", $user));
			$parameters = Core\CoreCommons\Parameters::Singleton();
			$rootsite = $parameters::Get("rootsite");
			$servicebase = $parameters::Get("servicebase");
			$facebooklink = $parameters::Get("facebooklink");
			$youtubelink = $parameters::Get("youtubelink");
			$values = array();
			$values['#firstname'] = $user->Firstname;
			$values['#lastname'] = $user->Lastname;
			$values['#email'] = $user->Email;
			$values['#password'] = $user->Password;
			$values['#hashPassword'] = $hashPassword;
			$values['#servicebase'] = $servicebase;
			$values['#rootsite'] = $rootsite;
			$values['#facebooklink'] = $facebooklink;
			$values['#youtubelink'] = $youtubelink;
			$mail = new Core\CoreCommons\Mail("UnBanUser", $values); 
			$mail->Send($user->Email);
		}
		catch (\Exception $ex) 
		{
			$response = Core\CoreCommons\ServiceResponse::CreateError($ex);
		}
		
		return $response;
	}
	
	/**
	* Generate a random string 
	* 
	* @param string $length The string $ength targeted
	*/
	private function generateRandomString($length = 10) 
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) 
		{
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
	
	/**
	* Generate a random string 
	* 
	* @param string $password The password provided by the user
	*
	* @return string This response contains the masked password
	*/
	private function maskPassword($password)
	{
		$lastCharacters = substr($password, -2);
		return str_pad($lastCharacters, strlen($password), "*", STR_PAD_LEFT); 
	}
	
	private function GetBrowserAgent()
	{
		Core\CoreCommons\Logger::Info("Users.GetBrowserAgent : Start to get the browser name");
		
		$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
		$result = null;
		
		Core\CoreCommons\Logger::Debug("Users.GetBrowserAgent : The user agent is " . serialize($userAgent));
		
		if(is_null($userAgent) == false)
		{
			if (
				strpos(strtolower($userAgent), 'safari/') &&
				strpos(strtolower($userAgent), 'opr/')
			) 
			{
				// Opera
				$result = 'Opera';
			} else if (
				strpos(strtolower($userAgent), 'safari/') &&
				strpos(strtolower($userAgent), 'chrome/')
			) 
			{
				// Chrome
				$result = 'Chrome';
			} else if (
				strpos(strtolower($userAgent), 'msie') ||
				strpos(strtolower($userAgent), 'trident/')
			) 
			{
				// Internet Explorer
				$result = 'Internet Explorer';
			} else if (strpos(strtolower($userAgent), 'firefox/')) 
			{
				// Firefox
				$result = 'Firefox';
			} else if (
				strpos(strtolower($userAgent), 'safari/') &&
				(strpos(strtolower($userAgent), 'opr/') === false) &&
				(strpos(strtolower($userAgent), 'chrome/') === false)
			) 
			{
				// Safari
				$result = 'Safari';
			}
		}
		else
		{
			$result = 'Unknown';
			Core\CoreCommons\Logger::Info("Users.GetBrowserAgent : The browser name is not detectable");
		}

		Core\CoreCommons\Logger::Info("Users.GetBrowserAgent : The browser name detected is $result");

		return $result;
	}
}

?> 