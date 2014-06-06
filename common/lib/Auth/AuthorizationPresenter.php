<?php

/**
 * This is a helper class that may provide a data structure to represent information to the end user on
 * the authorization grant page, based upon scopes as input.
 *
 * This class is meant to provide output for the   auth/templates/oauthgrant.mustache 
 * template.
 */
class AuthorizationPresenter {

	protected $scopes;

	function __construct($scopes) {

		$this->scopes = $scopes;

	}


	function getData() {

		$toProcess = array();
		foreach($this->scopes AS $s) {
			$toProcess[$s] = 1;
		}

		$data = array();
		$data['meta'] = array(
			'allScopes' => $this->scopes
		);

		if (isset($toProcess['feedread']) || isset($toProcess['feedwrite'])) {

			$data['feed'] = array();
			if (isset($toProcess['feedread']) && isset($toProcess['feedwrite'])) {
				$data['feed']['both'] = 1;
			} else if (isset($toProcess['feedread'])) {
				$data['feed']['read'] = 1;
			} else if (isset($toProcess['feedwrite'])) {
				$data['feed']['write'] = 1;
			}

			unset($toProcess['feedread']);
			unset($toProcess['feedwrite']);

		}

		$data['unknown'] = array('items' => array('foo', 'bar'));
		$knownPerms = array('userinfo', 'groupmanage', 'longterm');

		foreach($toProcess AS $s => $v) {
			if (in_array($s, $knownPerms)) {
				$data[$s] = 1;
			} else {
				$data['unknown']['items'][] = $s;
			}
			unset($toProcess[$s]);
		}
		if (empty($data['unknown']['items'])) {
			unset($data['unknown']['items']);
		}


		return $data;

	}


}