<?php


abstract class StoredModel extends Model {

	protected static $collection = null;
	protected static $primaryKey = null;
	protected static $mongoID = false;

	protected $store;
	protected $stored = false;


	protected static $cache = array();

	public function __construct($properties, $stored = false) {

		$this->store = new UWAPStore();
		$this->stored = $stored;

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

		$timenow = floor(microtime(true)*1000.0);


		// error_log("__STORE updates " . var_export($update, true));
		
		if (empty($update)) {
			// error_log("Nothing to store. No new properties to update. Existing. " . var_export($properties, true));
			return;
		}

		$matchValue = null;
		if(static::$primaryKey === '_id') {

			if (isset($this->properties['id'])) {
				$matchValue = new MongoId($this->properties['id']);	
			}
		} else {
			$matchValue = $this->properties[static::$primaryKey];
		}


		if (!isset($this->properties['created'])) {
			$update['created'] = new MongoDate();
		} else {
			$update['updated'] = new MongoDate();
		}


	

		// If not already stored in database, create new item in database.
		if (!$this->stored) {
			$this->store->store(static::$collection, null, $update);
			return;
		}

		// echo "about to store a an updated version of this object\n";
		// echo "store into this collection: " . static::$collection . "\n";
		// echo "Mathcing " . var_export(array(static::$primaryKey => $matchValue), true) . "\n";
		// echo "Update: " . var_export($update, true) . "\n";
		// exit;


		// If already stored, approch an update on the object.
		$this->store->upsert(static::$collection, 
			array(static::$primaryKey => $matchValue),
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



	protected static function getRawByKey($key, $value, $allowEmpty = false) {

		// echo "GET " . $key . "=" . $value;
		$store = new UWAPStore();

		if (empty(static::$collection)) throw new Exception('Incomplete Model implementation: collection to storage not set');


		if($key === 'id' && static::$primaryKey === '_id') {
			$query = array('_id' => new MongoId($value));

		} else if($key === '_id' && static::$primaryKey === '_id') {
			$query = array('_id' => new MongoId($value));
			
		} else {

			if (!in_array($key, static::$validProps)) {
				throw new Exception('Cannot obtain a model of [' . __CLASS__ . '] by this key [' . $key . ']');
			}
			
			$query = array($key => $value); 
		}

		// if ($key === 'oid') {
		// 	echo "Query "; print_r($query); exit;	
		// }
		
		// $search = $store->queryOne(static::$collection, $query, static::$validProps );
		$search = $store->queryOne(static::$collection, $query);


		// echo "looking up userid " . $userid; echo '<pre>'; print_r($search); exit;

		if (empty($search)) {

			if ($allowEmpty) {
				return null;
			} else {
				throw new UWAPObjectNotFoundException("Not about to look up from " .  static::$collection . " where [" . $key . "=" . $value . "]");
			}

		}

		return $search;

	}


	protected static function getRawByID($id, $allowEmpty = false) {
		if (empty(static::$primaryKey)) throw new Exception('Incomplete Model implementation: primaryKey to storage not set');
		return self::getRawByKey(static::$primaryKey, $id, $allowEmpty);
	}


	public static function getByKey($key, $value) {
		$data = self::getRawByKey($key, $value);

		if (empty($data)) throw new UWAPObjectNotFoundException();
		return new static($data, true);
	}


	public static function exists($id) {

		$data = self::getRawByID($id, true);
		return ($data !== null);
	}


	public static function getByID($id, $allowEmpty = false) {

		if (isset(self::$cache[$id])) {
			return self::$cache[$id];
		}

		$data = self::getRawByID($id, $allowEmpty);

		if (empty($data)) {
			if ($allowEmpty) return null;
			throw new UWAPObjectNotFoundException();
		}

		$item = new static($data, true);

		self::$cache[$id] = $item;

		return $item;
	}


}


