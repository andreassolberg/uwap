<?php

/**
* Represents a list of SCIMResources
*/
class SCIMListResponse {


	protected $resources = array();
	
	function __construct($resources = null) {
		$this->groupTypes = array();
		if (!empty($resources)) {
			$this->addResources($resources);
		}
	}

	public function addResources($resources) {
		foreach($resources AS $r) {
			$this->addResource($r);
		}
	}

	public function addGroupType(SCIMResourceGroupType $g) {
		$this->groupTypes[] = $g;
	}


	public function addResource(SCIMResource $r) {
		$this->resources[] = $r;
	}

	public function getJSON() {

		$res = array(
			'Resources' => array(),
			'GroupTypes' => array()
		);

		foreach($this->resources AS $r) {
			$res['Resources'][] = $r->getJSON();	
		}
		foreach($this->groupTypes AS $r) {
			$res['GroupTypes'][] = $r->getJSON();	
		}

		$res['schemas'] = array("urn:scim:schemas:core:2.0:ListResponse");
		$res['totalResults'] = count($this->resources);

		return $res;
	}



}