var http = require('http');
var https = require('https');



var Foodle = function(config) {
	this.config = config;

}

Foodle.prototype.getByUser = function(input, callback) {

	if (!this.config.config.user) {
		throw "Missing Foodle API username";
	}
	if (!this.config.config.pass) {
		throw "Missing Foodle API password";
	}

	var auth = 'Basic ' + new Buffer(this.config.config.user + ':' + this.config.config.pass).toString('base64');

	// var client = https.createClient(443, 'beta.foodl.org'); // to access this url i need to put basic auth.

	// var header = {'Host': 'www.example.com', 'Authorization': auth};
	// var request = client.request('GET', '/api/groups/' + input.userid, header);
	

	var options = {
		hostname: 'beta.foodl.org',
		port: 443,
		path: '/api/groups/' + input.userid,
		method: 'GET',
		headers: {'Authorization': auth}
	};

	var req = https.request(options, function(res) {

		var responseData = '';

		// console.error('STATUS: ' + res.statusCode);
		// console.error('HEADERS: ' + JSON.stringify(res.headers));
		res.setEncoding('utf8');
		res.on('data', function (chunk) {
			// console.error('BODY: ' + chunk);
			responseData += chunk;
		});
		res.on('end', function () {
			// responseData += chunk;
			// console.error('All response: ' + responseData);
			

			var data = JSON.parse(responseData);
			if (data.status && data.status === 'ok') {
				callback(data.data);
			}
		});
	});

	req.on('error', function(e) {
		console.error('problem with request: ' + e.message);
	});

	// write data to request body
	// req.write(JSON.stringify());
	req.end();







	// callback({
	// 	"uwap:foodle:foo": "Foodle foo"
	// });
}

exports.Foodle = Foodle;


