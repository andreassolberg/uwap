<?php

abstract class Set {

	protected $entries = array();

	function __construct($datalist = null) {

		if ($datalist !== null) {
			$this->addDataList($datalist);
		}
	}

	function add(Model $entry) {
		$this->entries[] = $entry;
	}

	abstract public function addData($entry);

	function addDataList($list) {
		if (!empty($list)) {
			foreach($list AS $entry) {
				$this->addData($entry);
			}
		}
	}



	function getJSON($opts = array()) {

		$result = array(
			'items' => array()
		);

		foreach($this->entries AS $e) {
			$result['items'][] = $e->getJSON($opts);
		}

		$result['count'] = count($result['items']);
		$result['startsWith'] = 1;
		$result['complete'] = true;
		return $result;
	}

}