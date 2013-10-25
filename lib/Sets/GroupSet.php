<?php

class GroupSet extends Set {


	public function addData($entry) {
		$this->add(new Group($entry));
	}


}