<?php


abstract class Model {
	

	protected $properties = array();
	protected $store;

	protected static $validProps = array();
	protected static $collection = null;
	protected static $primaryKey = null;

	public function __construct($properties) {

		// echo '<pre>Model';		echo __CLASS__ . ".\n\n";
		// echo "about to construct group"; 
		// print_r($properties); 
		// print_r(static::$validProps);
		// echo "\n------\n";

		$this->store = new UWAPStore();

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
			throw new Exception('Could not obtain object proeprty ' . $key);

			// echo '<pre>Do not have key id : '; print_r($this);
		}

		return $this->properties[$key];
	}

	public function has($key) {
		return (!empty($this->properties[$key]));
	}

	public function set($key, $val) {
		$this->properties[$key] = $val;
	}

	public function getJSON($opts = array()) {
		return $this->properties;
	}

	public function store() {

		if (empty(static::$collection)) throw new Exception('Incomplete Model implementation: collection to storage not set');
		if (empty(static::$primaryKey)) throw new Exception('Incomplete Model implementation: primaryKey to storage not set');

		$object = $this->properties;
		
		$this->store->upsert(static::$collection, 
			array(static::$primaryKey => $this->properties[static::$primaryKey]), 
			$this->properties
		);

	}

	public function remove() {

		if (empty(static::$collection)) throw new Exception('Incomplete Model implementation: collection to storage not set');
		if (empty(static::$primaryKey)) throw new Exception('Incomplete Model implementation: primaryKey to storage not set');

		$query = array(
			static::$primaryKey => $this->get(static::$primaryKey)
		);
		
		return $this->store->remove(static::$collection, null, $query);

	}



	public static function getByKey($key, $value) {


		$store = new UWAPStore();

		if (empty(static::$collection)) throw new Exception('Incomplete Model implementation: collection to storage not set');

		if (!in_array($key, static::$validProps)) {
			throw new Exception('Cannot obtain a model of ' . __CLASS__ . ' by this key ' . $key);
		}


		$query = array($key => $value); 
		$search = $store->queryOne(static::$collection, $query, static::$validProps );


		// echo "looking up userid " . $userid; echo '<pre>'; print_r($search); exit;

		if (empty($search)) return null;

		$user = new static($search);
		return $user;
	}



	public static function getByID($id) {


		if (empty(static::$primaryKey)) throw new Exception('Incomplete Model implementation: primaryKey to storage not set');

		return self::getByKey(static::$primaryKey, $id);
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