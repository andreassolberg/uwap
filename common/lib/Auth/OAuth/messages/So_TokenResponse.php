<?php

class So_TokenResponse extends So_Response {
	public $access_token, $token_type, $expires_in, $refresh_token, $scope, $state;
	function __construct($message) {
		
		// Hack to add support for Facebook. Token type is missing.
		if (empty($message['token_type'])) $message['token_type'] = 'bearer';
		
		parent::__construct($message);
		$this->access_token		= So_Utils::prequire($message, 'access_token');
		$this->token_type		= So_Utils::prequire($message, 'token_type');
		$this->expires_in		= So_Utils::optional($message, 'expires_in');
		$this->refresh_token	= So_Utils::optional($message, 'refresh_token');
		$this->scope			= So_Utils::optional($message, 'scope');
		$this->state			= So_Utils::optional($message, 'state');
	}
}
