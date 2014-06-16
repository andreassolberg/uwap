

var configsrc = require('../etc/config.json');

var config = {};


config.get = function(key) {
	return configsrc[key];
}



config.getSources = function(inputList, type) {
	var result = [], cur, input;

	for(var j = 0; j < inputList.length; j++) {

		input = inputList[j];

		for(var i = 0; i < configsrc.sources.length; i++) {
			cur = configsrc.sources[i];

			// console.log("Processing " + cur['plugin']);

			// if (cur.hasOwnProperty('filter:peoplesearch-realm') && input.custom && input.custom.realm) {
			// 	if (cur['filter:peoplesearch-realm'] === input.custom.realm) {
			// 		result.push([input, cur]);
			// 	}
			// 	continue;
			// }

			if (cur.hasOwnProperty('filter:userid')) {
				// console.log("Checking match for filter:userid ", cur['filter:userid'], input.user.userid);
				if (cur['filter:userid'] !== input.userid) continue;
			}

			if (cur.hasOwnProperty('filter:realm')) {
				if (cur['filter:realm'] !== input.realm) continue;
			}

			if (cur.hasOwnProperty('filter:idp')) {
				// console.log("Checking match for filter:idp ", cur['filter:idp'], input.user.idp);
				if (cur['filter:idp'] !== input.idp) continue;
			}

			if (type && cur['support']) {
				if (cur['support'][type] === false) continue;
			}

			// console.log("Processed  " + cur['plugin']);
			result.push([input, cur]);
		}

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

