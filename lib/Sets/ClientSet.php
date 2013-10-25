<?php

class ClientSet extends Set {



	function add(Model $entry) {
		if (!$entry instanceof Client) {
			throw new Exception('Invalid object model type added to ClientSet');
		}
		parent::add($entry);
	}

	public function addData($entry) {
		$this->add(new Client($entry));
	}


}