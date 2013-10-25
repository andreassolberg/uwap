define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core')
		;


	var App = function(properties) {
		
		this.properties = properties;

		// if (this.proxies) {
		// 	this.proxiesArr = [];
		// 	for(var key in this.proxies) {
		// 		this.proxies[key]['id'] = key;
		// 		this.proxiesArr.push(this.proxies[key]);
		// 	}
		// }

		// if (this['user-stats']) {
		// 	this.count = this['user-stats']['count'];
		// } else {
		// 	this.count = null;
		// }
	}

	App.prototype.getName = function() {
		return this.properties.name;
	}

	App.prototype.logo = function() {
		return UWAP.utils.getEngineURL('/api/media/logo/app/' + this.properties.id);
	}



	// App.prototype.getScopes = function() {
		

	// 	var result = {};

	// 	// if (this.scopes) {
	// 	// 	$.each(this.scopes, function(i, scope) {
				
	// 	// 	});
	// 	// }

	// 	return this.scopes;

	// 	var result = {};
	// 	var that = this;
	// 	var prefix = "rest_" + this.appconfig.id;

	// 	var smatch = new RegExp(prefix + '_([^_]+)$');

	// 	console.log ('------ loooking for scopes in ', that.appconfig.App.scopes);


	// 	$.each([ [this.scopes, true], [this.scopes_requested, false] ], function(i, p) {
	// 		var scopes = p[0], access = p[1];
	// 		console.log("About to priocess scopes", p, scopes);

	// 		$.each(scopes, function(i, scope) {
	// 			var localScope = null;
	// 			if (smatch.test(scope)) {
	// 				localScope = smatch.exec(scope)[1];
	// 			} else {
	// 				return;
	// 			}
	// 			console.log("LOCAL scope of " + scope + " using prefix " + prefix + " is " + localScope, that.appconfig.App.scopes);
	// 			if (that.appconfig.App && that.appconfig.App.scopes && that.appconfig.App.scopes[localScope]) {
	// 				result[scope] = that.appconfig.App.scopes[localScope];
	// 			} else {
	// 				result[scope] = {
	// 					name: "Unknown name"
	// 				}
	// 			}
	// 			result[scope].access = access;
	// 			result[scope].localScope = localScope;
	// 		});

	// 	});


	// 	console.log("Result is ", result);
	// 	return result;
		
	// }

	return App;
});