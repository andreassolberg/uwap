<?php


/**
* 
*/
abstract class SCIMResource  {
	
	protected 
		$resourceType = null,
		$created,
		$lastModified,
		$location,
		$version,
		$attributes
		;

	protected static $schemaIDs;


	protected $id;
	protected $externalId;
	protected $schemas = array();

	protected $values = array();

	function __construct($vs) {
		$this->addSchemas(static::$schemaIDs);
		foreach($vs AS $k => $v) {
			error_log ("----");
			try {
				$this->setValue($k, $v);
			} catch(Exception $e) {
				error_log ("Not able to set attribute [" . $k . "] - error:");
				error_log("   › " . $e->getMessage() . "");
			}
		}

	}

	public function validate() {

		$missingAttributes = array();
		$myattr = array_keys($this->values);

		foreach($this->schemas AS $s) {
			$reqattr = $s->getRequiredAttributes();
			$missing = array_diff($reqattr, $myattr);
			if (!empty($missing)) {
				error_log ("[Error] Missing these REQUIRED attributes from schema [" . $s->name . "] : " . join(', ', $missing) . "");
				$missingAttributes = array_merge($missingAttributes, $missing);
			}
		}

		if (!empty($missingAttributes)) {
			throw new Exception('This resource did not include REQUIRED attributes ' . join(', ', $missingAttributes));
		}

	}

	protected function getAttributeDef($name) {
		foreach($this->schemas AS $s) {
			if ($s->hasAttribute($name)) return $s;	
		}
		return null;
	}

	protected function setValue($k,$v) {

		$schema = $this->getAttributeDef($k);		
		if ($schema === null) throw new Exception('Not able to set attribute with name ' . $k . " (not defined in schemas for this resource type)");
		error_log ("Processing attribute [" . $k . "] which is defined in schema [" . $schema->name . "]");
		$schema->validateAttribute($k, $v);
		$this->values[$k] = $v;
	}


	protected function addSchemas($schemaids) {

		foreach($schemaids AS $s) $this->addSchema($s);
	}
	protected function addSchema($schemaid) {
		$this->schemas[] = SCIMSchemaDirectory::get($schemaid);
	}

	public function getJSON() {
		$obj = array();

		if (isset($this->id)) $obj['id'] = $this->id;
		if (isset($this->externalId)) $obj['externalId'] = $this->externalId;

		foreach($this->values AS $k => $v) {
			$obj[$k] = $v;
		}


		return $obj;
	}

	public static function getSchemas() {
		return self::$schemaIDs;
	}

}