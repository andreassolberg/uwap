

var config = require('./config').config;
var WaitGuard = require('./WaitGuard').WaitGuard;

var pluginNames = ['Foodle', 'Static', 'Agresso', 'LDAP'];

var pluginClasses = {};
for(var i = 0; i < pluginNames.length; i++) {
	pluginClasses[pluginNames[i]] = require('./plugins/' + pluginNames[i])[pluginNames[i]];
}








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


GroupEngine.prototype.getByUser = function(input, callback) {
	var that = this;
	var sources = config.getSources(input);
	var result = {
		"groups": {}
	};

	var guard = new WaitGuard(function(sources) {
		// Function when all plugins are completed..
		console.error("----] All Complete");
		result.sources = sources;
		callback(result);

	}, 2000);


	// console.log("Sources " + sources.length);
	// console.log(sources);

	var action;

	for(var i = 0; i < sources.length; i++) {
		// console.log("Adding source #" + i);
		// WaitGuard.prototype.addAction = function (sourceID, actionCallback, tooLate) {
		
		action = (function() {
			var currentSource = sources[i];
			return function(donecallback) {
				// console.error('___ Processing [' + currentSource.plugin + '] srcid:' + currentSource.sourceID);
				that.plugins[currentSource.sourceID].getByUser(input.user, function(moreGroups) {
					var globalGroupId;
					for(var key in moreGroups) {
						// console.error('______> adding group ' + moreGroups[key]['title']);
						globalGroupId = currentSource.sourceID + ':' + key;
 						moreGroups[key].source = currentSource.sourceID;
						result.groups[globalGroupId] = moreGroups[key];
					}
					donecallback();
				});

			}
		})();

		guard.addAction(sources[i].sourceID, action);
	}

	guard.startTimer();
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

