<?php

/**
 * Fetching groups from RedMine
 *
 * Activities for a user
 * 		http://scintilla.uninett.no:81/api/activities.json?days=30
 *
 * Group memberships for a user
 * 		http://scintilla.uninett.no:81/api/memberships.json?login=gurvinder.singh@uninett.no
 *
 * http://scintilla.uninett.no:81/api/activities.json?login=gurvinder.singh@uninett.no&key=5ecb7765af63387afecef80e957e39de5dc94df8
 *
 * Information about a project
 * 		http://scintilla.uninett.no:81/api/projects/1.json?key=5ecb7765af63387afecef80e957e39de5dc94df8
 * 
 */
class GroupFetcherRedmine {
	
	protected $userid;
	protected $key = '5ecb7765af63387afecef80e957e39de5dc94df8';
	protected $base = 'http://scintilla.uninett.no:81';

	/*
	 * Role map
	 */
	protected $rolemap = array(
		2 => 'admin',
	);

	function __construct($userid) {
		$this->userid = $userid;

		ini_set('default_socket_timeout', 3);
	}


	protected function getRole($roles) {

		foreach($this->rolemap AS $mapFrom => $mapTo) {
			foreach($roles AS $r) {
				if ($r['id'] === $mapFrom) return $mapTo;
			}
		}
		return 'member';
	}


	private function getURL($path, $query = array()) {
		
		$ctx = stream_context_create(array( 'http' => array( 'timeout' => 3 ) ) ); 

		$query['key'] = $this->key;
		$qs = http_build_query($query);

		$url = $this->base . $path . '?' . $qs;
		error_log('Contacting Redmine [' . $path . ']: ' . $url);
		$raw = file_get_contents($url, 0, $ctx);
		if ($raw === false) return array();
		$gr = json_decode($raw, true);
		return $gr;
	}

	private function getUserId() {
		$res = $this->getURL('/api/uidmapping.json', array('login' => $this->userid));
		if (empty($res)) throw new Exception('Could not resolve userid. Probably because it is not member of any groups.');

		// print_r($res); exit;

		$id = intval($res);
		return $id;
	}

	private function getUserinfo() {
		$userid = $this->getUserId();
		$userinfo = $this->getURL('/users/' . $userid . '.json', array('include' => 'memberships,groups'));
		return $userinfo['user'];
	}

	private function getMemberships() {
		$userinfo = $this->getUserinfo();

		if (empty($userinfo['memberships'])) return array();

		$groups = array();

		foreach($userinfo['memberships'] AS $ms) {
			$n = $ms['project'];
			$n['id'] = 'voot:uninett:redmine-test:' . $ms['project']['id'];
			$n['title'] = '[RedMine] ' . $ms['project']['name'];
			$n['roles'] = $ms['roles'];
			$n['role'] = $this->getRole($ms['roles']);
			unset($n['name']);
			$groups[] = $n;
		}

		// print_r($groups); exit;

		return $groups;
	}


	private function getProjectInfo($id) {
		return $this->getURL('/projects/' . $id . '.json');
	}




	function getGroups() {
		$gr = array();
		$memberships = $this->getMemberships();
		return $memberships;

		// echo '<pre>Groupinfo for :' . "\n"; print_r($memberships); exit;	

		foreach($memberships AS $m) {
			if (!isset($m['member'])) continue;
			$np = array();
			$pid = $m['member']['project_id'];
			$groupinfo = $this->getProjectInfo($pid);
			// echo '<pre>Groupinfo for ' . $pid . ':' . "\n"; print_r($memberships); print_r($groupinfo); exit;	

			$np['name'] = $groupinfo['name'];
			$np['description'] = $groupinfo['description'];
			$np['id'] = 'voot:uninett:redmine-test:' . $groupinfo['id'];

			$gr[] = $np;
		}

		// if (empty($gr)) return array();
		// foreach($gr AS $k => $info) {
		// 	$gr[$k]['id'] = 'voot:uninett:agora:' . $info['id'];
		// 	$gr[$k]['title'] = 'Agora: ' . $info['title'];
		// }

		return $gr;

	}

	function getGroupInfo() {
		return array();
	}


}