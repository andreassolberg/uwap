<?php


/**
* 
*/
class SCIMResouceGroup extends SCIMResource {

	protected static $schemaIDs = array(
		'urn:scim:schemas:core:2.0:Group',
		'urn:mace:voot:schemas:group'
	);

	function __construct($data) {
		parent::__construct($data);
	}

}