<?php


class HTTPClientBasic extends HTTPClientUserAuth {
	


	public function get($url, $options) {
		$result = array("status" => "ok");

		if (empty($this->config["client_user"])) throw new Exception("missing handler configuration [client_user]");
		if (empty($this->config["client_secret"])) throw new Exception("missing handler configuration [client_secret]");

		$headers = array(
			"Authorization" => $this->clientauth($this->config["client_user"], $this->config["client_secret"])
		);

		if (isset($this->config['user']) && $this->config['user'] === true) {
			$headers["UWAP-UserID"] = $this->userauth();
		}

		error_log("HTTPClientBasic headers:" .  var_export($headers, true));

		$result["data"] = $this->rawget($url, $headers, true, false, $options);
		// ($url, $headers = array(), $redir = true, $curl = false, $options = array()) {
		// 
		$result = $this->decode($result, $options);
		return $result;
	}

}