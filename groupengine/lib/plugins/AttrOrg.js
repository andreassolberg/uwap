var crypto = require('crypto');



var cleanOrgUnitName = function (str) {

	var index = str.indexOf('|');
	if (index > 0) {
		return str.substr(0, index);
	}
	return str;
}

var isPrefixed = function(str, search) {
	if (str.indexOf(search) !== 0) {
		return null;
	}

	return str.substr(search.length);
}

var contains = function(arr, key) {
	if (!arr) return false;
	for(var i = 0; i < arr.length; i++) {
		if (arr[i] === key) {
			return true;
		}
	}
	return false;
}


var decodeEntitlementGO = function(str, realm, orgname, unitnames) {

	var rawElements = str.split(':');
	var elements = rawElements.map(decodeURIComponent);

	if (elements.length !== 5) {
		console.error('Invalid number of elements in group')
		return null;
	}
 
	var ng = {};

	if (elements[0] === 'b') {
		ng.gruppetype = 'basisgruppe';
	} else if (elements[0] === 'u') {
		ng.gruppetype = 'undervisningsgruppe';
	} else {
		ng.gruppetype = 'annen gruppe';
	}

	// var shasum = crypto.createHash('sha1');
	// var name = cleanOrgUnitName(input.customdata['eduPersonOrgUnitDN:ou'][i]);


	ng.id = elements[0] + ':' + elements[1] + ':' + elements[2];

	var unitname = ng.id;
	if (unitnames[elements[1]]) {
		unitname = unitnames[elements[1]];
	}

	ng.role = 'member';

	if (elements[3] === 'faculty') {
		ng.role = 'admin';
	}

	ng.title = elements[4] + ' ved ' + unitname;
	ng.description = elements[4] + ' ' + ng.gruppetype + ' ved ' + unitname + ' (' + orgname + ')';
	ng.gorolle = elements[3];


	return ng;

}

var decodeEntitlementGrep = function(str, input) {

	var rawElements = str.split(':');

	var elements = rawElements.map(decodeURIComponent);

	return str;


}



var AttrOrg = function(config) {
	this.config = config;
}




AttrOrg.prototype.getByUser = function(input, callback) {


	// console.log("-----=====> ATTRORG -----=====> ATTRORG -----=====> ATTRORG -----=====> ATTRORG -----=====> ATTRORG -----=====> ATTRORG");
	// console.log(JSON.stringify(input, undefined, 4));

	var groups = {};
	var id, ng;

	var orgname = input.realm.toLowerCase();
	var unitnames = {};
	var realm = input.realm.toLowerCase();

	if (!input) return callback(groups);
	if (!input.customdata) return callback(groups);

	// console.log(JSON.stringify(input.customdata, undefined, 4));


	// 'eduPersonAffiliation'
	if (input.realm && input.customdata['eduPersonOrgDN:o']) {

		// console.log("jalla");
		var ng = {};

		orgname = input.customdata['eduPersonOrgDN:o'][0];

		ng.role = 'member';
		if (input.customdata['eduPersonAffiliation'] && contains(input.customdata['eduPersonAffiliation'], 'employee')) {
			ng.role = 'admin';
		}
		ng.title = 'Ansatte og studenter i ' + orgname;
		ng.description = 'Ansatte og studenter i ' + orgname;

		id = realm;

		groups['org:' + id] = ng;


		if (input.customdata['eduPersonAffiliation'] && contains(input.customdata['eduPersonAffiliation'], 'employee')) {


			var ng2 = {};
			ng2.role = 'member';
			ng2.title = 'Ansatte i ' + orgname;
			ng2.description = 'Kun de ansatte i ' + orgname;

			groups['org:' + id + ':employees'] = ng2;

		}


	}




	if (input.customdata['eduPersonOrgUnitDN:norEduOrgUnitUniqueIdentifier']) {

		
		for(var i = 0; i < input.customdata['eduPersonOrgUnitDN:norEduOrgUnitUniqueIdentifier'].length; i++) {

			ng = {};
			var shasum = crypto.createHash('sha1');

			var name = cleanOrgUnitName(input.customdata['eduPersonOrgUnitDN:ou'][i]);

			ng.role = 'member';
			ng.title = name;
			ng.description = 'Ansatte og studenter i ' + name;

			shasum.update(input.customdata['eduPersonOrgUnitDN'][i]);

			var unitid = input.customdata['eduPersonOrgUnitDN:norEduOrgUnitUniqueIdentifier'][i];

			id = input.realm.toLowerCase() + ':' + unitid;

			unitnames[unitid] = name;

			groups['orgunit:' + id] = ng;

		}

	}


	console.error(unitnames);

	if (input.customdata['eduPersonEntitlement']) {

		var entitlement, match;
		for(var i = 0; i < input.customdata['eduPersonEntitlement'].length; i++) {
			entitlement = input.customdata['eduPersonEntitlement'][i];

			if (match = isPrefixed(entitlement, 'urn:mace:feide.no:go:grep:') ) {

				ng = {};
				id = match;
				ng.role = 'member';
				ng.title = match;

				ng.debug = decodeEntitlementGrep(match, input);

				groups['grep:' + id] = ng;


			} else if (match = isPrefixed(entitlement, 'urn:mace:feide.no:go:group:') ) {

				ng = decodeEntitlementGO(match, realm, orgname, unitnames);

				if (ng !== null) {
					groups['group:' + ng.id] = ng;	
				}

				

			}

		}

	}



	// console.log("-", groups);

	return callback(groups);

	// setTimeout(function() {
	// 	callback({
	// 		"agresso1": {
	// 			"title": "Agresso Static Example",
	// 			"role": "member"
	// 		}
	// 	});
	// }, 2);

}

exports.AttrOrg = AttrOrg;

