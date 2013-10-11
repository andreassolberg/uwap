<?php



/*
 * test page. DELETE this at some point in time
 */

require_once('../lib/autoload.php');


// $config = new Config();
// $subhost = $config->getID();


// $res = User::getByUserID('andreas@uninett.no');

// $res->set('name', 'ANDREAS Solberg');
// $res->store();


// $res = User::getByUserID('andreas@uninett.no');



$a = new Authenticator();
$a->req(false, true);
$res = $a->getUser();

// $res = User::fromAttributes(array(

// 	'displayName' => array('Andreas S'),
// 	'eduPersonPrincipalName' => array('andreas@uninett.no'),
// 	'mail' => array('andreas@solweb.no')

// ));

// $data = new AdHocGroup(array(
// 	'id' => 'foo',
// 	'title' => 'bar'
// ));

header('Content-type: text/plain; charset=utf8');
$groups = $res->getGroups();
foreach($groups AS $g) {
	print_r($g->getJSON());
}
// print_r($data);

// $auth = new Auth();
// $auth->req();

// $result = array();
// $result['status'] = 'ok';

// $store = new UWAPStore();

// $res = $store->queryOne("appdata-test", $auth->getRealUserID(), array("bool" => true));





