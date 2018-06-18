<?php
/**
* User profiles  enumeration
*
* @author Didelot Guillaume <gdidelot@live.fr>
* @version 1.0
* @package Core
* @subpackage CoreContracts
*/
namespace Core\CoreContracts;
/**
* User profiles  enumeration
* 
* @method string[] getArray()
*/
class ProfileUser {
	/**
	* The user is a simple user
	*/
	const User = 0;
	/**
	* The user is a municipal employee
	*/
	const Supervisor = 1;
	/**
	* The user is an administrator
	*/
    const Administrator = 2;
	
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