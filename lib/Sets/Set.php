<?php

abstract class Set {

	protected $entries = array();

	protected $startsWith = 0;
	protected $count = 0;
	protected $limit = null;

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

	function setMeta($meta) {
		if (isset($meta['startsWith'])) {
			$this->startsWith = $meta['startsWith'];
		}
		if (isset($meta['count'])) {
			$this->count = $meta['count'];
		}

		if (isset($meta['limit'])) {
			$this->limit = $meta['limit'];
		}

	}


	function getJSON($opts = array()) {

		$result = array(
			'items' => array()
		);

		foreach($this->entries AS $e) {
			$result['items'][] = $e->getJSON($opts);
		}

		if (!isset($this->count)) {
			$this->count = count($result['items']);
		}

		$result['count'] = $this->count;
		$result['startsWith'] = $this->startsWith;
		$result['limit'] = $this->limit;
		$result['complete'] = true;
		return $result;
	}

}