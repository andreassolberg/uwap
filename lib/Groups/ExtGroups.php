<?php

class ExtGroups {
	
	protected $user;

	public function __construct($user) {
		$this->user = $user;
	}


	private function call($script, $input = array()) {

		if ($this->user) {
			$userdata = array(
				'userid' => $this->user->get('userid'),
			);
			if (preg_match('/(.*?)@(.*?)$/', $this->user->get('userid'), $matches)) {
				$userdata['realm'] = $matches[2];
			}
			$userdata['idp'] = 'https://idp.feide.no';
			$input['user'] = $userdata;
		}
		

		// echo "input: "; print_r($input);
		$inputstr = json_encode($input);
		// echo "script: " . $script;

		$cmd = '/root/nvm/v0.10.19/bin/node /root/groupengine/' . $script . '.js';
		$descriptorspec = array(
			0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
			1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
			2 => array("file", "/tmp/uwap-output-" . $script . ".txt", "a") // stderr is a file to write to
		);

		$cwd = '/tmp';
		$env = array('some_option' => 'aeiou');

		$process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);

		// echo "return value"; print_r($cmd);

		// echo "About to start"; print_r(json_encode($input));

		if (is_resource($process)) {
			// $pipes now looks like this:
			// 0 => writeable handle connected to child stdin
			// 1 => readable handle connected to child stdout
			// Any error output will be appended to /tmp/error-output.txt

			fwrite($pipes[0], $inputstr);
			fclose($pipes[0]);

			$result = stream_get_contents($pipes[1]);
			fclose($pipes[1]);

			// It is important that you close any pipes before calling
			// proc_close in order to avoid a deadlock
			$return_value = proc_close($process);

			 // echo "raw data is $result $return_value";

			if ($return_value === 0) {
				$parsedData = json_decode($result, true);

				// echo "raw data is $result";

				return $parsedData;



				// echo '<pre>';
				// print_r($parsedgroups);
				// exit;
			}

			// echo "command returned $return_value\n";
		}
		return null;



	}



	public function getPublicGroups() {

		return array();

	}


	public function getGroups() {

		$parsedgroups = $this->call('getbyuser');

		// echo "data"; print_r($parsedgroups);exit;

		$gos = array();

		// print_r($parsedgroups); exit;

		foreach($parsedgroups['groups'] AS $groupid => $pg) {

			// print_r($parsedgroups); exit;
			$pg['id'] = $groupid;
			$g = new Group($pg);
			$role = new Role($this->user, $g, array('role' => $pg['role']));
			$gos[] = $role;
		}

		return $gos;
	}	



	public function peopleListRealms() {
		$realms = array();
		$r = array(
			'name' => '',
			'realm' => '',
			'default' => false,
		);
		$realms[] = $r;
		return $realms;
	}

	public function peopleQuery($realm, $query) {
		
		$data = $this->call('getpeople', array(
			'query' => $query, 
			'realm' => $realm
		));

		return $data;
	}


	public function getByID($id) {

		$parsedgroups = $this->call('getgroup', array('groupid' => $id));

		return $parsedgroups;

		// echo "RESULT:";
		// print_r($parsedgroups ); exit;


		// $gos = array();
		// foreach($parsedgroups['groups'] AS $groupid => $pg) {

		// 	// print_r($parsedgroups); exit;
		// 	$pg['id'] = $groupid;
		// 	$g = new Group($pg);
		// 	$role = new Role($this->user, $g, array('role' => $pg['role']));
		// 	$gos[] = $role;
		// }

		// return $gos;

	}

}