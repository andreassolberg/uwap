<?php


class GroupFetcherAgora {
	
	protected $userid;

	function __construct($userid) {
		$this->userid = $userid;
	}

	function getGroups() {

		$gr = json_decode(file_get_contents('https://filsync.uninett.no:8082/agora/user/' . $this->userid . '/groups'), true);


		foreach($gr AS $k => $info) {
			$gr[$k]['id'] = 'voot:uninett:agora:' . $info['id'];
			$gr[$k]['title'] = 'Agora: ' . $info['title'];
		}

		return $gr;

	}

	function getGroupInfo() {
		return array();
	}


}