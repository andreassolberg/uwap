<?php


/**
 * Config is a class that holds a set of values, 
 * and helper functions to get them. This config class is usually not used alone, but
 * instead from a config container class, such as GlobalConfig (that loads config etc.)
 */
class Config {

	protected $properties;

	public function __construct($properties) {
		assert('is_array($properties)');
		$this->properties = $properties;
	}

	public function getAll() {
		return $this->properties;
	}

	public function get($key, $default = null, $required = false) {
		if (isset($this->properties[$key])) return $this->properties[$key];
		if ($required === true) throw new Exception('Missing required global configuration property [' . $key . ']');
		return $default;
	}



}