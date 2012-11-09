<?php


class GroupFetcherAgora {
	
	protected $userid;

	function __construct($userid) {
		$this->userid = $userid;
	}

	function getGroups() {

		
		ini_set('default_socket_timeout', 3);
		$ctx = stream_context_create(array( 'http' => array( 'timeout' => 3 ) ) ); 

		$url = 'https://filsync.uninett.no:8082/agora/user/' . $this->userid . '/groups';
		error_log('Contacting Agora: ' . $url);
		$raw = @file_get_contents($url, 0, $ctx);
		if ($raw === false) return array();
		$gr = json_decode($raw, true);

		if (empty($gr)) return array();
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