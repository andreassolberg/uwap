<?php


// Some left over from the API.
// Moved here, in order to someday create a test from it.


	// TODO: DELETE THIS...
	} else if (Utils::route(false, '^/debuggroups', $parameters)) {


		$user = User::getByID('andreas@uninett.no');
		$groupconnector = new GroupConnector($user);
		$response = $groupconnector->getGroupsListResponse();

		$type = new SCIMResourceGroupType(array('id' => 'uwap:group:type:ad-hoc', 
			'displayName' => array(
				'en' => 'Ad-Hoc',
				'nb' => 'Någruppe'
			)
		));


		echo 'Groups: <pre>'; 
		print_r($type);
		print_r($type->getJSON()); 
		exit;


