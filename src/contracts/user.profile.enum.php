<?php
/**
* User profiles  enumeration
*
* @author Didelot Guillaume <gdidelot@live.fr>
* @version 1.0
* @package Core
* @subpackage CoreContracts
*/
namespace MalezHive\Contracts;
/**
* User profiles  enumeration
* 
* @method string[] getArray()
*/
class UserProfile {
	/**
	* The user is a simple user
	*/
	const User = 0;
	/**
	* The user is a customer
	*/
	const Customer = 1;
	/**
	* The user is an employee
	*/
    const Employee = 2;
	/**
	* The user is an administrator
	*/
    const Administrator = 3;
	
	/**
	* Get all available user profiles
	*
	* @return string[] All enumeration on string array
	*/
	public function getArray()
	{
		$class = new \ReflectionClass(__Class__);
		return $class->getConstants();
	}
}
?> 