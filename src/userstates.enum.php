<?php
namespace Core\CoreContracts;
/*
* States user enumeration
*/
class StateUser {

	/*
	* The user is unknown on the system
	*/
	const Unknown = 0;
	/*
	* The user is not validating by email
	*/
	const NotValid = 1;
	/*
	* The user is online
	*/
    const Online = 2;
	/*
	* The user is offline
	*/
    const Offline = 3;
	/*
	* The user is banned
	*/
    const Banned = 4;
	
	/*
	* Get all available state
	*/
	public function getArray()
	{
		$class = new \ReflectionClass(__Class__);
		return $class->getConstants();
	}
}
?> 