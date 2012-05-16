<?php


class ParentMessenger {
	
	static function send($message) {
		header("Content-Type: text/html; charset: utf-8");
		require_once("../templates/parentmessage.php"); exit;
	}

}