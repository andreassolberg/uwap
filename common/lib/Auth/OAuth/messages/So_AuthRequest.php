<?php



class So_AuthRequest extends So_Request {
	public $response_type, $client_id, $redirect_uri, $scope, $state;
	function __construct($message) {
		parent::__construct($message);
		$this->response_type	= So_Utils::prequire($message, 'response_type', array('code', 'token'), true);		
		$this->client_id 		= So_Utils::prequire($message, 'client_id');
		$this->redirect_uri		= So_Utils::optional($message, 'redirect_uri');
		$this->scope			= So_Utils::spacelist(So_Utils::optional($message, 'scope'));
		$this->state			= So_Utils::optional($message, 'state');
	}
	
	function asQS() {
		$qs = array();
		foreach($this AS $key => $value) {
			if (empty($value)) continue;
			if ($key === 'scope') {
				$qs[] = urlencode($key) . '=' . urlencode(join(' ', $value));
				continue;
			} 
			$qs[] = urlencode($key) . '=' . urlencode($value);
		}
		return join('&', $qs);
	}
	
	function getResponse($message) {
		$message['state'] = $this->state;
		return new So_AuthResponse($message);
	}
}
