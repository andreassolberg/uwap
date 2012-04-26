<?php


class HTTPClientBasic extends HTTPClientUserAuth {
	
	public function __construct($config) {
		parent::__construct($config);
	}

	public function get($url, $options) {
		$result = array("status" => "ok");

		if (empty($this->config["client_user"])) throw new Exception("missing handler configuration [client_user]");
		if (empty($this->config["client_password"])) throw new Exception("missing handler configuration [client_password]");

		$headers = array(
			"Authorization" => $this->clientauth($this->config["client_user"], $this->config["client_password"])
		);

		if (isset($this->config['user']) && $this->config['user'] === true) {
			$headers["UWAP-UserID"] = $this->userauth();
		}

		error_log(var_export($headers, true));

		$result["data"] = $this->rawget($url, $headers);
		$result = $this->decode($result, $options);
		return $result;
	}

}