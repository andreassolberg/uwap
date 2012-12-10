<?php

class Static_File {
	
	protected $config;

	function __construct() {

		$this->config = Config::getInstance();

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
			// echo '<PRE>'; print_r($_SERVER); echo '</PRE>' exit;
			if (!in_array($_SERVER["REMOTE_ADDR"], $acl["ip"])) {
				header("X-UWAP-ACCESS: Blocked by IP", true, 403);
				header("Content-type: text/plain; charset: utf-8");
				echo "Access denied.";
				exit;
				// throw new Exception("access denied.");
			}
		}
	}

	public static function getpath($p) {
		$default = '/index.html';
		if (empty($p)) return $default;
		// error_log("Checking " . $p);
		if (preg_match('/^([a-zA-Z0-9\-._\/]*)(\?.*)?$/', $p, $matches)) {
			// error_log("MATCH");

			if (preg_match('/\.\./', $p)) {
					throw new Exception('Invalid with .. in filename.');
			}

			return $matches[1];
		} else {
			throw new Exception('Invalid file name');
		}
		return $default;
	}

	function getInfo() {

		$subhost = $this->config->getID();
		$subhostpath = $this->config->getAppPath(''); //Utils::getPath('apps/' . $subhost);

		$localfile = self::getpath($_SERVER['REQUEST_URI']);
		if ($localfile === '/') $localfile = '/index.html';

		$file = $subhostpath . $localfile;

		return array(
			'subhost' => $subhost,
			'subhostpath' => $subhostpath,
			'localfile' => $localfile,
			'file' => $file,
 		);
	}

	function show() {

		$subhost = $this->config->getID();
		$subhostpath = $this->config->getAppPath(''); //Utils::getPath('apps/' . $subhost);

		$localfile = self::getpath($_SERVER['REQUEST_URI']);
		if ($localfile === '/') $localfile = '/index.html';

		if (!preg_match('/^[a-zA-Z0-9\.\-_\/]+$/', $localfile)) {
			throw new Exception('Invalid characters in filename');
		}


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
		

		$caching = GlobalConfig::getValue('cache', true);

		$data = file_get_contents($file);
		$timestamp = filemtime($file);
		$tsstring = gmdate('D, d M Y H:i:s ', $timestamp) . 'GMT';
		$etag = md5($data);

		$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
		$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;

		header('Cache-Control: max-age=290304000, public');

		if ($caching && $if_none_match && ($if_none_match === $etag) ) {

			header('X-Cache-Match: etag');
			header('HTTP/1.1 304 Not Modified');
			exit();

		} else if ($caching && $if_modified_since && $if_modified_since === $tsstring) {

			header('X-Cache-Match: modified-since');
			header('HTTP/1.1 304 Not Modified');
			exit();

		} else {

			header('X-Cache-Etag: ' . $if_none_match . ' != ' . $etag);
			header('X-Cache-Modified: ' . $if_modified_since . ' != ' . $tsstring);

		    header("Last-Modified: $tsstring");
		    header("ETag: \"{$etag}\"");

		}

		echo $data;



	}

}

