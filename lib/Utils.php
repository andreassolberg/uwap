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
		self::validateID($subhost);
		return $subhost;
	}

	public static function validateID($id) {
		if (preg_match('/^([a-zA-Z0-9]+)$/', $id, $matches)) {
			return true;
		}
		throw new Exception('Invalid characters in provided app ID');
	}

	
}