<?php



// function http_parse_headers( $header, $hdrs ) {
// 	$key = null;
// 	$value = null;

// 	$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
// 	foreach( $fields as $field ) {
// 	    if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
// 	        $key = strtolower(preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1]))));
// 	        $value = trim($match[2]);

// 	        if (isset($key)) {
// 	        	if (!isset($hdrs[$key])) {
// 	        		$hdrs[$key] = array();
// 	        	}
// 	        	$hdrs[$key][] = $value;
// 	        }

// 	    }
// 	}

// }




class So_Utils {
	
	
	static function spacelist($arg) {
		if ($arg === null) return null;
		return explode(' ', $arg);
	}
	
	static function geturl() {
		$url = ((!empty($_SERVER['HTTPS'])) ? 
			"https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : 
			"http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
		return $url;
	}
	
	// Found here:
	// 	http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
	static function gen_uuid() {
	    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	        // 32 bits for "time_low"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

	        // 16 bits for "time_mid"
	        mt_rand( 0, 0xffff ),

	        // 16 bits for "time_hi_and_version",
	        // four most significant bits holds version number 4
	        mt_rand( 0, 0x0fff ) | 0x4000,

	        // 16 bits, 8 bits for "clk_seq_hi_res",
	        // 8 bits for "clk_seq_low",
	        // two most significant bits holds zero and one for variant DCE1.1
	        mt_rand( 0, 0x3fff ) | 0x8000,

	        // 48 bits for "node"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	    );
	}
	
	public static function optional($message, $key) {
		if (empty($message[$key])) return null;
		return $message[$key];
	}
	public static function prequire($message, $key, $values = null, $multivalued = false) {
		if (empty($message[$key])) {
			throw new So_Exception('invalid_request', 'Message does not include prequired parameter [' . $key . ']');
		}
		if (!empty($values)) {
			if ($multivalued) {
				$rvs = explode(' ', $message[$key]);
				foreach($rvs AS $v) {
					if (!in_array($v, $values)) {
						throw new So_Exception('invalid_request', 'Message parameter [' . $key . '] does include an illegal / unknown value.');
					}					
				}
			}
			if (!in_array($message[$key], $values)) {
				throw new So_Exception('invalid_request', 'Message parameter [' . $key . '] does include an illegal / unknown value.');
			}
		} 
		return $message[$key];
	}
}
