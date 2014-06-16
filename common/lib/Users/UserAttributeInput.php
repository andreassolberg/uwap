<?php


class UserAttributeInput {


	protected $attributes, $idp;
	public $accountinfo, $accountId, $complexId;

	function __construct($attributes, $idp) {
		$this->attributes = $attributes;
		$this->idp = $idp;

		$this->interpretAccountId();
		$this->interpretAccountInfo();
	}


	/**
	 * From the set of provided attributeinput, 
	 * @return [type] [description]
	 */
	private function interpretAccountId() {
		$this->accountId = null;

		$map = GlobalConfig::getValue('useridattrs', null, true);
		$uid = new ComplexUserID();

		foreach($map AS $keydef) {
			if (isset($this->attributes[$keydef['attribute']])) {
				$uid->add($keydef['key'], $this->attributes[$keydef['attribute']][0]);
				if ($this->accountId === null) {
					$this->accountId = $keydef['key'] . ':' . $this->attributes[$keydef['attribute']][0];
				}
			}
		}

		if (!$uid->isValid()) {
			throw new Exception('Could not create a complex ID from the provided attribute set.');
		}
		if ($this->accountId === null) {
			throw new Exception('Could not find a proper account ID for the provided attribute set.');	
		}

		$this->complexId = $uid;
	}


	private function interpretAccountInfo() {
		
		$this->accountinfo = array();

		$this->accountinfo['idp'] = $this->idp;

		$map = GlobalConfig::getValue('attributeMap', null, true);

		/*
		 * Pick the attributes defined in the configuration mapping, and store the attributes in 
		 * a new attribute array $userattr
		 */
		foreach($map AS $key => $akey) {
			if (isset($this->attributes[$akey])) {
				$this->accountinfo[$key] = $this->attributes[$akey][0];	
			}
		}
		// $groups = self::groupsFromAttributes($attributes);
		

		/*
		 * The user field customdata can contain a set of custom data to be used for other purposes
		 * such as generating groups from it. 
		 */
		$collectUserdata = GlobalConfig::getValue('customAttributes', null, true);
		$this->accountinfo['custom'] = array();
		foreach($collectUserdata AS $key) {
			if (isset($this->attributes[$key])) {
				$this->accountinfo['custom'][$key] = $this->attributes[$key];	
			}
		}
	}





}