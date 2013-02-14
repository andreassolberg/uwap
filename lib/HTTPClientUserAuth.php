<?php


abstract class HTTPClientUserAuth extends HTTPClient {
	

	// protected function clientauth($u, $p) {
	// 	return "Basic " . base64_encode($u . ':' . $p);
	// }

	// protected function groups() {
	// 	$groups = array('testgroups');
	// 	return join(',', $groups);
	// }

	// protected function userauth() {

		// require_once('../simplesamlphp/lib/_autoload.php');

		// $as = new SimpleSAML_Auth_Simple('default-sp');

		// if (!$as->isAuthenticated()) {
		// 	throw new Exception("user is not authenticated");
		// }
		// $attr = $as->getAttributes();
		// if (empty($attr["eduPersonPrincipalName"])) {
		// 	throw new Exception("could not obtain userid of authenticated user");
		// }

		// if (empty($this->userid)) {
		// 	throw new Exception("REST handler requires an user authenticated token to perform operation.");
		// }
		// $userid = $this->userid;
		// return $userid;

		// return $attr["eduPersonPrincipalName"][0];
	// }


}