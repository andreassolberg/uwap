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

	var AuthorizedClient = function(properties, client, targetApp) {
		this.properties = properties;
		this.client = client;
		this.targetApp = targetApp;

		// console.log("CREATING A NEW AuthorizedClient");
		// console.log(properties, client, targetApp)

	}

	// AuthorizedClient.prototype.isPending = function() {
	// 	console.log("Checking is in_array()", "rest_" + this.properties.id, this.scopes);

	// 	return !in_array("rest_" + this.properties.id, this.scopes);
	// }

	AuthorizedClient.prototype.debug = function() {
		return 'Debug: ' + JSON.stringify(this, undefined, 2);
	}


	AuthorizedClient.prototype.getScopes = function() {
		return ;

		var result = {};
		var that = this;
		var prefix = "rest_" + this.properties.id;

		var smatch = new RegExp(prefix + '_([^_]+)$');

		// console.log ('------ loooking for scopes in ', that.properties.proxy.scopes);


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
				console.log("LOCAL scope of " + scope + " using prefix " + prefix + " is " + localScope, that.properties.proxy.scopes);
				if (that.properties.proxy && that.properties.proxy.scopes && that.properties.proxy.scopes[localScope]) {
					result[scope] = that.properties.proxy.scopes[localScope];
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

	AuthorizedClient.prototype.getView = function() {

		var view = {
			properties: this.properties
		};

		view.client = this.client.getView();
		return view;

	}


	return AuthorizedClient;
});