<?php

class So_ErrorResponse extends So_Response {
	public $error, $error_description, $error_uri, $state;
	function __construct($message) {
		parent::__construct($message);
		$this->error 				= So_Utils::prequire($message, 'error', array(
			'invalid_request', 'access_denied', 'invalid_client', 'invalid_grant', 'unauthorized_client', 'unsupported_grant_type', 'invalid_scope'
		));
		$this->error_description	= So_Utils::optional($message, 'error_description');
		$this->error_uri			= So_Utils::optional($message, 'error_uri');
		$this->state				= So_Utils::optional($message, 'state');
	}

	public function sendBodyJSON() {


		if($this->error === 'invalid_client') {
			http_response_code(401);
		} else if ($this->error === 'invalid_client') {
			http_response_code(401);
		} else {
			http_response_code(400);
		}

		parent::sendBodyJSON();

	}
}