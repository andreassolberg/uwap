<?php


class AppHosting {
	
	protected $store, $user;
	
	public function __construct($user) {
		$this->store = new UWAPStore();
		$this->user = $user;
	}

	protected function generateDavCredentials() {
		$username = Utils::generateCleanUsername($this->user->get('id'));
		$password = Utils::generateRandpassword();
		$credentials = array(
			'uwap-userid' => $this->user->get('id'),
			'username' => $username,
			'password' => $password
		);
		UWAPLogger::info('core-dev', 'Generating new DAV credentials for ' . $username);
		$this->store->store('davcredentials', null, $credentials);
	}

	public function getDavCredentials(App $app) {

		$userfilter = array(
			"uwap-userid" => $this->user->get('userid')
		);

		$credentials = array(
			'url' => Utils::getScheme() . '://dav.' . GlobalConfig::hostname() . '/' . $app->get('id')
		);

		$lookup = $this->store->queryOne('davcredentials', $userfilter);
		if(empty($lookup)) {
			$this->generateDavCredentials();
		}

		$lookup = $this->store->queryOne('davcredentials', $userfilter);

		$credentials['username'] = $lookup['username'];

		UWAPLogger::debug('config', 'Got DAVcredentials. (hidden password)', $credentials);

		$credentials['password'] = $lookup['password'];
		return $credentials;
	}

	public function bootstrap(App $app, $object) {
		if (!is_string($object) || empty($object)) {
			throw new Exception('Invalid template input to bootstrap application data');
		}
		if (!in_array($object, array('twitter', 'boilerplate'))) {
			throw new Exception('Not valid template to bootstrap application data');	
		}

		$template = $object;

		$td = Utils::getPath('bootstrap/' . $template);
		$ad = Utils::getPath('apps/' . $app->get('id'));

		if (!is_dir($td)) throw new Exception('Could not find bootstrap dir');
		if (!is_dir($ad)) throw new Exception('Could not find application dir');

		$cmd = 'cp -ruT ' . escapeshellarg($td) . ' ' . escapeshellarg($ad);

		$ret = null;
		$output = null;
		exec($cmd, &$output, &$ret);

		error_log("Performing " . $cmd);
		error_log(var_export($output, true));
		error_log(var_export($ret, true));

		UWAPLogger::info('core-dev', 'Bootstrapping application ', array(
			'command' => $cmd,
			'output' => $output,
			'returnvalue' => $ret,
		));
		
		return ($ret === 0);


	}



}