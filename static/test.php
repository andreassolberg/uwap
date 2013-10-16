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
$user = $a->getUser();


// $res = User::fromAttributes(array(

// 	'displayName' => array('Andreas S'),
// 	'eduPersonPrincipalName' => array('andreas@uninett.no'),
// 	'mail' => array('andreas@solweb.no')
// 	
// ));

// $data = new AdHocGroup(array(
// 	'id' => 'foo',
// 	'title' => 'bar'
// ));

header('Content-type: text/plain; charset=utf8');


$groupconnector = new GroupConnector($user);
// $data2 = $groupconnector->getByID('uwap:grp-ah:da5832bf-453f-4333-abc8-36b83579d9df');
//$data = $groupconnector->getByID('uwap:grp:uninett:org:orgunit:AVD-U2');

// $data = $groupconnector->getByID('uwap:grp-ah:10383ba6-44b0-4fdf-8ecb-639bda37e9b7');

// $res = $groupconnector->addGroup(array(
// 	'title' => 'One new group',
// 	'description' => 'jalla',
// 	'listable' => true
// ));


$res = $groupconnector->peopleQuery('uninett.no', 'lar');

// $data3 = $groupconnector->getGroups();
// foreach($data3 AS $g) {
	// print_r($g->getJSON());
// }

// $res = $groupconnector->remove('uwap:grp-ah:a50420f9-593a-41a1-ac55-0e0a42032dca');


// $res = $groupconnector->addMember('uwap:grp-ah:10383ba6-44b0-4fdf-8ecb-639bda37e9b7', array(
// 	'userid' => 'test-123@foo.com',
// 	'name' => 'Test 123',
// ));

// $res = $groupconnector->updateMember('uwap:grp-ah:10383ba6-44b0-4fdf-8ecb-639bda37e9b7', 'test-123@foo.com', 'admin');
// $res = $groupconnector->removeMember('uwap:grp-ah:10383ba6-44b0-4fdf-8ecb-639bda37e9b7', 'test-123@foo.com');

// $res = $groupconnector->unsubscribe('uwap:grp-ah:b24653f2-526d-4c28-97b9-160caa678681');
// $res = $groupconnector->unsubscribe('');


// $res = $groupconnector->update(
// 	'uwap:grp-ah:7ea1c555-583c-4a1f-9ae2-1273b0c66ebc',
// 	array(
// 		'listable' => true,
// 	)
// );

print_r($res);
// print_r($res);

// $res = $groupconnector->addGroup(array(

// 	'title' => 'TESTGROUP99',
// 	'description' => 'baby baby baby',
// 	'listable' => true,

// ));


// $data = Group::getByID('uwap:grp-ah:da5832bf-453f-4333-abc8-36b83579d9df');
// print_r($data3);
// print_r($data->getJSON());


// $groups = $res->getGroups();

// echo "poot3";

// print_r($data);

// $auth = new Auth();
// $auth->req();

// $result = array();
// $result['status'] = 'ok';

// $store = new UWAPStore();

// $res = $store->queryOne("appdata-test", $auth->getRealUserID(), array("bool" => true));





