var 
	http = require('http'),
	https = require('https'),

	Group = require('../models/Group').Group,
	Role = require('../models/Role').Role
	;



var FS = function(config) {
	this.config = config;
	this.prefix = 'uwap:grp:fs';
}


FS.prototype.createRoleObj = function(attrs) {

	var role = new Role(attrs);
	return role;
}


FS.prototype.createGroupObj = function(attrs) {

	if (!attrs.id) return null;

	var expectedPrefix = 'uwap:grp:';
	var id = attrs.id;	
	if (id.indexOf(expectedPrefix) === 0) {
		id = id.substring(expectedPrefix.length);
	}
	attrs.id = id;

	if (attrs.hasOwnProperty('title')) {
		attrs.displayName = attrs.title;
		delete attrs.title;
	}

	if (attrs.hasOwnProperty('displayName')) {
		attrs.displayName_ = attrs.displayName;
		delete attrs.displayName;
	}

	if (attrs.hasOwnProperty('type')) {
		attrs.groupType = attrs.type;
		delete attrs.type;
	}

	if (attrs.hasOwnProperty('role')) {
		attrs.vootRole = attrs.role;
		delete attrs.role;
	}

	var may = {
		'listMembers': false,
		'manageGroup': false,
		'manageMembers': false
	};
	if (attrs.vootRole && attrs.vootRole.basic && attrs.vootRole.basic === 'admin') {
		may.listMembers = true;
	}
	if (attrs.vootRole) {
		attrs.vootRole.may = may;	
	}
	

	var group = new Group(attrs);
	return group;

}


/*
 * Perform a API HTTP Request to FS Server..
 *
 * 
 */
FS.prototype._getData = function(path, callback) {
	
	// console.log("Performing _getData", path);

	if (!this.config.config.user) {
		throw "Missing FS API username";
	}
	if (!this.config.config.pass) {
		throw "Missing FS API password";
	}
	var auth = 'Basic ' + new Buffer(this.config.config.user + ':' + this.config.config.pass).toString('base64');
	var options = {
		hostname: 'jboss-test.uio.no',
		port: 443,
		path: path,
		method: 'GET',
		headers: {'Authorization': auth}
	};

	// var client = https.createClient(443, 'beta.foodl.org'); // to access this url i need to put basic auth.
	// var header = {'Host': 'www.example.com', 'Authorization': auth};
	// var request = client.request('GET', '/api/groups/' + input.userid, header);

	var req = https.request(options, function(res) {

		var responseData = '';
		// console.error('STATUS: ' + res.statusCode);
		// console.error('HEADERS: ' + JSON.stringify(res.headers));
		res.setEncoding('utf8');
		res.on('data', function (chunk) {
			responseData += chunk;
		});
		res.on('end', function (x) {
			if (responseData === null) return callback(null);

			if (res.statusCode === 200) {

				var data = JSON.parse(responseData);
				return callback(data);


			} else {
				// console.error("Error performing the request [" + res.statusCode + "]: " + responseData);
				callback(new Error(responseData));

			}


		});
	});

	req.on('error', function(e) {
		console.error('problem with request: ' + e.message);
		callback({'message': e.message});
	});
	req.end();
}


FS.prototype.getGroup = function(input, callback) {


	// console.log("About to getGroup," ,input);

	var groupid;
	groupid = 'uwap:grp:' + input.groupid.substring(this.prefix.length);
	this._getData('/fsrest/rest/api/group/'+ groupid, callback);

}



FS.prototype.getByUser = function(input, callback) {
	var that = this;
	// console.log("getByUser ...");
	this._getData('/fsrest/rest/api/user/brn:15517390264@fsutv.no/groups', function(response) {

		if (response instanceof Error) {
			return callback(null);
		}

		// console.log("›››› CALLBACK SUCCESS !!!", JSON.stringify(response)); return;
		var obj = [], newobj;
		for(var i = 0; i < response.length; i++) {
			newobj = that.createGroupObj(response[i]);
			if (newobj === null) continue;
			obj.push(newobj);
		}
		return callback(obj);

	});


}

exports.FS = FS;


