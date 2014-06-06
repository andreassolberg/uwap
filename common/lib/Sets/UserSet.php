<?php

class UserSet extends Set {
	

	public function addData($entry) {
		$this->add(new User($entry));
	}

}