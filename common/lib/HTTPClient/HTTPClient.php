<?php


class HTTPClientException extends Exception {
	public $code, $httpmsg, $body;
	function __construct($message, $code, $httpmsg, $body) {
		parent::__construct($message);
		$this->code = $code;
		$this->httpmsg = $httpmsg;
		$this->body = $body;
	}
}

class HTTPResponse {
	public $code, $httpmsg, $body, $contentType;
	function __construct($code, $body) {
		$this->code = $code;
		// $this->httpmsg = $httpmsg;
		$this->body = $body;
	}

	public function getJSON() {

	}
}

class HTTPClient {
	
	protected $config;
	protected $client = null;
	protected $user = null;


	public function __construct($config, $client) {
		$this->config = $config;
		$this->client = $client;
	}

	public function setAuthenticated(User $user = null) {
		$this->user = $user;
	}

	// public function setAuthenticatedClient($clientid, $scopes) {
	// 	$this->clientid = $clientid;
	// 	$this->clientScopes = $scopes;
	// }

	protected function getUserAuthHeaders(&$headers) {

		// $headers = array();

		if (!isset($this->config['user'])) return $headers;
		if (!$this->config['user']) return $headers;
		if ($this->user === null) throw new Exception('Cannot add http headers with authenticated user when user is not authenticated.');
		if ($this->client === null) throw new Exception('Cannot add http headers with authenticated user when client is not authenticated.');

		$scopes = array();

		$headers['UWAP-UserID'] = $this->user->get('userid');
		$headers["UWAP-Groups"] = join(',', $this->user->getGroupIDs());
		$headers['UWAP-Client'] = $this->client->get('id');
		$headers["UWAP-Scopes"] = join(',', $scopes);

		if (
				isset($this->config['userid-secondary']) && 
				$this->config['userid-secondary'] !== null &&
				is_string($this->config['userid-secondary'])
				) {

			$secKeys = $this->user->getSecondaryKeysOfType($this->config['userid-secondary']);
			$headers['UWAP-UserIDsec'] = join(',', $secKeys);
			// $headers['UWAP-UserIDsec'] = 'data';

		}
		// $headers['CONFIG'] = json_encode($this->config);
		// echo "Headers: \n"; print_r($headers); exit;

		return $headers;
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
			
			

			$json = xmlToArray(new SimpleXMLElement($result["data"]));

			// echo '<pre>'; print_r($json); exit;

			error_log("Retrieved data is in format: XML ----------->>>> ". $json);
			$result['data'] = $json;
			// $result['data'] = json_decode(json_encode(new SimpleXMLElement($result["data"]), true));
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

	public function verifyURL($url) {
		// error_log(" [================= x =================] About to verify URL " . $url . "  " . $this->config['host']);
		if (isset($this->config['host'])) {
			// Throw an exception if configured prefix does not match handler host configuration.
			if (strpos($url, $this->config['host']) !== 0) {
				throw new Exception('This authroization handler is limited to only work on a specific host, and this was not the one...');
			}
		}
	}

	protected function setHeaders(&$headers) {
		return $headers;
	}

	public function get($method, $url, $options, $requestBody = null, $headers = array()) {

		// $result = array("status" => "ok");
		// $this->verifyURL($url);
		// // ($url, $headers = array(), $redir = true, $curl = false, $options = array()) {
		// $response = $this->rawget($url, array(), true, false, $options);
		// // error_log("Got data: " . var_export($rawdata, true)) ;
		// // $result = $this->decode($rawdata, $options);
		// return $response; // json_decode($rawdata, true);

		$redir = true;
		if (isset($options['followRedirects'])) {
			$redir = $options['followRedirects'];
		}

		$this->verifyURL($url);

		// $headers = array();
		$this->setHeaders($headers);



		$ch = curl_init();
	 	$ha = array();
	 	foreach($headers AS $k => $v) {
	 		$ha[] = $k . ': ' . $v;
	 	}

	 	if ($method !== 'GET') {
	 		
	 		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	 	}

	 	curl_setopt($ch, CURLOPT_HTTPHEADER, $ha);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,2); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 2); //timeout in seconds


		// TODO: Make an option for whether to validate ssl certiciates. Defualts to on.
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, ($redir ? 1 : 0));
		if ($requestBody !== null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
		}

		// Performing the HTTP Request. All preexec options must be set before this.
		$data = curl_exec($ch);

		// Obtaining info about the response.
		$code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
		$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);



		// if (true) { // debug
		// 	$debug = curl_getinfo($ch);
		// 	print_r($debug); exit;
		// }

		curl_close($ch);
	 
		$response = new HTTPResponse($code, $data);
		$response->contentType = $contentType;

		return $response;



	}


	public static function getClientFromConfig(APIProxy $proxy, Client $client) {
		// if (!is_array($config)) throw new Exception('Must call getClientWithConfig() with config array');

		$config = $proxy->get('proxy');

		switch($config['type']) {

			case "basic":
				return new HTTPClientBasic($config, $client);

			case "token":
				return new HTTPClientToken($config, $client);

			case "oauth2":
				return new HTTPClientOAuth2($config, $client);

			case "oauth1":
				return new HTTPClientOAuth1($config, $client);

			case "plain":
			default:
				return new HTTPClient($config, $client);
		}

	}

	public static function getClient(Client $client = null, $handler = 'plain') {


		$config = array("type" => "plain");
		if ($handler !== 'plain') {
			$config = $client->getAuthzHandler($handler);
		}

		if (empty($config["type"])) {
			throw new Exception("Handler configuration for [" . $handler . "] does not include the required [type] field.");
		}

		switch($config['type']) {

			case "basic":
				return new HTTPClientBasic($config, $client);

			case "token":
				return new HTTPClientToken($config, $client);

			case "oauth2":
				return new HTTPClientOAuth2($config, $client);

			case "oauth1":
				return new HTTPClientOAuth1($config, $client);

			case "plain":
			default:
				return new HTTPClient($config, $client);
		}


	}

}


