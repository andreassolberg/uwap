<?php


class Utils {
	
	public static function getSubID() {
		$commonhost = '.app.bridge.uninett.no';
		$subhost = null;

		if (preg_match('/^([a-zA-Z0-9]+).app.bridge.uninett.no$/', $_SERVER['HTTP_HOST'], $matches)) {
			$subhost = $matches[1];
		} else {
			throw new Exception('Invalid host name');
		}
		return $subhost;
	}

	
}