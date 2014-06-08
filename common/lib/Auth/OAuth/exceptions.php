<?php





class So_ExpiredToken extends Exception {}
class So_AuthorizationRequired extends Exception {
	public $scopes;
	public $client_id;
}
class So_InsufficientScope extends Exception {}


class So_RedirectException extends Exception {
	protected $url;
	function __construct($url) {
		$this->url = $url;
	}
	function getURL() {
		return $this->url;
	}
}





class So_Exception extends Exception {
	protected $code, $state;
	function __construct($code, $message, $state = null) {
		parent::__construct($message);
		$this->code = $code;
		$this->state = $state;
	}
	function getResponse() {
		$message = array('error' => $this->code, 'error_description' => $this->getMessage() );
		if (!empty($this->state)) $message['state'] = $this->state;
		$m = new So_ErrorResponse();
	}
}



class So_UnauthorizedRequest extends So_Exception {

}


class So_InvalidResponse extends So_Exception {
	public $raw;
}