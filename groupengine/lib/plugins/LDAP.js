// var ldap = require('ldapjs');
// var util = require('util');
// var WaitGuard = require('../WaitGuard').WaitGuard;

var 

	Group = require('../models/Group').Group,
	Role = require('../models/Role').Role
	;
	
var WaitGuard = require('../WaitGuard').WaitGuard;



var ldaplib = require('../ldaplib');





var LDAP = function(config) {
	this.config = config;
	this.ldap = new ldaplib.LDAP(this.config);
}


/**
 * Lookup a persons group memberships based upon the users ID
 *
 * The input will be on a form like this:
 *
 * 	{
 * 		"userid": "andreas@uninett.no",
 * 		"realm": "uninett.no",
 * 		"idp": "https://idp.feide.no"
 * 	}
 * 
 * @param  {[type]}   input    [description]
 * @param  {Function} callback [description]
 * @return {[type]}            [description]
 */
LDAP.prototype.getByUser = function(input, callback) {
	
	var that = this;
	// console.log("input"); console.log(input);

	// Extract an array including the personEntry attributes that refers to groups.
	var groupattrs = [];
	for(var key in this.config.config['group-refs']) {
		groupattrs.push(key);
	}


	// ldapSearch = function(base, query, type, attrs, multiple, callback) {


	// Perform a LDAP search for the personEntry. Extract attributes referring to groups.
	this.ldap.ldapSearch(
		this.config.config['search-base'], 
		ldaplib.LDAP.sfilter(this.config.config['useridattr'],input.userid),
		"sub", groupattrs, false,

	// Callback when person LDAP search is successfully completed...
	function(personResultEntry) {

		var groups = [];
		var groupLookupStatus;

		// console.log("LDAP personResultEntry result is "); console.log(personResultEntry);

		if (personResultEntry === null) {
			callback(null); return;
		}


		var guard = new WaitGuard(function(sources) {
			// Function when all plugins are completed..
			console.error("----] All Complete");
			groupLookupStatus = sources;

			callback(groups);

		}, 2000);

		var action;


		// console.log("personResultEntry", personResultEntry);


		// console.log("Config LDAP entry"); console.log(that.config.config['group-refs']);

		// For each type of group reference
		for(var groupAttrName in that.config.config['group-refs']) {

			// Iterate over all entries of this type of group references
			// in example a reference could be "eduPersonOrgUnitDN", and there
			// could be several entries of this type.

			// console.log("----- personResultEntry");
			// console.log(personResultEntry);

			// console.log("PROCESSING " + groupAttrName);

			if (personResultEntry.hasProperty(groupAttrName)) {

				// personGroupRefs is the list of DNs referred to by this specific group reference
				// of type groupAttrName
				var personGroupRefs = personResultEntry.getValueArray(groupAttrName);
				 // ldaplib.LDAP.arrayize(personResultEntry[groupAttrName]);

				// Let us iterate this list of references to groups of type groupAttrName
				for(var i = 0; i < personGroupRefs.length; i++) {

					// The sourceID will be 
					var sourceID = groupAttrName + '.' + i;

					// console.log("CHECKING " + groupAttrName + '.' + i);

					// Prepare an action that for a specific group reference perform the LDAP lookup, and add the result
					action = (function() {
						
						var dn = personGroupRefs[i];

						// console.log("Preparing an action for " + sourceID + " " + dn);

						var mapping = new ldaplib.ObjectMap(that.config.config['group-refs'][groupAttrName]);

						return function(donecallback) {
							console.error('___ Processing [' + sourceID + '] lookin up ' + dn);

							that.ldap.ldapLookup(dn, mapping.getAttrs(), function(groupResultEntry) {

								var groupobj;
								var group = groupResultEntry.getMapped(mapping);
								group.vootRole = {
									'basic': 'member'
								};

								// ldapSearch = function(base, query, type, attrs, multiple, callback) {
								that.ldap.ldapSearch(dn, 
								{
									"objectClass": "alias"
								}, "one", ["aliasedObjectName"], false, function(leaderResultEntry) {

									if (leaderResultEntry) {


										// console.log("Found leader of this group", groupResultEntry);
										// console.log(" COMPARING ====> " + personResultEntry.dn);
										// console.log(" COMPARING ====> " + res[0].aliasedObjectName);

										if (personResultEntry.getDN() === leaderResultEntry.getAliasedObjectName()) {

											group.vootRole.basic = 'admin';
										}

									}


									groupobj = new Group(group);
									groups.push(groupobj);
									donecallback();
								});


							});
						}
					})();
					guard.addAction(sourceID, action);

				}
			}

		}


		guard.startTimer();



	});



}


