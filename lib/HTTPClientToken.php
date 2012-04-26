<?php


class HTTPClientToken extends HTTPClientUserAuth {
	
	public function __construct($config) {
		parent::__construct($config);
	}

	public function get($url, $options) {
		$result = array("status" => "ok");

		if (empty($this->config["token_hdr"])) throw new Exception("missing handler configuration [token_hdr]");
		if (empty($this->config["token"])) throw new Exception("missing handler configuration [token]");

		$headers = array($this->config["token_hdr"] => $this->config["token"]);

		$redir = true;
		if (isset($this->config['followRedirects']) && $this->config['followRedirects'] === false) {
			$redir = false;
		}

		$curl = false;
		if (isset($this->config['curl']) && $this->config['curl'] === true) {
			$curl = true;
		}


		if (isset($this->config['user']) && $this->config['user'] === true) {
			$headers["UWAP-UserID"] = $this->userauth();
		}

		error_log(var_export($headers, true));

		$result["data"] = $this->rawget($url, $headers, $redir, $curl);
		$result = $this->decode($result, $options);
		return $result;
	}

}