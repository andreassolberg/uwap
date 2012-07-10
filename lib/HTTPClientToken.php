<?php


class HTTPClientToken extends HTTPClientUserAuth {
	

	public function get($url, $options) {
		$result = array("status" => "ok");

		if (empty($this->config["token_hdr"])) throw new Exception("missing handler configuration [token_hdr]");
		if (empty($this->config["token_val"])) throw new Exception("missing handler configuration [token_val]");

		$headers = array($this->config["token_hdr"] => $this->config["token_val"]);

		$redir = true;
		// if (isset($this->config['followRedirects']) && $this->config['followRedirects'] === false) {
		// 	$redir = false;
		// }
		if (isset($options['followRedirects']) && $options['followRedirects'] === false) {
			$redir = false;
		}

		$curl = false;
		// if (isset($this->config['curl']) && $this->config['curl'] === true) {
		// 	$curl = true;
		// }
		if (isset($options['curl']) && $options['curl'] === true) {
			$curl = true;
		}

		if (isset($this->config['userinfo'])) {
			error_log('Config: ' . json_encode($this->config['userinfo']));
			if (isset($this->config['userinfo']['userid']) && $this->config['userinfo']['userid'] === true) {
				$headers["UWAP-UserID"] = $this->userauth();
			}
		}

		error_log('Handler: ' . json_encode($options['handler']));
		error_log('URL: ' . json_encode($options['url']));
		error_log('Headers: ' . json_encode($headers));
		error_log('Redirect?: ' . json_encode($redir));
		error_log('Options: ' . json_encode($options));

		// ($url, $headers = array(), $redir = true, $curl = false, $options = array()) {
		$result["data"] = $this->rawget($url, $headers, $redir, $curl, $options);

		$result = $this->decode($result, $options);
		return $result;
	}

}