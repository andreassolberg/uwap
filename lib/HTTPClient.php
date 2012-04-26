<?php


class HTTPClient {
	
	protected $config;

	public function __construct($config) {
		$this->config = $config;
	}

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

	protected function rawget($url, $headers = array(), $redir = true, $curl = false) {

		$headerstring = '';
		foreach($headers AS $k => $v) {
			$headerstring .= $k . ': ' . $v . "\r\n";
		}
		$opts = array(
			'http'=>array(
				'method'=>"GET",
				'header'=> $headerstring,
				'follow_location' => $redir,
				'max_redirects' => 1
			)
		);
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

	protected function decode($result, $options) {
		if (empty($result["data"])) {
			return $result;
		}
		if (!empty($options['xml'])) {
			$result['data'] = json_decode(json_encode(new SimpleXMLElement($result["data"]), true));
		} else {
			$result['data'] = json_decode($result["data"], true);		
		}		
		return $result;
	}

	public function get($url, $options) {
		$result = array("status" => "ok");
		$result["data"] = $this->rawget($url);

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

			// case "custom":
			// 	return new HTTPClientCustom($config);

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


