<?php


class HTTPClientBasic extends HTTPClient {
	

	protected function clientauth($u, $p) {
		return "Basic " . base64_encode($u . ':' . $p);
	}

	public function get($url, $options) {
		$result = array("status" => "ok");

		if (empty($this->config["client_user"])) throw new Exception("missing handler configuration [client_user]");
		if (empty($this->config["client_secret"])) throw new Exception("missing handler configuration [client_secret]");

		$headers = array(
			"Authorization" => $this->clientauth($this->config["client_user"], $this->config["client_secret"])
		);

		$this->getUserAuthHeaders($headers);
		$this->verifyURL($url);

		// if (isset($this->config['user']) && $this->config['user'] === true) {
		// 	$headers["UWAP-UserID"] = $this->userauth();
		// }
		// if (isset($this->config['groups']) && $this->config['groups'] === true) {
		// 	$headers["UWAP-Groups"] = $this->groups();
		// }

		error_log("HTTPClientBasic headers:" .  var_export($headers, true));

		$result["data"] = $this->rawget($url, $headers, true, false, $options);
		// ($url, $headers = array(), $redir = true, $curl = false, $options = array()) {
		// 
		// $result = $this->decode($result, $options);
		return $result;
	}

}