<?php


class People {
	protected $store, $userid;

	protected $config = array(
		'bind' => 'ldap://ldap.uninett.no',
		'base' => 'cn=people,dc=uninett,dc=no',
	);

	protected $amap = array(
		'displayname' => 'name',
		'edupersonprincipalname' => 'userid',
	);

	public function __construct($userid) {

		$this->userid = $userid;
		$this->store = new UWAPStore();

	}

	public function map($k) {
		if (isset($this->amap[$k])) {
			return $this->amap[$k];
		}
		return $k;
	}

	public function query($query) {




		$ds = ldap_connect($this->config['bind']) or die("Could not connect to LDAP");
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
		ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything

		$filter='(|(sn=*' . $query . '*)(givenname=*' . $query . '*))';
		// $justthese = array("ou", "sn", "givenname", "mail");

		$displayattrs = array('o', 'edupersonprincipalname', 'displayname', 'jpegphoto', 'mail');
		$searchattrs = array('edupersonprincipalname', 'displayname', 'mail');

		$filterstrs = array();
		foreach($searchattrs AS $sa) {
			$filterstrs[] = '(' . $sa . '=*' . $query . '*)';
		}
		$filter = '(|' . join('', $filterstrs) . ')';


		$sr=ldap_search($ds, $this->config['base'], $filter, $displayattrs);
		$info = ldap_get_entries($ds, $sr);

		$result = array();

		for($i = 0; $i < $info['count']; $i++) {

			$entry = array();
	
			for($j = 0; $j < $info[$i]['count']; $j++) {

				$key = $info[$i][$j];
				$values = array();

				for($k = 0; $k < $info[$i][$key]['count']; $k++) {
					if ($key === 'jpegphoto') {
						$values[] = base64_encode($info[$i][$key][$k]);
					} else {
						$values[] = $info[$i][$key][$k];
					}
				}
				$entry[$this->map($key)] = $values;

			}			
			$result[] = $entry;
		}


		// echo '<pre>';
		// print_r($result);
		// echo '</pre>';
		// exit;

		return $result;
	}

}



?>