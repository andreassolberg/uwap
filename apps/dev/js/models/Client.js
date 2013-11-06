define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core')
		;


	var Client = function(opts) {
		for(var key in opts) {
			this[key] = opts[key];
		}

		console.log("CREATING NEW CLIENT", this.id);
	}


	Client.prototype.get = function(key) {
		return this[key];
	}

	Client.prototype.getGenericScopes = function() {
		
		var scopes = {
			"userinfo": {
				"name": "User information",
				"descr": "Gives access to authentication, and user attributes."
			},
			"longterm": {
				"name": "Longterm - persistent access"
			},
			"appconfig": {
				"name": "Application config",
				"descr": "Give access to manage set of applications. Used by 'dev' application."
			},
			"feedread": {
				"name": "Gives read access to activity stream API"
			},
			"feedwrite": {
				"name": "Gives write access to activity stream API"
			}
		};

		if (this.scopes) {
			$.each(this.scopes, function(i, item) {
				if(scopes[item]) {
					scopes[item].granted = true;
				}
			});
		}
		if (this.scopes_requested) {
			$.each(this.scopes_requested, function(i, item) {
				if(scopes[item]) {
					scopes[item].requested = true;
				}
			});
		}
		
		return scopes;
	}

	Client.prototype.logo = function() {
		console.log("properties of client is", this.id);
		return UWAP.utils.getEngineURL('/api/media/logo/app/' + this.id);
	}

	Client.prototype.getAppScopes = function() {
		
		var smatch = new RegExp('rest_([^_]+)(_([^_]+))?$');

		var children = {};
		var results = {};

		$.each([ [this.scopes, true], [this.scopes_requested, false] ], function(i, p) {
			var scopes = p[0], access = p[1];

			console.log("About to process scopes", p, scopes);

			if (scopes && scopes.length > 0) {
				$.each(scopes, function(i, scope) {
					var localScope = null;

					if (!smatch.test(scope)) return;

					var m = smatch.exec(scope);

					if (m[3]) {
						if (!children[m[1]]) children[m[1]] = {};
						children[m[1]][m[3]] = {access: access, app: m[1]};

					} else {
						results[m[1]] = {
							access: access,
							app: m[1]
						}
					}

				});
			}


		});

		$.each(children, function(key, item) {
			if (results[key]) {
				results[key].subscopes = children[key];
			}
		});


		console.log("Result is ", results);
		return results;
		
	}

	Client.prototype.hasScope = function(scope) {

		console.error("Checking whether " + this.get('id') + ' client has scope ' + scope, this.scopes, this.scopes_requested);

		var i;
		if (this.scopes) {
			for(i = 0; i < this.scopes.length; i++) {
				if (scope === this.scopes[i]) return true;
			}			
		}
		if (this.scopes_requested) {
			for(i = 0; i < this.scopes_requested.length; i++) {
				if (scope === this.scopes_requested[i]) return false;
			}
		}
		return null;
	}

	Client.prototype.getView = function() {
		var view = {};

		for(var key in this) {
			if (typeof this[key] !== 'function' ) {
				view[key] = this[key];
			}
		}
		view.genericScopes = this.getGenericScopes();
		view.logo = this.logo();
		return view;
	}



	return Client;
});;