LDAP.prototype.getGroupByLocalID = function(localID, callback) {




}

LDAP.prototype.getGroupRefByPrefix = function(groupid) {

	var fp, leftID;

	// For each type of group reference
	for(var groupAttrName in this.config.config['group-refs']) {


		fp = this.config.sourceID + ':' + this.config.config['group-refs'][groupAttrName].prefix.id + ':';

		// console.log("Checking " + groupAttrName);
		// console.log(fp)
		// console.log(groupid)


		if (groupid.indexOf(fp) === 0) {

			leftID = groupid.substring(fp.length);
			// console.log("MATCHED " + groupAttrName + ' ' + leftID);

			return {
				"attrName": groupAttrName,
				"localID": leftID,
				"config": this.config.config['group-refs'][groupAttrName]
			}


		}

	}

	return null;


}


/** 
 * Create a LDAP search filter based upon a local Identifier,
 * and a group ref config.
 */
LDAP.prototype.getGroupRefSearchFilter = function(groupref) {

	var reverseMap = {};
	for(var key in groupref.config.map) {
		reverseMap[groupref.config.map[key]] = key;
	}
	// console.log("Reverse map", reverseMap);

	var query = {};
	query[reverseMap["id"]] = groupref.localID;
	return query;

}

/**
 * Search configured person search base for group members that match a specific value
 *
 * In example: search for all members of cn=people,dc=uninet.no,dc=no
 * for users that have the attribute   norEduPersonNIN=NO23984798
 * 
 * @param  {[type]}   query     [description]
 * @param  {Function} callback [description]
 * @return {[type]}            [description]
 */
LDAP.prototype.getMembers = function(query, callback) {

	
	var that = this;
	var groups = {};

	// console.log("getMembers ", query);

	// Extract an array including the personEntry attributes that refers to groups.
	var personattrs = [];
	for(var key in that.config.config.person.map) {
		personattrs.push(key);
	}

	var personMap = new ldaplib.ObjectMap(that.config.config.person);



	// LDAP.prototype.ldapSearch = function(base, query, type, attrs, multiple, callback) {
	// Perform a LDAP search for the personEntry. Extract attributes referring to groups.
	this.ldap.ldapSearch(
		this.config.config['search-base'], 
		query,
		"sub",
		personMap.getAttrs(),
		true,
		callback);

}



LDAP.prototype.getGroup = function(input, callback) {

	var that = this;
	var groupid = input.groupid;
	var result = {
		id: groupid,
		sourceID: this.config.sourceID
	};

	var groupref = this.getGroupRefByPrefix(groupid);
	var searchFilter = this.getGroupRefSearchFilter(groupref);

	var base = 'dc=no'; // TODO

	var mapping = new ldaplib.ObjectMap(groupref.config);
	var personMap = new ldaplib.ObjectMap(that.config.config.person);

	// LDAP.prototype.ldapSearch = function(base, query, type, attrs, multiple, callback) {
	// console.log("PERFORMING A SEARCH FOR GROUP");
	this.ldap.ldapSearch(base, searchFilter, "sub", mapping.getAttrs(), false, function(groupResultEntry) {

		if (groupResultEntry) {

			var group = groupResultEntry.getMapped(mapping);

			group.id = groupid;
			group.sourceID = that.config.sourceID;

			// console.log("Found group identifier!!!", groupResultEntry);

			var memberQuery = {};
			memberQuery[groupref.attrName] = groupResultEntry.getDN();

			that.getMembers(memberQuery, function(members) {

				// console.log("GOT MEMBERS");


				group.users = members.getMapped(personMap);
				// delete groupResultEntry["_"];

				callback(group);
			});


		} else {
			// console.log(" ------ NOT FOUND");
			callback(null);
		}

	});






}


LDAP.prototype.peopleSearch = function(input, callback) {


	var that = this;
	var groups = {};

	// console.log("getMembers ", query);

	// Extract an array including the personEntry attributes that refers to groups.
	var personattrs = [];
	for(var key in that.config.config.person.map) {
		personattrs.push(key);
	}

	var personMap = new ldaplib.ObjectMap(that.config.config.person);


	var query = '(displayName=*' + input.query + '*)';

	// LDAP.prototype.ldapSearch = function(base, query, type, attrs, multiple, callback) {
	// Perform a LDAP search for the personEntry. Extract attributes referring to groups.
	this.ldap.ldapSearch(
		this.config.config['search-base'], 
		query,
		"sub",
		personMap.getAttrs(),
		true,
		function(personResultEntries) {


			// console.log("Person results"); console.log(personResultEntries.entries);
			callback(personResultEntries.getMapped(personMap));

	});



}



exports.LDAP = LDAP;

