<?php

class So_AuthResponse extends So_Message {
	public $code, $state;
	function __construct($message) {
		parent::__construct($message);
		$this->code 		= So_Utils::prequire($message, 'code');
		$this->state		= So_Utils::optional($message, 'state');
	}
	function getTokenRequest($message = array()) {
		$message['code'] = $this->code;
		$message['grant_type'] = 'authorization_code';
		return new So_TokenRequest($message);
	}
}