<?php

class Authentication {

	protected $storage;
	protected $server;
	protected $auth;

	function __construct() {
		$this->storage = new So_StorageServerUWAP();
		$this->server  = new So_Server($this->storage);
		// $this->auth = new AuthBase();

		$this->auth = new Authenticator();
	}



	
}