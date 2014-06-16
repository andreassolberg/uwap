

var util = require('util');
var async = require('async');

var config = require('./config').config;
var WaitGuard = require('./WaitGuard').WaitGuard;

var pluginNames = ['Foodle', 'Static', 'Agresso', 'LDAP', 'AttrOrg', 'FS'];

var pluginClasses = {};
for(var i = 0; i < pluginNames.length; i++) {
	pluginClasses[pluginNames[i]] = require('./plugins/' + pluginNames[i])[pluginNames[i]];
}







/**
 * GroupEngine controls reads configuration of group sources, and retrieves group information
 * based upon input as a JSON structure.
 * It invokes in parallell several plugins based upon the configuration, and the configured filters,
 * that decides which input will go to which plugins.
 */
var GroupEngine = function() {


	this.plugins = {};

	var sources = config.get('sources');
	for(var i = 0; i < sources.length; i++) {

		var currentSourceConfig = sources[i];
		var pluginName = currentSourceConfig.plugin;
		var sourceID = currentSourceConfig.sourceID;

		// console.log("Launching a new plugin");
		// console.log(currentSourceConfig);

		this.plugins[sourceID] = new pluginClasses[pluginName](currentSourceConfig);
	}



}


/**
 * Takes a user object as an input that can contain multiple accounts, then returns
 * multiple user objects for the same user, but with one account each.
 *
 *	In example split:
 *		{
 *			"userid": "andreas",
 *			"accounts": {
 *				"feide:1": {},
 *				"feide:2": {}
 *			}
 *		}
 *	into:
 *
 * 		[
 * 			{
 * 				"userid": "andreas",
 * 				"account": {
 * 					"userid": "feide:1"
 * 				}
 * 			},
 * 			{
 * 				"userid": "andreas",
 * 				"account": {
 * 					"userid": "feide:2"
 * 				}
 * 			}
 * 		]
 * 
 * @param  {[type]} input [description]
 * @return {[type]}       [description]
 */
GroupEngine.prototype.splitInput = function(input) {

	// IF no accounts is defined, return one entry.
	if (!input.hasOwnProperty('accounts')) {
		return [input];
	}

	var entry = {};
	var entries = [];

	for (var key in input.accounts) {
		entry = util._extend({}, input);
		delete entry.accounts;

		entry.account = input.accounts[key];
		entry.account.userid = key;
		entries.push(entry);
	}
	return entries;

}




/**
 * Based upon a input structure pointing to a specific user, return a list of groups.
 * 
 * @param  {[type]}   input    [description]
 * @param  {Function} callback [description]
 * @return {[type]}            [description]
 */
