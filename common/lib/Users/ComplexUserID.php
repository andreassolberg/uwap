<?php


class ComplexUserID {

	protected $primary = null;
	protected $keys = array();

	function __construct() {

	}

	public function addRaw($key) {
		if (empty($key)) throw new Exception('Trying to set an empty userID');
		$this->keys[] = $key;
	}
	public function add($prefix, $key) {
		if (empty($prefix)) throw new Exception('Missing prefix for userid key');
		if (empty($key)) throw new Exception('Trying to set an empty userID');
		$this->addRaw($prefix . ':' . $key);
	}

	public function getPri() {
		return $this->primary;
	}

	public function hasPri() {
		return ($this->primary !== null);
	}

	public function setPri($p) {
		$this->primary = $p;
	}

	public function isValid() {
		if ($this->hasPri()) return true;
		if (!empty($this->keys)) return true;
		return false;
	}

	public function getQuery() {

		$query = array();
		$queryComponents = array();

		if ($this->hasPri()) {
			$queryComponents[] = array(
				'userid' => $this->getPri()
			);
		}

		if (!empty($this->keys)) {
			
			$queryComponents[] = array(
				'userid-sec' => array(
					'$in' => $this->keys
				)
			);

		}

		if (count($queryComponents) === 0) { 
			return array();
		} else if (count($queryComponents) === 1) { 
			return $queryComponents[0];
		} else {
			return array(
				'$or' => $queryComponents
			);
		}

	}


	public static function generate() {
		$n = new ComplexUserID();
		$n->setPri('uuid:' . UWAPUtils::genUUID());
		return $n;
	}

}