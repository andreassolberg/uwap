<?php


class HTTPClientToken extends HTTPClient {
	

	protected function setHeaders(&$headers) {

		if (empty($this->config["token_hdr"])) throw new Exception("missing handler configuration [token_hdr]");
		if (empty($this->config["token_val"])) throw new Exception("missing handler configuration [token_val]");


		$headers[$this->config["token_hdr"]] = $this->config["token_val"];
		$this->getUserAuthHeaders($headers);

		return parent::setHeaders($headers);
	}


}