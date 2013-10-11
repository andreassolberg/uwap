<?php

class ExtGroups {
	
	protected $user;

	public function __construct($user) {
		$this->user = $user;
	}

	public function getGroups() {

		$groups = array();


		$input = array(
			'userid' => $this->user->get('userid'),
		);
		if (preg_match('/(.*?)@(.*?)$/', $this->user->get('userid'), $matches)) {
			$input['realm'] = $matches[2];
		}
		$input['idp'] = 'https://idp.feide.no';

		$inputstr = json_encode($input);

		$cmd = '/root/nvm/v0.10.19/bin/node /root/groupengine/getbyuser.js';
		$descriptorspec = array(
			0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
			1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
			2 => array("file", "/tmp/uwap-group-output.txt", "a") // stderr is a file to write to
		);

		$cwd = '/tmp';
		$env = array('some_option' => 'aeiou');

		$process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);

		// echo "return value"; print_r($cmd);

		// echo "About to start"; print_r($input);

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



			if ($return_value === 0) {
				$parsedgroups = json_decode($result, true);


				$gos = array();
				foreach($parsedgroups['groups'] AS $groupid => $pg) {

					// print_r($parsedgroups); exit;
					$pg['id'] = $groupid;
					$g = new Group($pg);
					$role = new Role($this->user, $g, array('role' => $pg['role']));
					$gos[] = $role;
				}

				return $gos;

				// echo '<pre>';
				// print_r($parsedgroups);
				// exit;
			}

			// echo "command returned $return_value\n";
		}
		return null;
		



		return $groups;

	}	

}