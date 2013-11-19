<?php

class RoleSet extends Set {
	

	public function addData($entry) {
		$this->add(new Role($entry));
	}

}