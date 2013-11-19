<?php


abstract class StoredModel extends Model {

	protected static $collection = null;
	protected static $primaryKey = null;
	protected static $mongoID = false;

	protected $store;


	protected static $cache = array();

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
			$update['created'] = $timenow;
		} else {
			$update['updated'] = $timenow;
		}


		if (!isset($matchValue)) {
			$this->store->store(static::$collection, null, $update);
			return;
		}



		// echo "about to store a new object: "; print_r($update); exit;


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



	protected static function getRawByKey($key, $value) {

		// echo "GET " . $key . "=" . $value;
		$store = new UWAPStore();

		if (empty(static::$collection)) throw new Exception('Incomplete Model implementation: collection to storage not set');



		if($key === 'id' && static::$primaryKey === '_id') {
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

		if (empty($data)) return null;
		return new static($data);
	}


	public static function exists($id) {
		$data = self::getRawByID($id);
		return ($data !== null);
	}

	public static function getByID($id) {

		if (isset(self::$cache[$id])) {
			return self::$cache[$id];
		}

		$data = self::getRawByID($id);

		if ($data === null) return null;
		// echo "data<pre>";
		// print_r($data);// exit();

		$item = new static($data);

		self::$cache[$id] = $item;

		return $item;
	}
}