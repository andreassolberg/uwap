<?php

/**
* 
*/
class SCIMResourceRole extends SCIMResource {

	protected static $schemaIDs = array(
		'urn:mace:voot:schemas:role'
	);

	function __construct($data) {
		parent::__construct($data);
	}

}