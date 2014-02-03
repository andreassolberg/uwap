

var configsrc = require('../etc/config.json');

var config = {};


config.get = function(key) {
	return configsrc[key];
}

config.getSources = function(input, type) {
	var result = [], cur;
	for(var i = 0; i < configsrc.sources.length; i++) {
		cur = configsrc.sources[i];

		// console.log("Processing " + cur['plugin']);

		if (cur.hasOwnProperty('filter:peoplesearch-realm') && input.realm) {
			if (cur['filter:peoplesearch-realm'] === input.realm) {
				result.push(cur); 
			}
			continue;
		}

		if (cur.hasOwnProperty('filter:userid')) {
			if (cur['filter:userid'] !== input.user.userid) continue;
		}
		if (cur.hasOwnProperty('filter:realm')) {
			if (cur['filter:realm'] !== input.user.realm) continue;
		}
		if (cur.hasOwnProperty('filter:idp')) {
			if (cur['filter:idp'] !== input.user.idp) continue;
		}

		if (type && cur['support']) {
			if (cur['support'][type] === false) continue;
		}
		// console.log("Processed  " + cur['plugin']);
		result.push(cur);
	}

	// console.log('-----');
	// console.log('Input', input);
	// console.log('Config', result);
	// console.log('-----');


	return result;
}

config.getSourceByGroupID = function(input) {

	if (!input.groupid) throw new Error('Input property groupid is not set. This is required.');
	var groupid = input.groupid;

	for(var i = 0; i < configsrc.sources.length; i++) {
		cur = configsrc.sources[i];

		if (groupid.indexOf(cur.sourceID + ':') === 0) {
			return cur;
		}

	}
	return null;
}

exports.config = config;

