<?php


class HTTPClient {
	
	protected $config;

	public function __construct($config) {
		$this->config = $config;
	}

	// TODO: Security check on URL to not refer to local file system
	private function file_get_contents_curl($url, $headers = array(), $redir = true) {
		$ch = curl_init();

	 	$ha = array();
	 	foreach($headers AS $k => $v) {
	 		$ha[] = $k . ': ' . $v;
	 	}
	 	curl_setopt($ch, CURLOPT_HTTPHEADER, $ha);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_URL, $url);
		if (!$redir) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);	
		}
		
	 
		$data = curl_exec($ch);
		curl_close($ch);
	 
		return $data;
	}

	// TODO: Security check on URL to not refer to local file system
	protected function rawget($url, $headers = array(), $redir = true, $curl = false) {

		$headerstring = '';
		foreach($headers AS $k => $v) {
			$headerstring .= $k . ': ' . $v . "\r\n";
		}
		$opts = array(
			// Documentation on http stream options available here:
			// * http://www.php.net/manual/en/context.http.php
			'http'=>array(
				'method'=>"GET",
				'header'=> $headerstring,
				'follow_location' => $redir,
				'max_redirects' => ($redir ? 9 : 1)
			)
		);
		error_log("Options: " . json_encode($opts));
		error_log("Header string: " . $headerstring);
		error_log("Headers: " . json_encode($headers));
		$context = stream_context_create($opts);

		if ($curl) {
			return $this->file_get_contents_curl($url, $headers, $redir);
		}
		error_log("About to retrieve: " . $url);
		$rawdata = file_get_contents($url, false, $context);
		if ($rawdata === false) throw new Exception();
		return $rawdata;
	}


	/**
	 * A check if whether a string is valid JSON
	 * @param  string  $string The string to check
	 * @return boolean         Returns true if valid JSON
	 */
	protected function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	/**
	 * Takes data obtained as a string and conditionally perform some parsing of it 
	 * into other data types, such as XML or JSON.
	 * @param  array $result  The result object to update
	 * @param  array $options Options
	 * @return array          Returns result.
	 */
	protected function decode($result, $options) {
		if (empty($result["data"])) {
			return $result;
		}

		if (isset($options['xml']) && $options['xml'] == 1) {
			$result['data'] = json_decode(json_encode(new SimpleXMLElement($result["data"]), true));
			$result['type'] = 'xml2json';
		} else if ($this->isJson($result["data"])) {
			$result['data'] = json_decode($result["data"], true);		
			$result['type'] = 'json';
		} else {
			$result['type'] = 'text';
		}
		return $result;
	}

	public function get($url, $options) {
		$result = array("status" => "ok");
		$result["data"] = $this->rawget($url);

		// error_log("Got data: " . var_export($result["data"], true)) ;

		$result = $this->decode($result, $options);

		return $result;
	}

	static function getClient($handler) {

		$subconfigobj = new Config();
		$subhost = $subconfigobj->getID();
		$subconfig = $subconfigobj->getConfig();


		$config = array("type" => "plain");
		if ($handler !== 'plain') {

			if (empty($subconfig["handlers"]) || empty($subconfig["handlers"][$handler])) {
				throw new Exception("Cannot find a authentication handler for [" . $handler . "]");
			}
			$config = $subconfig["handlers"][$handler];			
		}


		if (empty($config["type"])) {
			throw new Exception("Handler configuration for [" . $handler . "] does not include the required [type] field.");
		}

		$config["subhost"] = $subhost;

		switch($config['type']) {

			case "basic":
				return new HTTPClientBasic($config);

			case "token":
				return new HTTPClientToken($config);

			case "oauth2":
				return new HTTPClientOAuth2($config);

			case "oauth1":
				return new HTTPClientOAuth1($config);

			case "plain":
			default:
				return new HTTPClient($config);
		}


	}

}


