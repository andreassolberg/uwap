<?php


/**
* 
*/
class SCIMResourceGroupType extends SCIMResource {

	protected static $schemaIDs = array(
		'urn:mace:voot:schemas:groupType'
	);

	function __construct($data) {
		parent::__construct($data);
	}


	public function getJSON() {
		$obj = parent::getJSON();
		return $obj;
	}

}