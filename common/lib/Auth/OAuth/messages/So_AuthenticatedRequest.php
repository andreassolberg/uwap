<?php


abstract class So_AuthenticatedRequest extends So_Request {
	public $client_id;
	protected $client_secret;
	function __construct($message) {
		parent::__construct($message);
		$this->client_id		= So_Utils::optional($message, 'client_id');
		$this->client_secret		= So_Utils::optional($message, 'client_secret');
	}
	function setClientCredentials($u, $p) {
		error_log('setClientCredentials ('  . $u. ',' . $p. ')');
		$this->client_id = $u;
		$this->client_secret = $p;
	}
	function getAuthorizationHeader() {
		if (empty($this->client_id) || empty($this->client_secret)) throw new Exception('Cannot authenticate without username and passwd');
		return 'Authorization: Basic ' . base64_encode($this->client_id . ':' . $this->client_secret);
	}
	function checkCredentials($u, $p) {
		if ($u !== $this->client_id) throw new So_Exception('invalid_grant', 'Invalid client credentials');
		if ($p !== $this->client_secret) throw new So_Exception('invalid_grant', 'Invalid client credentials');
		// error_log("Checking credentials [" . $u . "] [" . $p . "]");
		// error_log("Checking credentials Client id is  " . $this->client_id);
		// error_log("Checking credentials Client secret is  " . $this->client_secret);

	}
	
	function parseServer($server) {
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			$this->client_id = $_SERVER['PHP_AUTH_USER'];
		}
		if (isset($_SERVER['PHP_AUTH_PW'])) {
			$this->client_secret = $_SERVER['PHP_AUTH_PW'];
		}
		error_log('Authenticated request with [' . $this->client_id . '] and [' . $this->client_secret . ']');
	}
	
	protected function getContentType($hdrs) {
		foreach ($hdrs AS $h) {
			if (preg_match('|^Content-[Tt]ype:\s*text/plain|i', $h, $matches)) {
				return 'application/x-www-form-urlencoded';
			} else if (preg_match('|^Content-[Tt]ype:\s*application/x-www-form-urlencoded|i', $h, $matches)) {
				return 'application/x-www-form-urlencoded';
			}
		}
		return 'application/json';
	}
	
	protected function getStatusCode($hdrs) {
		$explode = explode(' ', $hdrs[0]);
		return $explode[1];
	}
	
	public function post($endpoint) {
		
		$postdata = $this->asQS();		
		error_log('Posting typically a token request: ' .var_export(array(
		 		'endpoint' => $endpoint,
				'header' => $this->getAuthorizationHeader(),
				'body' => $postdata,
		 	), true));
		So_log::debug('Posting typically a token request: ',
		 	array(
		 		'endpoint' => $endpoint,
				'header' => $this->getAuthorizationHeader(),
				'body' => $postdata,
		 	));
		
		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . 
				// '',
				$this->getAuthorizationHeader() . "\r\n",
		        'content' => $postdata
		    )
		);
		$context  = @stream_context_create($opts);

		error_log("Posting to ednpoint: " . $endpoint);
		$result = @file_get_contents($endpoint, false, $context);
		$statuscode = $this->getStatusCode($http_response_header);
		
		if ((string)$statuscode !== '200') {
			
			So_log::error('When sending a token request, using a provided code, the returned status code was not 200 OK.',
				array(
					'resultdata' => $result,
					'headers' => $http_response_header
				)
			);
			
			throw new Exception('When sending a token request, using a provided code, the returned status code was not 200 OK.');
		}
		$ct = $this->getContentType($http_response_header);
		
		if ($ct === 'application/json') {

			error_log('RESPONSE WAS: '. var_export($result, true));

			$resultobj = json_decode($result, true);
			if ($resultobj === null) {
				$e = new So_InvalidResponse('na', 'Statuscode 200, but content was invalid JSON, on Token endpoint.');
				$e->raw = $result;
				throw $e;
			}
			
		} else if ($ct === 'application/x-www-form-urlencoded') {
			
			$resultobj = array();
			parse_str(trim($result), $resultobj);
			
		} else {
			// cannot be reached, right now.
			throw new Exception('Invalid content type in Token response.');
		}
		error_log("Done. Output was: " . $result );
		So_log::debug('Successfully parsed the Token Response body',array('response' => $resultobj));
		return $resultobj;
	}
	
}