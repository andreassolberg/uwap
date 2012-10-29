<?php


class Mailer {


	var $adr;
	var $body;

	function __construct($adr, $body = null) {
		$this->adr = $adr;
		$this->body = $body;
	}

	function send() {

		$subject = 'UNINETT Feed Updates';
		$headers = 'From: notifications@uwap.org' . "\r\n" .
			'Reply-To: andreas.solberg@uninett.no' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();

		mail($this->adr, $subject, $this->body, $headers);

	}


	function setNotifications($notifications) {

		$str = '';
		foreach($notifications AS $n) {
			$str .= '  - ' . $n['summary'] . "\n";
		}

		$this->body = 'Important updates waits for you on UNINETT Feeds.

Here is a list of recent updates:

' . $str . '

View these messages at: https://feed.uwap.org#!/


To set your mail notifications preferences, please visit:
	https://feed.uwap.org/#!/usersettings

UWAP is an experimental service. Contact andreas@uninett.no for questions or 
comments regarding this service.

Contact andreas@uninett.no if you want to be removed from the list of test users
that receives status updates.';

	}


}