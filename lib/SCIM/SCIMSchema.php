<?php

/**
* 
*/
class SCIMSchema 
{


	public $id, $name, $description, $attributes = array();
	
	function __construct($def) {

		if (isset($def['id'])) {
			$this->id = $def['id'];
		}

		if (isset($def['name'])) {
			$this->name = $def['name'];
		}
		if (isset($def['description'])) {
			$this->description = $def['description'];
		}
		if (isset($def['attributes'])) {
			$this->attributes = $def['attributes'];
		}

	}

	public function hasAttribute($k) {

		if (empty($this->attributes)) return false;
		foreach($this->attributes AS $a) {
			if ($k === $a['name']) return true;
		}
		return false;
	}

	public function getAttributeDef($k) {
		if (empty($this->attributes)) return null;
		foreach($this->attributes AS $a) {
			if ($k === $a['name']) return $a;
		}
		return null;
	}

	public function getRequiredAttributes() {
		$attrs = array();
		if (empty($this->attributes)) return array();
		if (empty($this->attributes)) return null;
		foreach($this->attributes AS $a) {
			if ($a['required']) {
				$attrs[] = $a['name'];
			}
		}
		return $attrs;
	}

	public function validateAttribute($k, $v) {

		if (!$this->hasAttribute($k)) throw new Exception('Attribute is not defined.');
		$adef = $this->getAttributeDef($k);


		if (isset($adef['multiValued']) && $adef['multiValued']) {
			if (!is_array($v)) {
				throw new Exception('Attribute [' . $k . '] is defined to be multivalued, and a single value is not allowed.');
			}
			$multivalue = $v;
		} else {
			if (is_array($v) && strtolower($adef['type']) !== 'complex') {
				throw new Exception('Attribute [' . $k . '] is defined to be singlevalued, and multiple values are not allowed.');
			}
			$multivalue = array($v);
		}


		if (empty($adef['type'])) 
			throw new Exception('Schema attribute definition for [' . $k . '] contains an empty attribute type definition');

		switch(strtolower($adef['type'])) {
			case 'string': 
				foreach($multivalue AS $m) $this->validateString($k, $m);
				break;

			case 'boolean':
				foreach($multivalue AS $m) $this->validateBoolean($k, $m);
				break;

			case 'datetime':
				foreach($multivalue AS $m) $this->validateDateTime($k, $m);
				break;

			case 'complex': 
				foreach($multivalue AS $m) $this->validateComplex($k, $m);
				break;			

			default: 
				throw new Exception('Schema attribute definition for [' . $k . '] contains an unknown attribute type [' . $adef['type'] . ']');
		}


	}

	protected function validateComplex($k, $v) {
		if (!is_array($v)) throw new Exception('Attribute [' . $k . '] value does not appear to be a complex attribute');
	}

	protected function validateString($k, $v) {
		if (!is_string($v)) throw new Exception('Attribute [' . $k . '] value does not appear to be a valid string');
	}

	protected function validateBoolean($k, $v) {
		if (!is_bool($v)) throw new Exception('Attribute [' . $k . '] value does not appear to be a valid boolean');
	}

	protected function validateDateTime($k, $v) {
		if (!is_string($v)) throw new Exception('Attribute [' . $k . '] value does not appear to be a valid datetime');
	}



}