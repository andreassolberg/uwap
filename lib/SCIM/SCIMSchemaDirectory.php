<?php

/**
* SCIMSchemaDirectory
*/
class SCIMSchemaDirectory {

	protected static $instance = null;

	protected $schemas = array();

	function __construct() {

	}

	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function createSchema($id) {
		$base = dirname(__FILE__);
		switch($id) {
			case 'urn:scim:schemas:core:2.0:Group':
				return new SCIMSchema(json_decode(file_get_contents($base . '/schemas/group.json'), true));

			case 'urn:mace:voot:schemas:group':
				return new SCIMSchema(json_decode(file_get_contents($base . '/schemas/vootGroup.json'), true));

			case 'urn:mace:voot:schemas:role':
				return new SCIMSchema(json_decode(file_get_contents($base . '/schemas/role.json'), true));

			case 'urn:scim:schemas:core:2.0:User':
				return new SCIMSchema(json_decode(file_get_contents($base . '/schemas/user.json'), true));

			case 'urn:mace:voot:schemas:groupType':
				return new SCIMSchema(json_decode(file_get_contents($base . '/schemas/grouptype.json'), true));			

			default: 
				throw new Exception('Could not find defintiion of schema [' . $id . ']');
		}

	}

	public function getSchema($schemaID) {
		if (!isset($this->schemas[$schemaID])) {
			$this->schemas[$schemaID] = $this->createSchema($schemaID);
		}
		return $this->schemas[$schemaID];
	}

	public static function get($schemaID) {
		$t = self::getInstance();
		return $t->getSchema($schemaID);
	}

}