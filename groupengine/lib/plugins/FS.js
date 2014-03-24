var http = require('http');
var https = require('https');



var FS = function(config) {
	this.config = config;

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
			var data = JSON.parse(responseData);
			return callback(data);
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

	var prefix = 'uwap:grp:fs:';
	groupid = input.groupid.substring(prefix.length);

	this._getData('/fsrest/rest/api/group/'+ groupid, callback);

}



FS.prototype.getByUser = function(input, callback) {

	// console.log("getByUser ...");
	this._getData('/fsrest/rest/api/user/brn:15517390264@fsutv.no/groups', function(response) {

		// console.log("›››› CALLBACL SUCCESS !!!", response);
		var obj = {};
		for(var i = 0; i < response.length; i++) {
			obj[response[i].id] = response[i];
		}
		return callback(obj);

	});


}

exports.FS = FS;


