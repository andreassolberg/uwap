<?php


class Consent {
	
	protected $config;
	protected $store;

	function __construct() {
		$this->config = new Config();
		$this->store = new UWAPStore():
		$this->auth = new Auth();
	}



}