<?php

abstract class Set {

	protected $items = array();

	protected $startsWith = 0;
	protected $count = null;
	protected $limit = null;




	function __construct($datalist = null) {

		if ($datalist !== null) {
			$this->addDataList($datalist);
		}
	}

	function add(Model $entry) {
		$this->items[] = $entry;
	}

	public function getItems() {
		return $this->items;
	}

	abstract public function addData($entry);

	public function addDataList($list) {
		if (!empty($list)) {
			foreach($list AS $entry) {
				$this->addData($entry);
			}
		}
	}

	public function mergeInto(Set $set) {
		// TODO: Verify that sets is of same type.

		if (!empty($this->items)) {
			foreach($this->items AS $entry) {
				// echo "merging in "; print_r($entry);
				$set->add($entry);
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

		foreach($this->items AS $e) {
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