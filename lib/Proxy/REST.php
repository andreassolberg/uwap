<?php

class Proxy_REST {
	
	protected $config, $userid;

	function __construct() {

		$this->config = Config::getInstance();
	}



	function oauth() {
		$storage = new So_StorageServerUWAP();
		$server = new So_Server($storage);

		$token = $server->checkToken();
		$this->userid = $token->userid;
		// if ($token->userid !== 'andreas@uninett.no') throw new Exception('Youre not authorized to access this information.');

	}

	protected function rawget($url, $headers = array(), $redir = true, $curl = false) {

		$headerstring = '';
		foreach($headers AS $k => $v) {
			$headerstring .= $k . ': ' . $v . "\r\n";
		}
		$method = "GET";

		if (isset($opts["method"])) {
			$method = $opts["method"];
		}
		$opts = array(
			'http'=>array(
				'method'=> $method,
				'header'=> $headerstring,
				'follow_location' => $redir,
				'max_redirects' => 1
			)
		);
		error_log("URL: " . $url);
		error_log("Options: " . var_export($opts, true));
		error_log("Header string: " . $headerstring);
		error_log("Headers: " . var_export($headers, true));
		$context = stream_context_create($opts);

		if ($curl) {
			return $this->file_get_contents_curl($url, $headers, $redir);
		}

		return file_get_contents($url, false, $context);
		// return file_get_contents($url);
	}


	function getInfo() {
		$fullpath = $_SERVER['REQUEST_URI'];
		$proxyconfig = $this->config->getValue('proxies', array());

		return array(
			'fullpath' => $fullpath,
			'proxyconfig' => $proxyconfig,
		);
	}

	function show() {


		$fullpath = $_SERVER['REQUEST_URI'];
		$proxyconfig = $this->config->getValue('proxies', array());


		if (preg_match('|^/([a-zA-Z0-9_\-]+)/(.*?)$|', $fullpath, $matches)) {

			$proxy = $matches[1];
			$remotepath = $matches[2];

			if (!isset($proxyconfig[$proxy])) {
				throw new Exception('Proxy not setup for this endpoint.');
			}

			$url = $proxyconfig[$proxy]["endpoints"][0] . $remotepath;

			try {
				$this->oauth();	
			} catch(So_ExpiredToken $e) {
				header('WWW-Authenticate: Bearer realm="uwap", error="invalid_token", error_description="The access token expired"', true, 401);
				exit;
			}

			error_log("Proxying request to: " . $url);

			header('Content-Type: application/json; charset: utf-8');

			$headers = array();

			if ($proxyconfig[$proxy]["user"]) {
				$headers["UWAP-UserID"] = $this->userid;
			}
			if ($proxyconfig[$proxy]["token_hdr"]) {
				$headers[$proxyconfig[$proxy]["token_hdr"]] =$proxyconfig[$proxy]["token"];
			}
			echo $this->rawget($url, $headers);

			error_log( "show" . $file);
			exit;


		} else {
			throw new Exception('Wrong URL used for app proxy.');
		}


	}

}

