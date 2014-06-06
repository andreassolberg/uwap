<?php

/**
* SCIMAttributeDef defines properties of an attribute in a schema
*/
class SCIMAttributeDef 
{
	
	public 
		$name = null, 
		$type = 'String', 
		$multiValued = false, 
		$description = null, 
		$required = false, 
		$caseExact = false,
		$mutability = 'readWrite',
		$returned = 'default',
		$uniqueness = 'none',
		$referenceTypes = null
		;


	function __construct($opts) {
		foreach($opts AS $k => $v) {
			$this->{$k} = $v;
		}
	}

	function validateInput($input) {
		
	}

}