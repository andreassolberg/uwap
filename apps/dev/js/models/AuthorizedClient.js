define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core')
		;

	var in_array = function (key, array) {

		var i;
		if (typeof array === 'undefined' || !array.length) return false;
		for(i = 0; i < array.length; i++) {
			if (key === array[i]) return true;
		}
		return false;
	}

	var AuthorizedClient = function(appconfig, opts) {
		this.appconfig = appconfig;
		for(var key in opts) {
			this[key] = opts[key];
		}

		if (!this.scopes) this.scopes = [];
		if (!this.scopes_requested) this.scopes_requested = [];


		// if (this.proxies) {
		// 	this.proxiesArr = [];
		// 	for(var key in this.proxies) {
		// 		this.proxies[key]['id'] = key;
		// 		this.proxiesArr.push(this.proxies[key]);
		// 	}
		// }

	}

	AuthorizedClient.prototype.isPending = function() {
		console.log("Checking is in_array()", "rest_" + this.appconfig.id, this.scopes);

		return !in_array("rest_" + this.appconfig.id, this.scopes);
	}

	AuthorizedClient.prototype.getScopes = function() {
		
		var result = {};
		var that = this;
		var prefix = "rest_" + this.appconfig.id;

		var smatch = new RegExp(prefix + '_([^_]+)$');

		// console.log ('------ loooking for scopes in ', that.appconfig.proxy.scopes);


		$.each([ [this.scopes, true], [this.scopes_requested, false] ], function(i, p) {
			var scopes = p[0], access = p[1];
			console.log("About to priocess sciopes", p, scopes);

			$.each(scopes, function(i, scope) {
				var localScope = null;
				if (smatch.test(scope)) {
					localScope = smatch.exec(scope)[1];
				} else {
					return;
				}
				console.log("LOCAL scope of " + scope + " using prefix " + prefix + " is " + localScope, that.appconfig.proxy.scopes);
				if (that.appconfig.proxy && that.appconfig.proxy.scopes && that.appconfig.proxy.scopes[localScope]) {
					result[scope] = that.appconfig.proxy.scopes[localScope];
				} else {
					result[scope] = {
						name: "Unknown name"
					}
				}
				result[scope].access = access;
				result[scope].localScope = localScope;
			});

		});


		console.log("Result is ", result);
		return result;
		
	}

	return AuthorizedClient;
});