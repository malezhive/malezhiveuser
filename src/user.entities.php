<?php
namespace MalezHive\Contracts;
use MalezHive;

/**
 * @Entity @Table(name="t_users")
 **/
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
    * The firstname
    * @var string
    * @Column(type="string")
    */
    public $Firstname;
    
    /**
    * The lastname
    * @var string
    * @Column(type="string")
    */
    public $Lastname;
    
    /**
    * The email address
    * @var string
    * @Column(type="string")
    */
    public $Email;

    /**
    * The phone number
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $PhoneNumber;
    
    /**
    * The web address
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $WebAddress;
    
    /**
    * The user state
    * @var Core\CoreContracts\StateUser
    * @see Core\CoreContracts\StateUser
    * @Column(type="integer")
    */
    public $State;
    
    /**
    * The user type
    * @var Core\CoreContracts\UserType
    * @see Core\CoreContracts\UserType
    * @Column(type="integer")
    */
    public $Type;
    
    /**
    * The user profile
    * @var Core\CoreContracts\ProfileUser
    * @see Core\CoreContracts\ProfileUser
    * @Column(type="integer")
    */
    public $Profile;
    
    /**
    * The user password
    * @var string
    * @Column(type="string")
    */
    public $Password;
    
    /**
    * The user avatar content
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $Avatar;
    
    /**
    * The user avatar mime
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $AvatarMime;
    
    /**
    * The user avatar extension
    * @var string
    * @Column(type="string", nullable=true)
    */
    public $AvatarExtension;
    
    /**
    * The user address
    * @var Core\CoreContracts\Address
    * @see Core\CoreContracts\Address
    * @ManyToOne(targetEntity="Core\CoreContracts\Address", fetch="EAGER", cascade="persist")
    * @JoinColumn(name="Address", referencedColumnName="Id")
    */
    public $Address;
	
	/**
    * Is it the first use ?
    * @var boolean
    * @Column(type="boolean")
    */
    public $IsFirstUse;
    
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
    * @param string $firstname The firstname
    * @param string $lastname The lastname
    * @param string $email The email address
    * @param string $phonenumber The phone number
    * @param string $webaddress The web address
    * @param string $password The user password
    * @param string $profile The user profile
    * @param string $type The user type
    */
    public function __construct($firstname, $lastname, $email, $phonenumber, $webaddress, $password, $type, $profile = Core\CoreContracts\ProfileUser::User)
    {
        $this->Firstname = $firstname;
        $this->Lastname = $lastname;
        $this->Email = $email;
        $this->PhoneNumber = $phonenumber;
        $this->WebAddress = $webaddress;
        $this->State = Core\CoreContracts\StateUser::NotValid;
        $this->Profile = $profile;
        $this->Type = $type;
        $this->Password = $password;
        $this->IsFirstUse = true;
        $this->ModificationDate = new \DateTime();
        $this->CreationDate = new \DateTime();
    }
    
    /**
    * Set user as online
    */
    public function Online()
    {
        $this->ModificationDate = new \DateTime();
        $this->State = Core\CoreContracts\StateUser::Online;
    }
    
    /**
    * Set user as offline
    */
    public function Offline()
    {
        $this->ModificationDate = new \DateTime();
        $this->State = Core\CoreContracts\StateUser::Offline;
    }
    
    /**
    * Convert an user to string
    */
    public function __toString()
    {
        return sprintf("%s %s", $this->Firstname, $this->Lastname);
    }
}
?> 