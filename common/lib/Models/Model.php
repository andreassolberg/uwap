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

		$more = array('created', 'updated');

		foreach($more AS $p) {
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

	public function getArray($key, $default = '____NA') {
		$item = $this->get($key, $default);
		if (is_scalar($item)) return array($item);
		if (is_array($item)) return $item;
		return $default;
	}

	public function has($key) {
		return (isset($this->properties[$key]));
	}

	public function set($key, $val) {
		$changed = null;
		if (is_scalar($val)) {
			$changed = ($this->properties[$key] !== $val);
		}
		$this->properties[$key] = $val;
		return $changed;
	}

	public function getJSON($opts = array()) {
		return $this->properties;
	}

	public function addItemsToList($key, $items) {
		$current = $this->properties[$key];
		if (!is_array($current)) $current = array();
		$this->properties[$key] = self::array_add($current, $items);
	}
	public function removeItemsFromList($key, $items) {
		$current = $this->properties[$key];
		if (!is_array($current)) $current = array();
		$this->properties[$key] = self::array_remove($current, $items);
	}


	public static function array_remove($arr, $keys) {
		$newarr = array();

		foreach($arr AS $v) {
			$newarr[$v] = 1;
		}
		if (is_array($keys)) {
			foreach($keys AS $key) {
				unset($newarr[$key]);
			}
		} else {
			unset($newarr[$keys]);
		}
		return array_keys($newarr);
	}

	public static function array_add($arr, $keys) {
		$newarr = array();

		foreach($arr AS $v) {
			$newarr[$v] = 1;
		}
		if (is_array($keys)) {
			foreach($keys AS $key) {
				$newarr[$key] = 1;		
			}
		} else {
			$newarr[$keys] = 1;
		}
		

		return array_keys($newarr);
	}
	

}