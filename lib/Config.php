<?php


class Config {
	
	private $subid;
	private $config;
	
	function __construct($id = null) {

		if ($id === null) {
			$this->subid = Utils::getSubID();			
		} else {
			$this->subid = $id;
		}
		
		$file = dirname(dirname(__FILE__)) . '/config/' . $this->subid . '.js';
		// echo 'Parsing: ' .file_get_contents($file);
		
		$this->config = json_decode(file_get_contents($file), true);
		if ($this->config === null) {
			throw new Exception('Could not parse config [' . $file . ']');
		}
	}
	
	public function getID() {
		return $this->subid;
	}
	
	public function getConfig() {
		return $this->config;
	}

	public function getValue($key, $default = null) {
		if (isset($this->config[$key])) return $this->config[$key];
		return $default;
	}

	public function getHandlerConfig($handler) {

		// echo "getHandlerConfig($handler)"; print_r($this->config);

		if (empty($this->config["handlers"])) return null;
		if (!isset($this->config["handlers"][$handler])) return null;

		$pc = $this->config["handlers"][$handler];
		if ($pc["type"] === "oauth2") {
			$pc["client_credentials"]["redirect_uri"] = 'http://' . $this->subid . '.app.bridge.uninett.no/_/api/callbackOAuth2.php';
		}
		return $pc;
	}

	
}