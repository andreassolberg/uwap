define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),

		AuthorizedClient = require('./AuthorizedClient'),

		App = require('./App'),
		Proxy = require('./Proxy'),
		Client = require('./Client')
		;

	// var in_array = function (key, array) {

	// 	var i;
	// 	if (typeof array === 'undefined' || !array.length) return false;
	// 	for(i = 0; i < array.length; i++) {
	// 		if (key === array[i]) return true;
	// 	}
	// 	return false;
	// }

	var AuthorizationList = function(data, proxy) {
		this.data = data;

		this.clients = {};
		this.targetApps = {};
		this.items = [];
		this.proxy = proxy;

		this.count = null;
		this.startsWith = null;
		this.limit = null;

		if (data.count) {
			this.count = data.count;
		}
		if (data.startsWith) {
			this.startsWith = data.startsWith;
		}
		if (data.limit) {
			this.limit = data.limit;
		}

		console.log("Getting elemenet ", this.data);

		if (this.data.targetApps) {
			for(var appid in this.data.targetApps) {
				if (this.data.targetApps[appid].type === 'app') {
					this.targetApps[appid] = new App(this.data.targetApps[appid]);
				} else if (this.data.targetApps[appid].type === 'proxy') {
					this.targetApps[appid] = new Proxy(this.data.targetApps[appid]);
				} else {
					throw "Missing [type] property on targetApp";
				}
				
			}			
		}

		if (this.data.clients) {
			for(var clientid in this.data.clients) {
				this.clients[clientid] = new Client(this.data.clients[clientid]);
			}			
		}

		if (this.data.items) {

			for(var i = 0; i < this.data.items.length; i++) {

				this.items.push(new AuthorizedClient(
					this.data.items[i], 								// properties
					this.clients[this.data.items[i].client], 			// client
					// this.targetApps[this.data.items[i].targetApp] 		// targetApp
					this.targetApps[this.data.items[i].targetApp]
				));

			}

		}

	}

	AuthorizationList.prototype.getPager = function() {
		var meta = {
			count: this.count,
			startsWith: this.startsWith,
			limit: this.limit
		};
		return meta;
	}

	AuthorizationList.prototype.getApp = function() {
		return this.proxy;
	}

	AuthorizationList.prototype.getAuthorizedScopes = function(clientid) {
		for(var i = 0; i < this.items.length; i++) {
			// console.log("looking for scopes in this client " + this.items[i].getClientID() + ' === ' + clientid);
			if (this.items[i].getClientID() === clientid) {

				return this.items[i].getAuthorizedScopes();
			}
		}
		return null;
	}

	AuthorizationList.prototype.getRequestedScopes = function(clientid) {
		for(var i = 0; i < this.items.length; i++) {
			// console.log("looking for scopes in this client " + this.items[i].getClientID() + ' === ' + clientid);
			if (this.items[i].getClientID() === clientid) {

				return this.items[i].getRequestedScopes();
			}
		}
		return null;
	}

	AuthorizationList.prototype.getView = function() {

		var view = {
			count: this.count,
			limit: this.limit,
			startsWith: this.startsWith,
			items: []
		};

		for(var i = 0; i < this.items.length; i++) {
			view.items.push(this.items[i].getView());
		}
		return view;

	}


	return AuthorizationList;
});