<?php


class HTTPClientBasic extends HTTPClient {
	

	protected function clientauth($u, $p) {
		return "Basic " . base64_encode($u . ':' . $p);
	}

	protected function setHeaders(&$headers) {

		if (empty($this->config["client_user"])) throw new Exception("missing handler configuration [client_user]");
		if (empty($this->config["client_secret"])) throw new Exception("missing handler configuration [client_secret]");

		$headers["Authorization"] = $this->clientauth($this->config["client_user"], $this->config["client_secret"]);
		$this->getUserAuthHeaders($headers);

		return parent::setHeaders($headers);
	}



}