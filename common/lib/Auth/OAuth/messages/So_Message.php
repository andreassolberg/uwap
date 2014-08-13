<?php



class So_Message {
	function __construct($message) {	
	}
	function asQS() {
		$qs = array();
		foreach($this AS $key => $value) {
			if (empty($value)) continue;
			$qs[] = urlencode($key) . '=' . urlencode($value);
		}
		return join('&', $qs);
	}

	public function getRedirectURL($endpoint, $hash = false) {
		if ($hash) {
			$redirurl = $endpoint . '#' . $this->asQS();
		} else {
			if (strstr($endpoint, "?")) {
				$redirurl = $endpoint . '&' . $this->asQS();
			} else {
				$redirurl = $endpoint . '?' . $this->asQS();
			}
			
		}
		return $redirurl;
	}
	
	public function sendRedirect($endpoint, $hash = false) {
		$redirurl = $this->getRedirectURL($endpoint, $hash);		
		header('Location: ' . $redirurl);
		exit;
	}

	public function sendBodyJSON() {
		header('Content-Type: application/json;charset=UTF-8');

		$body = array();
		foreach($this AS $key => $value) {
			if (empty($value)) continue;
			$body[$key] = $value;
		}

		// echo json_encode($body);
		echo json_encode($body, JSON_PRETTY_PRINT); 
		exit;
	}

	public function sendBodyForm() {
		// header('Content-Type: application/json; charset=utf-8');
		header('Content-Type: application/x-www-form-urlencoded');

		$body = array();
		foreach($this AS $key => $value) {
			if (empty($value)) continue;
			$body[$key] = $value;
		}

		// echo json_encode($body);
		echo http_build_query($body);
		exit;
	}
	

	
	public function post($endpoint) {
		error_log('posting to endpoint: ' . $endpoint);
		$postdata = $this->asQS();
		
		error_log('Sending body: ' . $postdata);
		
		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded' . "\r\n",
		        'content' => $postdata
		    )
		);
		$context  = stream_context_create($opts);

		$result = file_get_contents($endpoint, false, $context);
		
		$resultobj = json_decode($result, true);
		

		return $resultobj;
	}
}