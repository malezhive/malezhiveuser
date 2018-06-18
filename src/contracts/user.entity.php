<?php
/**
* this class is the definition of a User
*
* @author Didelot Guillaume <gdidelot@live.fr>
* @version 1.0
* @package Core
* @subpackage CoreContracts
*/
namespace Core\CoreContracts;

use Core;

/**
* This class defined the user object.
*
* @method Core\CoreContracts\User __construct($companyName, $ownerName, $address, $email, $phone, $tag, $isAdherent, $estimation, $password, $profile = Core\CoreContracts\ProfileUser::User)
* @method Core\CoreContracts\User __toString()
*
* @Entity @Table(name="t_users")
*/
class User
{
    /**
    * The unique identifier
    * @var integer
    * @Id @Column(type="integer")
    * @GeneratedValue
    */
    public $Id;
    
    /**
    * The owner name
    * @var string
    * @Column(type="string")
    */
    public $Firstname;
	
	/**
    * The company name
    * @var string
    * @Column(type="string")
    */
    public $Lastname;
	
    /**
    * The email address
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $Email;

    /**
    * The phone number
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $City;

     /**
    * The phone number
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $ZipCode;
    
	/**
    * The phone number
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $Pseudo;
	
    /**
    * The user profile
    * @var Core\CoreContracts\ProfileUser
    * @see Core\CoreContracts\ProfileUser
    * @Column(type="integer")
    */
    public $Profile;
	
	/**
    * The user state
    * @var Core\CoreContracts\StateUser
    * @see Core\CoreContracts\StateUser
    * @Column(type="integer")
    */
    public $State;
    
    /**
    * The user password
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $Password;
	
	/**
    * The user TestPassword1
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $TestPassword1;
	
	/**
    * The user TestPassword2
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $TestPassword2;
	
	/**
    * The user TestPassword3
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $TestPassword3;
	
	/**
    * The latitude position
    * @var float
    * @Column(type="float", nullable=true)
    */
    public $Latitude;
	
	/**
    * The longitude position
    * @var float
    * @Column(type="float", nullable=true)
    */
    public $Longitude;
	
	/**
    * Is it the first use ?
    * @var boolean
    * @Column(type="boolean")
    */
    public $IsFirstUse;
	
	/**
    * The user score
    * @var integer
    * @Column(type="integer")
    */
    public $Score;
	
	/**
    * The user user agent
    * @var integer
    * @Column(type="string", nullable=true)
    */
    public $LastUserAgent;
	
    /**
    * The user modification date
    * @var datetime
    * @Column(type="datetime", nullable=false)
    */
    public $ModificationDate;
    
    /**
    * The user creation date
    * @var datetime
    * @Column(type="datetime", nullable=false)
    */
    public $CreationDate;
    
    /**
    * The default constructor
    *
    * @param string $firstname The user firstname name
    * @param string $lastname The user lastname name
    * @param string $email The user email address
    * @param string $city The user city
    * @param string $pseudo The user pseudo
    * @param string $password The user password
    * @param string $latitude The user latitude
    * @param string $longitude The user longitude
    * @param string $profile The user profile
    */
    public function __construct($firstname, $lastname, $email, $city, $zipcode, $pseudo, $password, $latitude, $longitude, $profile = Core\CoreContracts\ProfileUser::User)
    {
        $this->Lastname = $lastname;
        $this->Firstname = $firstname;
        $this->Email = $email;
        $this->City = $city;
        $this->ZipCode = $zipcode;
        $this->Pseudo = $pseudo;
        $this->Profile = $profile;
		$this->State = Core\CoreContracts\StateUser::NotValid;
        $this->Password = $password;
		$this->IsFirstUse = true;
		$this->Latitude = $latitude;
		$this->Longitude = $longitude;
		$this->Score = 0;
        $this->ModificationDate = new \DateTime();
        $this->CreationDate = new \DateTime();
    }
    
    /**
    * Convert an user to string
    */
    public function __toString()
    {
        return sprintf("%s", $this->Pseudo);
    }
}
?>