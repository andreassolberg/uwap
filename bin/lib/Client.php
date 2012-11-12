<?php


class Client {


	protected $endpointToken = 'http://core.app.bridge.uninett.no/api/oauth/token';
	protected $u;
	protected $p;
	protected $token;

	public function __construct($u, $p) {
		$this->u = $u; 
		$this->p = $p;

		$this->obtainToken();
	}

	protected function obtainToken() {
		// echo "About to obtain token\n";
		$data = array("grant_type" => 'client_credentials');
		$this->token = $this->basic_post($this->endpointToken, $data);
		// echo "Token is obtained\n";
		// echo "Token\n\n";
		// print_r($this->token);
		// echo  "\n\n";

	}

	protected function basic_post($url, $data = null) {
		$ch = curl_init();

		$data = http_build_query($data);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	 	// curl_setopt($ch, CURLOPT_HTTPHEADER, $ha);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $this->u . ':' . $this->p);
		
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
	 
		$ret = json_decode($output, true);

		// echo ("Received ");
		// print_r($output);

		return $ret;
	}
	
	protected function getAuthHeader() {
		// echo "About to use token";
		if (empty($this->token) || empty($this->token['access_token'])) throw new Exception('Cannot perform call without an access token');
		return 'Authorization: Bearer ' . $this->token['access_token'];
	}

	public function oauth_http($url, $data = null) {
		$ch = curl_init();
		$headers = array($this->getAuthHeader());

		if (!empty($data)) {

			$postdata = json_encode($data);
			$headers[] = 'Content-Type: application/json; charset: utf-8';
			$headers[] = 'Content-Length: ' . strlen($postdata);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);	
			// curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		} else {
			curl_setopt($ch, CURLOPT_POST, 0);
		}
		
	 	// curl_setopt($ch, CURLOPT_HTTPHEADER, $ha);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		// curl_setopt($ch, CURLOPT_USERPWD, $this->u . ':' . $this->p);
		
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		// echo "Raw out \n" . $output . "\n\n";
	 
		$ret = json_decode($output, true);

		if ($ret['data'] === false) {
			echo "Already published\n";
		} else {
			echo "Publishing new.\n";
		}

		return $ret;
	}


}