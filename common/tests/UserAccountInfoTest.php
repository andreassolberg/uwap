<?php

require_once(dirname(dirname(__FILE__)) . '/lib/autoload.php');


class UserAccountInfoTest extends PHPUnit_Framework_TestCase {
    


	protected $user1json = '
{
	"eduPersonPrincipalName" : ["testuser1@uninett.no"],
	"displayName" : ["Andreas _TestUser1_ Solberg"],
	"idp" : ["https://idp.feide.no"],
	"norEduPersonNIN" : ["101080"],
	"eduPersonOrgDN:o": [
		"Oslo kommune"
	],
	"eduPersonOrgUnitDN:ou": [
		"Alna grunnskole"
	],
	"eduPersonOrgUnitDN": [
		"ou=Alna grunnskole,cn=testorg,dc=feide,dc=no,ou=feide,dc=uninett,dc=no"
	],
	"eduPersonEntitlement": [
		"urn:mace:feide.no:go:grep:http://psi.udir.no/laereplan/aarstrinn/aarstrinn3",
		"urn:mace:feide.no:go:group:b:NO876326125:3A:student:Klasse%203A",
		"urn:mace:feide.no:go:group:u:NO876326125:3A-MAT:student:Matematikk%203A",
		"urn:mace:feide.no:go:group:u:NO876326125:3A-NOR:student:Norsk%203A"
	]
}
';
	protected $user2json = '
{
	"eduPersonPrincipalName" : ["testuser2@uninett.no"],
	"displayName" : ["Andreas _TestUser2_ Solberg"],
	"idp" : ["https://idp.feide.no"],
	"norEduPersonNIN" : ["101080"],
	"eduPersonOrgDN:o": [
		"Trondheim kommune"
	],
	"eduPersonOrgUnitDN:ou": [
		"aaa grunnskole"
	],
	"eduPersonOrgUnitDN": [
		"ou=dasdfsdf grunnskole,cn=testorg,dc=feide,dc=no,ou=feide,dc=uninett,dc=no"
	],
	"eduPersonEntitlement": [
		"urn:mace:feide.no:go:grep:http://psi.udir.no/laereplan/aarstrinn/aarstrinn3",
		"urn:mace:feide.no:go:group:b:NO876326125:3A:student:Klasse%203A",
		"urn:mace:feide.no:go:group:u:NO876326125:3A-MAT:student:Matematikk%203A",
		"urn:mace:feide.no:go:group:u:NO876326125:3A-NOR:student:Norsk%203A"
	]
}
';



    public function test1() {

    	$dir = new UserDirectory();

    	$account = new UserAttributeInput(json_decode($this->user1json, true));
		$user = $dir->getUserFromAttributes($account, true);


		// echo "Account info"; print_r($account->accountinfo);
		// echo "Complex id"; print_r($account->complexId);
		// echo "Account id"; print_r($account->accountId);



    	$account2 = new UserAttributeInput(json_decode($this->user2json, true));
    	$user2 = $dir->getUserFromAttributes($account2, true);

		print_r($user->getJSON());
		print_r($user2->getJSON());


		$user->remove();
		$user2->remove();



    }




}