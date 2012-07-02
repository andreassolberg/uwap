<?php

class Static_File {
	
	protected $config;

	function __construct($config) {

		$this->config = $config;
		$acl = $this->config->getValue("access");
		if (!empty($acl)) {
			$this->authz();	
		}

		if (!$this->config->hasStatus(array('operational'))) {
			if ($this->config->hasStatus(array('pendingDAV'))) {
				echo 'Site is just created. It may take a few minutes before the site is operational.';
			} else {
				echo 'This site is disabled.';
			}
			exit;
		}

	}	

	function authz() {
		$acl = $this->config->getValue("access");
		if ($acl["ip"]) {
			// echo '<PRE>'; print_r($_SERVER); exit;
			if (!in_array($_SERVER["REMOTE_ADDR"], $acl["ip"])) {
				header("X-UWAP-ACCESS: Blocked by IP", true, 403);
				header("Content-type: text/plain; charset: utf-8");
				echo "Access denied.";
				exit;
				// throw new Exception("access denied.");
			}
		}
	}

	function show() {

		$subhost = $this->config->getID();
		$subhostpath = Config::getPath('apps/' . $subhost);

		$localfile = $_SERVER['REQUEST_URI'];
		if ($localfile === '/') $localfile = '/index.html';

		$file = $subhostpath . $localfile;

		if (preg_match('/\.html$/', $file)) {
			header("Content-Type: text/html; chatset: utf-8");
		} else if(preg_match('/\.png$/', $file)) {
			header("Content-Type: image/png");
		} else if(preg_match('/\.jpeg$/', $file)) {
			header("Content-Type: image/jpeg");
		} else if(preg_match('/\.jpg$/', $file)) {
			header("Content-Type: image/jpeg");
		} else if(preg_match('/\.gif$/', $file)) {
			header("Content-Type: image/gif");
		} else if(preg_match('/\.css$/', $file)) {
			header("Content-Type: text/css");
		} else if(preg_match('/\.js$/', $file)) {
			header("Content-Type: application/javascript; charset: utf-8");
		}


		// TODO: Do strict input checking on filename. for security.

		if (file_exists($file)) {
			echo file_get_contents($file);
		} else {
			throw new Exception('File not found [' . $file . ']');
		}

	}

}

