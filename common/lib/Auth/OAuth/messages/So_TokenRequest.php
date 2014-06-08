<?php

class So_TokenRequest extends So_AuthenticatedRequest {
	public $grant_type, $code, $redirect_uri;
	function __construct($message) {
		parent::__construct($message);
		$this->grant_type		= So_Utils::prequire($message, 'grant_type', array('authorization_code', 'refresh_token', 'client_credentials'));
		$this->code 			= So_Utils::optional($message, 'code');
		$this->redirect_uri		= So_Utils::optional($message, 'redirect_uri');
	}

}
