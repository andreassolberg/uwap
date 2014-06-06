<?php

require_once(dirname(dirname(__FILE__)) . '/lib/autoload.php');


class UsersTest extends PHPUnit_Framework_TestCase {
    

    protected $user1 = array(
		'eduPersonPrincipalName' => array('testuser1@uninett.no'),
		'displayName' => array('Andreas _TestUser1_ Solberg'),
		// 'norEduPersonNIN' => array('101080'),
    );
    protected $user2 = array(
		'eduPersonPrincipalName' => array('testuser2@uninett.no'),
		'displayName' => array('Andreas _TestUser2_ Solberg'),
		'norEduPersonNIN' => array('101080'),
    );

    protected $user11 = array(
		'eduPersonPrincipalName' => array('testuser11@uninett.no'),
		'displayName' => array('Andreas _TestUser11_ Solberg'),
		'mail' => array('test@uninett.no'),
    );
    protected $user3 = array(
		'eduPersonPrincipalName' => array('testuser1@uninett.no'),
		'displayName' => array('Andreas _TestUser3_ Solberg'),
		'norEduPersonNIN' => array('101080'),
		'mail' => array('test@uninett.no'),
    );

    protected $user1x = array(
		'eduPersonPrincipalName' => array('testuser1@uninett.no'),
		'displayName' => array('Andreas _TestUser1x_ Solberg'),
		// 'norEduPersonNIN' => array('101080'),
    );

	public function testCreateAppOwner() {

		// TODO: this shuold not be neccessary

		$dir = new UserDirectory();

		$user = $dir->getUserFromAttributes(array(
			'eduPersonPrincipalName' => array('andreas@uninett.no'),
			'displayName' => array('Andreas Åkre Solberg'),
		));

		$user->set('userid', 'andreas@uninett.no');
		$user->store();


	}




	public function testUserCreate() {


		$dir = new UserDirectory();

		$user = $dir->getUserFromAttributes($this->user1);
    	$this->assertInstanceOf('User', $user, 'Should be able to create a user1 object from attributes');
		$userid = $user->get('userid');

    	$res = $dir->lookupKey('feide:testuser1@uninett.no');
    	$this->assertCount(1, $res, 'Should only find one user');
    	$this->assertInstanceOf('User', $res[0], 'Lookup should return a User object');
    	$this->assertEquals($userid, $res[0]->get('userid'), 'UserID should mach');

    	$res[0]->remove();

    	$this->assertEmpty($dir->lookupAttributes($this->user1), 'Should not find any users after user is deleted');

	}

    public function testUserMerge() {


		$dir = new UserDirectory();

		$user1 = $dir->getUserFromAttributes($this->user1);
    	$this->assertInstanceOf('User', $user1, 'Should be able to create a user1 object from attributes');
		$userid1 = $user1->get('userid');

		$user2 = $dir->getUserFromAttributes($this->user2);
    	$this->assertInstanceOf('User', $user2, 'Should be able to create a user2 object from attributes');
		$userid2 = $user2->get('userid');

		$this->assertNotEquals($userid1, $userid2, 'UserID should not match for user1 and user2');



		$user3 = $dir->getUserFromAttributes($this->user3);
    	$this->assertInstanceOf('User', $user3, 'Should be able to create a user3 object from attributes');
		$userid3 = $user3->get('userid');

		$user1b = $dir->getUserFromAttributes($this->user1);
    	$this->assertInstanceOf('User', $user1b, 'Should be able to create a user1b object from attributes');
		$userid1b = $user1b->get('userid');
		
		$user2b = $dir->getUserFromAttributes($this->user2);
    	$this->assertInstanceOf('User', $user2b, 'Should be able to create a user1b object from attributes');
		$userid2b = $user2b->get('userid');

		$this->assertEquals($userid3, $userid1b, 'UserID should match for userid3 and userid1b');
		$this->assertEquals($userid3, $userid2b, 'UserID should match for userid3 and userid2b');

		$this->assertEquals($userid1, $userid1b, 'UserID for user1 should not change, since it was created first');
		$this->assertNotEquals($userid2, $userid2b, 'UserID for user2 should change because it was merged into another object.');


    	$res = $dir->lookupKey('nnin:101080');
    	$this->assertCount(1, $res, 'Should only find one user');
    	$this->assertInstanceOf('User', $res[0], 'Lookup should return a User object');

    	$res[0]->remove();

    	$this->assertEmpty($dir->lookupAttributes($this->user1), 'Should not find any users after user is deleted');
    	$this->assertEmpty($dir->lookupAttributes($this->user2), 'Should not find any users after user is deleted');
    	$this->assertEmpty($dir->lookupAttributes($this->user3), 'Should not find any users after user is deleted');



        // $client = User::getByID('andreas@rnd.feide.no');


    }


