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
	protected function rawget($url, $headers = array(), $redir = true, $curl = false, $options = array()) {

		if (isset($options['data'])) {
			$headers['Content-type'] = 'application/json';
		}

		$headerstring = '';
		foreach($headers AS $k => $v) {
			$headerstring .= $k . ': ' . $v . "\r\n";
		}
		$method = "GET";
		if (isset($options["method"])) {
			$method = $options["method"];
		}
		$opts = array(
			// Documentation on http stream options available here:
			// * http://www.php.net/manual/en/context.http.php
			'http'=>array(
				'method'=> $method,
				'header'=> $headerstring,
				'follow_location' => $redir,
				'max_redirects' => ($redir ? 9 : 1)
			)
		);
		if (isset($options['data'])) {
			$opts['http']['content'] = json_encode($options['data']);
		}
		error_log("Options: " . json_encode($opts));
		error_log("Header string: " . $headerstring);
		error_log("HTTPClient      headers:" .  var_export($headers, true));
		$context = stream_context_create($opts);

		if ($curl) {
			return $this->file_get_contents_curl($url, $headers, $redir);
		}

		error_log("About to retrieve: " . $url);
		$rawdata = file_get_contents($url, false, $context);

		list($version, $status_code, $msg) = explode(' ', $http_response_header[0], 3);
		$headers = array();
		foreach($http_response_header AS $hdr) {
			self::http_parse_headers($hdr, &$headers);
		}

		// print_r($rawdata);
		if ($rawdata === false) throw new Exception('Status [' . $status_code .  ']: ' . $msg);

		return $rawdata;
	}

	protected static function http_parse_headers( $header, $hdrs ) {
		$key = null;
		$value = null;

		$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
		foreach( $fields as $field ) {
		    if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
		        $key = strtolower(preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1]))));
		        $value = trim($match[2]);

		        if (isset($key)) {
		        	if (!isset($hdrs[$key])) {
		        		$hdrs[$key] = array();
		        	}
		        	$hdrs[$key][] = $value;
		        }

		    }
		}
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
		// error_log("About to decode " . $result['data']);

		if (isset($options['xml']) && $options['xml'] == 1) {
			error_log("Retrieved data is in format: XML");
			$result['data'] = json_decode(json_encode(new SimpleXMLElement($result["data"]), true));
			$result['type'] = 'xml2json';
		} else if ($this->isJson($result["data"])) {
			error_log("Retrieved data is in format: json");
			$result['data'] = json_decode($result["data"], true);		
			$result['type'] = 'json';
		} else {
			error_log("Retrieved data is in format: text");
			$result['type'] = 'text';

			// $result['data'] = (string) $result['data'];
			
			// Detect whether the text is UTF-8 or not, if not then try to encode it as
			// UTF-8. This is needed in order to proper json encode, later on.
			if (mb_detect_encoding($result['data'], "UTF-8", true) === false) {
				$result['data'] = utf8_encode($result['data']);
			}

		}
		return $result;
	}

	public function get($url, $options) {
		$result = array("status" => "ok");

		// ($url, $headers = array(), $redir = true, $curl = false, $options = array()) {
		$result["data"] = $this->rawget($url, array(), true, false, $options);

		error_log("Got data: " . var_export($result["data"], true)) ;

		$result = $this->decode($result, $options);
		q
		return $result;
	}

	static function getClient($handler) {

		$subconfigobj = Config::getInstance();
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


