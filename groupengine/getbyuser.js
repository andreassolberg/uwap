#!/usr/bin/env node


var fs = require('fs');

var GroupEngine = require('./lib/GroupEngine').GroupEngine;



var readInput = function(callback, error) {

	var message = '';

	process.stdin.resume();
	process.stdin.setEncoding('utf8');

	process.stdin.on('data', function(chunk) {
		// process.stdout.write('data: ' + chunk);
		message += chunk;
	});

	process.stdin.on('end', function() {
		// process.stdout.write('end');
		try {
			var pm = JSON.parse(message);	
		} catch(e) {
			// console.log("Error objcet: " + e);
			error(e);
			return;
		}
		
		callback(pm);
	});

}


var groupengine = new GroupEngine();

readInput(function(input) {
	// console.error("Data read: OK");
	// console.error(data);
	console.error(JSON.stringify(input));

	// console.log(groupengine.getByUser(input));

	groupengine.getByUser(input, function(res) {
		console.error('--------- RESULT ----------');
		console.log(JSON.stringify(res, null, 4));
		process.exit(0);
	});


}, function(err) {
	console.log(JSON.stringify({"message": "Input error: " + err}))
	process.exit(1);
});

