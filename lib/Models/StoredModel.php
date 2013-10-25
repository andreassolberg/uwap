<?php


abstract class StoredModel extends Model {

	protected static $collection = null;
	protected static $primaryKey = null;	
	
	protected $store;


	public function __construct($properties) {

		$this->store = new UWAPStore();

		parent::__construct($properties);

	}


	public function store($properties = null) {

		// error_log("__STORE ABOUT TO STORe new client " . var_export($this->properties, true));
		// error_log("__STORE properties " . var_export($properties, true));

		if (empty(static::$collection)) throw new Exception('Incomplete Model implementation: collection to storage not set');
		if (empty(static::$primaryKey)) throw new Exception('Incomplete Model implementation: primaryKey to storage not set');


		/*
		 * Decide which properties to store.
		 */
		$update = null;
		if ($properties === null) {
			$update = $this->properties;
		} else {
			$update = array();
			foreach($properties AS $p) {
				if (isset($this->properties[$p])) {
					$update[$p] = $this->properties[$p];
				}
			}
		}


		// error_log("__STORE updates " . var_export($update, true));
		
		if (empty($update)) {
			// error_log("Nothing to store. No new properties to update. Existing. " . var_export($properties, true));
			return;
		}

		

		$this->store->upsert(static::$collection, 
			array(static::$primaryKey => $this->properties[static::$primaryKey]), 
			$update
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



	protected static function getRawByKey($key, $value) {

		// echo "GET " . $key . "=" . $value;
		$store = new UWAPStore();

		if (empty(static::$collection)) throw new Exception('Incomplete Model implementation: collection to storage not set');

		if (!in_array($key, static::$validProps)) {
			throw new Exception('Cannot obtain a model of [' . __CLASS__ . '] by this key [' . $key . ']');
		}


		$query = array($key => $value); 
		// $search = $store->queryOne(static::$collection, $query, static::$validProps );
		$search = $store->queryOne(static::$collection, $query);


		// echo "looking up userid " . $userid; echo '<pre>'; print_r($search); exit;

		if (empty($search)) return null;

		return $search;

		// $user = new static($search);
		// return $user;
	}


	protected static function getRawByID($id) {
		if (empty(static::$primaryKey)) throw new Exception('Incomplete Model implementation: primaryKey to storage not set');
		return self::getRawByKey(static::$primaryKey, $id);
	}

	public static function getByKey($key, $value) {
		$data = self::getRawByKey($key, $value);
		return new static($data);
	}


	public static function exists($id) {
		$data = self::getRawByID($id);
		return ($data !== null);
	}

	public static function getByID($id) {

		$data = self::getRawByID($id);
		return new static($data);
	}
}