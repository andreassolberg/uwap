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

		if (!this.properties.scopes) {
			this.properties.scopes = [];
		}
		if (!this.properties.scopes_requested) {
			this.properties.scopes_requested = [];
		}

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


	AuthorizedClient.prototype.getClientID = function() {
		return this.client.get('id');
	}

	AuthorizedClient.prototype.getAuthorizedScopes = function() {
		if (this.properties['scopes']) return this.properties['scopes'];
		return null;
	}

	AuthorizedClient.prototype.getRequestedScopes = function() {
		if (this.properties['scopes_requested']) return this.properties['scopes_requested'];
		return null;
	}

	AuthorizedClient.prototype.getScopes = function() {

		// console.log("About to get scopes from");
		// console.log(this);

		var result = {};
		var that = this;
		// var prefix = "rest_" + this.properties.id;

		var smatch = new RegExp('^rest_([^_]+)($|_([^_]+)$)');

		// console.log ('------ loooking for scopes in ', that.properties.proxy.scopes);


		$.each(['scopes', 'scopes_requested'], function(i, scopeAccess) {

			var scopes = that.properties[scopeAccess];

			// result[scopeAccess] = {};

			$.each(scopes, function(i, scope) {
				var localScope = null, targetApp = null, m;
				if (smatch.test(scope)) {
					m = smatch.exec(scope);
					targetApp = m[1];
					if (m[2]) {
						localScope = m[3];	

						if (!result[scopeAccess]) {
							result[scopeAccess] = {};
						}

						result[scopeAccess][scope] = {};
						result[scopeAccess][scope].localScope = localScope;
						result[scopeAccess][scope].targetApp = targetApp;

						// console.log("TARgETAPP", that.targetApp);
						result[scopeAccess][scope].scopeName = that.targetApp.getScopeName(localScope);

						if (scopeAccess === 'scopes_requested') {
							result['pending'] = true;
						}


					} else {
						localScope = null;

						if (scopeAccess === 'scopes') {
							result['generic_accepted'] = true;
						} else if (scopeAccess === 'scopes_requested') {
							result['generic_requested'] = true;
							result['pending'] = true;
						} 

					}

					
				} else {

					// For now: ignore scopes that is not on the form   rest_<target> or rest_<target>_<localscope>
					return;
				}

				// console.log("LOCAL scope of " + scope + " targetting app " + targetApp + " with local scope " + localScope);
				// if (that.properties.proxy && that.properties.proxy.scopes && that.properties.proxy.scopes[localScope]) {
				// 	result[scope] = that.properties.proxy.scopes[localScope];
				// } else {
				// 	result[scope] = {
				// 		name: "Unknown name"
				// 	}
				// }
				

				// if (targetApp === that.targetApp.get('id')) {
				// 	result[scope].app = that.targetApp.getView();
				// }
				



			});

		});


		// console.log("Result is ", result);
		return result;
		
	}




	AuthorizedClient.prototype.getView = function() {

		var view = {
			properties: this.properties
		};

		view.client = this.client.getView();
		view.scopes = this.getScopes();
		view.app = this.targetApp.getView();

		var tsc = this.getScopes();
		// console.error("TSC", tsc);

		if (view.app && view.app.proxy && view.app.proxy.scopes) {

			for(var key in view.app.proxy.scopes) {

				var fscope = 'rest_' + view.app.id + "_" + key;

				view.app.proxy.scopes[key].authorized = false;
				view.app.proxy.scopes[key].requested = false;

				// console.error("view.scopes.scopes_requested", view.scopes.scopes_requested);

				if (view.scopes.scopes_requested && view.scopes.scopes_requested[fscope]) {
					view.app.proxy.scopes[key].requested = true;
				}


				if (view.scopes.scopes && view.scopes.scopes[fscope]) {
					view.app.proxy.scopes[key].authorized = true;
				}


				// console.error("= DELAING WITH", key, view.app.proxy.scopes[key]);
				// console.error(view.client.scopes);

				// var hs = this.client.hasScope("rest_" + this.targetApp.get('id') + '_'  + key);
				// if (hs === true) {
				// 	view.app.proxy.scopes[key].authorized = true;
				// 	view.app.proxy.scopes[key].requested = false;
				// } else if (hs === false) {
				// 	view.app.proxy.scopes[key].authorized = false;
				// 	view.app.proxy.scopes[key].requested = true;
				// } else {
				// 	view.app.proxy.scopes[key].authorized = false;
				// 	view.app.proxy.scopes[key].requested = false;
				// }
			}
		}

		// console.error("RETURN VIEW");
		// console.log(view);

		return view;

	}


	return AuthorizedClient;
});