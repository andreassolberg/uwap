define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),

		AuthorizedClient = require('./AuthorizedClient'),

		App = require('./App'),
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

	var AuthorizationList = function(data) {
		this.data = data;

		this.clients = {};
		this.targetApps = {};
		this.items = [];

		console.log("Getting elemenet ", this.data);

		if (this.data.targetApps) {
			for(var appid in this.data.targetApps) {
				this.targetApps[appid] = new App(this.data.targetApps[appid]);
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
					this.targetApps[this.data.items[i].targetApp] 		// targetApp
				));

			}

		}

	}

	AuthorizationList.prototype.getView = function() {

		var view = {
			items: []
		};

		for(var i = 0; i < this.items.length; i++) {
			view.items.push(this.items[i].getView());
		}
		return view;

	}


	return AuthorizationList;
});