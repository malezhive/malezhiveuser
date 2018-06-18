<?php
namespace Core\CoreContracts;
/*
* Profiles user enumeration
*/
class ProfileUser {

	/*
	* The user is a simple user
	*/
	const Unknwown = 0;
	/*
	* The user is an employee
	*/
	const Customer = 1;
	/*
	* The user is a member
	*/
    const Administrator = 2;
	
	/*
	* Get all available profile
	*/
	public function getArray()
	{
		$class = new \ReflectionClass(__Class__);
		return $class->getConstants();
	}
}
?> 