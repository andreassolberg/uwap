<?php


abstract class Model {
	

	protected $properties = array();


	protected static $validProps = array();


	public function __construct($properties) {


		$toset = array();

		foreach(static::$validProps AS $p) {
			if (isset($properties[$p])) {
				$toset[$p] = $properties[$p];
			}
		}
		$this->properties = $toset;

	}


	public function get($key, $default = '____NA') {
		if (!$this->has($key)) {
			if ($default !== '____NA') {
				return $default;
			}
			// echo '<pre>';
			// print_r($this->properties);
			throw new Exception('Could not obtain object property [' . $key . ']');

			// echo '<pre>Do not have key id : '; print_r($this);
		}

		return $this->properties[$key];
	}

	public function has($key) {
		return (isset($this->properties[$key]));
	}

	public function set($key, $val) {
		$this->properties[$key] = $val;
	}

	public function getJSON($opts = array()) {
		return $this->properties;
	}


	public static function array_remove($arr, $key) {
		$newarr = array();

		foreach($arr AS $v) {
			if ($v !== $key) $newarr[$v] = 1;
		}

		return array_keys($newarr);
	}

	public static function array_add($arr, $key) {
		$newarr = array();

		foreach($arr AS $v) {
			$newarr[$v] = 1;
		}
		$newarr[$key] = 1;

		return array_keys($newarr);
	}
	

}