    public function testUserMerge3() {


		$dir = new UserDirectory();

		$user1 = $dir->getUserFromAttributes($this->user1);
    	$this->assertInstanceOf('User', $user1, 'Should be able to create a user1 object from attributes');
		$userid1 = $user1->get('userid');

		$user2 = $dir->getUserFromAttributes($this->user2);
    	$this->assertInstanceOf('User', $user2, 'Should be able to create a user2 object from attributes');
		$userid2 = $user2->get('userid');

		$user11 = $dir->getUserFromAttributes($this->user11);
    	$this->assertInstanceOf('User', $user11, 'Should be able to create a user11 object from attributes');
		$userid11 = $user11->get('userid');

		$this->assertNotEquals($userid1, $userid2, 'UserID should not match for user1 and user2');
		$this->assertNotEquals($userid1, $userid11, 'UserID should not match for user1 and user11');
		$this->assertNotEquals($userid2, $userid11, 'UserID should not match for user2 and user11');


		$user3 = $dir->getUserFromAttributes($this->user3);
    	$this->assertInstanceOf('User', $user3, 'Should be able to create a user3 object from attributes');
		$userid3 = $user3->get('userid');


		$user11b = $dir->getUserFromAttributes($this->user11);
    	$this->assertInstanceOf('User', $user11b, 'Should be able to create a user11b object from attributes');
		$userid11b = $user11b->get('userid');
		
		$user2b = $dir->getUserFromAttributes($this->user2);
    	$this->assertInstanceOf('User', $user2b, 'Should be able to create a user1b object from attributes');
		$userid2b = $user2b->get('userid');

		$this->assertEquals($userid1, $userid3, 'UserID should match for userid1 and userid3');
		$this->assertEquals($userid1, $userid2b, 'UserID should match for userid1 and userid2b');
		$this->assertEquals($userid1, $userid11b, 'UserID should match for userid1 and userid11b');

		$this->assertNotEquals($userid2, $userid2b, 'UserID for user2 should change because it was merged into another object.');
		$this->assertNotEquals($userid11, $userid11b, 'UserID for userid11 should change because it was merged into another object.');


    	$res = $dir->lookupKey('nnin:101080');
    	$this->assertCount(1, $res, 'Should only find one user');
    	$this->assertInstanceOf('User', $res[0], 'Lookup should return a User object');

    	// print_r($user1->getJSON());
    	// print_r($user2->getJSON());
    	// print_r($user11->getJSON());
    	// print_r($user3->getJSON());
    	
    	

    	$res[0]->remove();

    	$this->assertEmpty($dir->lookupAttributes($this->user1), 'Should not find any users after user is deleted');
    	$this->assertEmpty($dir->lookupAttributes($this->user2), 'Should not find any users after user is deleted');
    	$this->assertEmpty($dir->lookupAttributes($this->user3), 'Should not find any users after user is deleted');
    	$this->assertEmpty($dir->lookupAttributes($this->user11), 'Should not find any users after user is deleted');


    }


    public function testUserUpdate() {


		$dir = new UserDirectory();

		$user1 = $dir->getUserFromAttributes($this->user1);
    	$this->assertInstanceOf('User', $user1, 'Should be able to create a user1 object from attributes');
		$userid1 = $user1->get('userid');

		$this->assertFalse($user1->updateFromAttributes($this->user1), 'No need to update when no changes');
		$this->assertTrue($user1->updateFromAttributes($this->user1x), 'Should update with new name on user');

		$user1b = $dir->getUserFromAttributes($this->user1);
    	$this->assertInstanceOf('User', $user1b, 'Should be able to create a user1b object from attributes');
		$userid1b = $user1b->get('userid');

		$this->assertEquals($userid1, $userid1b, 'UserID should not be changed after user update');
		$this->assertNotEquals($this->user1['displayName'][0], $user1b->get('name'), 'Name should not be the same after user udpate');

    	$user1->remove();

    	$this->assertEmpty($dir->lookupAttributes($this->user1), 'Should not find any users after user is deleted');

	}


}