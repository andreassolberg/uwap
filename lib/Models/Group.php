<?php

class Group extends Model {
	



	protected static $validProps = array(
		'id', 'type', 'title', 'description', 'members', 'admins', 'listable', 'uwap-userid', 'source');


	public function __construct($properties) {

		parent::__construct($properties);

	}



	public function getJSON($opts = array()) {

		// echo 'group::getjson <pre>'; print_r($opts);
		// throw new Exception();


		$props = self::$validProps;
		if (isset($opts['type']) && $opts['type'] === 'basic') {
			$props = array('id', 'title', 'type', 'description', 'uwap-userid');
		}

		$ret = array();
		foreach($props AS $p) {
			if (isset($this->properties[$p])) {
				$ret[$p] = $this->properties[$p];
			}
		}


		return $ret;
	}





}