GroupEngine.prototype.getByUser = function(inputSource, callback) {
	var that = this;
	var inputList = this.splitInput(inputSource);
	var sources = config.getSources(inputList);
	var result = {
		"groups": []
	};



	console.error('Ready to perform getByUser operation based upon the following input:'); 
	console.error(inputList);
	console.error('Sources [input, source]:'); 
	console.error(sources);


	var guard = new WaitGuard(function(sources) {
		// Function when all plugins are completed..
		console.error("----] All Complete");
		result.sources = sources;
		callback(result);

	}, 2000);


	// console.log("Sources " + sources.length);
	// console.log(sources);
	

	var process = function(data, sourceCompleteCallback) {

		var input = data[0];
		var source = data[1];

		var timeout = setTimeout(function() {
			// console.log("about to do timeout");
			// console.log(sourceCompleteCallback);
			for(var key in sourceCompleteCallback) console.log("key " + key);
			sourceCompleteCallback('Timeout on group plugin');
		}, 100);

		// console.log("Input"); console.log(input);
		// console.log("Source"); console.log(source);

		console.error("-----");
		console.error("Running plugin " + source.sourceID + " on user " + input.userid);
		that.plugins[source.sourceID].getByUser(input, function(err, res) {
			clearTimeout(timeout);
			sourceCompleteCallback(err, res)
		});
	}






	console.error('----- ASYNC ----');

	async.map(sources, process, function(err, results) {

		if (err) {
			console.error("Error. Im not sure what: " + err);
			return callback(new Error('Error processing groupengine plugins ' + err ));
		}

		// console.log("results");
		// console.log(results);


		// Flatten two level array into a flat array...
		var res = [];
		for(var i = 0; i < results.length; i++) {
			if (results[i] !== null) {
				for(var j = 0; j < results[i].length; j++) {
					res.push(results[i][j]);
				}
			}
		}
		result.groups = res;
		callback(result);
	});

	
	return;





	// var action;

	// for(var i = 0; i < sources.length; i++) {
	// 	// console.log("Adding source #" + i);
	// 	// WaitGuard.prototype.addAction = function (sourceID, actionCallback, tooLate) {
		
	// 	action = (function() {
	// 		var currentSource = sources[i];
	// 		return function(donecallback) {
	// 			// console.error('___ Processing [' + currentSource.plugin + '] srcid:' + currentSource.sourceID);
	// 			that.plugins[currentSource.sourceID].getByUser(input.user, function(moreGroups) {
	// 				var globalGroupId;
	// 				if (moreGroups !== null) {
	// 					for(var i = 0; i < moreGroups.length; i++) {
	// 						// console.error('______> adding group ' + moreGroups[key]['title']);
	// 						// globalGroupId = currentSource.sourceID + ':' + moreGroups[i].id;
	// 						moreGroups[i].id = currentSource.sourceID + ':' + moreGroups[i].id;
	// 						// moreGroups[i]._globalId = globalGroupId;
	//  						moreGroups[i].sourceID = currentSource.sourceID;
	// 						result.groups.push(moreGroups[i]);
	// 					}
						
	// 				}
	// 				donecallback();
	// 			});

	// 		}
	// 	})();

	// 	guard.addAction(sources[i].sourceID, action);
	// }

	// guard.startTimer();
}



GroupEngine.prototype.peopleSearch = function(input, callback) {
	var that = this;
	var sources = config.getSources(input, "peopleSearch");
	var result = {
		"people": []
	};

	var guard = new WaitGuard(function(sources) {
		// Function when all plugins are completed..
		console.error("----] All Complete");
		result.sources = sources;
		callback(result);

	}, 2000);


	// console.log("Sources " + sources.length);
	// console.log(sources);


	if (input.realm) {
		input.user.realm = input.realm;
	}

	var action;

	for(var i = 0; i < sources.length; i++) {
		// console.log("Adding source #" + i);
		// WaitGuard.prototype.addAction = function (sourceID, actionCallback, tooLate) {
		
		action = (function() {
			var currentSource = sources[i];
			return function(donecallback) {
				// console.error('___ Processing [' + currentSource.plugin + '] srcid:' + currentSource.sourceID);
				that.plugins[currentSource.sourceID].peopleSearch(input, function(morePeople) {
					// var globalGroupId;
					for(var i = 0; i < morePeople.length; i++) {

						morePeople[i].source = currentSource.sourceID;

						result.people.push(morePeople[i]);

						// console.error('______> adding group ' + moreGroups[key]['title']);
						// globalGroupId = currentSource.sourceID + ':' + key;
 						// moreGroups[key].source = currentSource.sourceID;
						// result.people.push = moreGroups[key];
					}
					donecallback();
				});

			}
		})();

		guard.addAction(sources[i].sourceID, action);
	}

	guard.startTimer();
}

GroupEngine.prototype.getGroup = function(input, callback) {

	var that = this;
	var source = config.getSourceByGroupID(input);

	if (source === null) throw new Error('Could not find plugin to lookup this group');

	that.plugins[source.sourceID].getGroup(input, callback);

	// console.log("Source found was"); console.log(source);



}


exports.GroupEngine = GroupEngine;

