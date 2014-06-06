define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		Controller = require('./Controller')

		;

	
	var AppController = Controller.extend({

		"init": function() {
			
			console.log("initiator (AppController)")

			this._super($("body"));

			$(".loader-hideOnLoad").hide();
			$(".loader-showOnLoad").show();

			// Routing
			
			if (!this.routes) this.routes = [];
			this.routingEnabled = true;
			$(window).bind('hashchange', $.proxy(this.route, this));
			$(window).bind('load', function() {
				console.log("====> onload");
			});

		},

		"setupRoute": function(match, func) {
			if (!this.routes) this.routes = [];
			this.routes.push([match, func]);
		},

		"route": function() {
			
			if (!this.routingEnabled) return;
			// console.log("Routing continue", this.routingEnabled);

			var hash = window.location.hash;
			
			if (hash.length < 3) {
				this.setHash('/');
				hash = window.location.hash;
			}
			hash = hash.substr(2);

			var parameters;

			for(var i = 0; i < this.routes.length; i++) {

				if (parameters = hash.match(this.routes[i][0])) {
					console.log("Found a route match on ", this.routes[i], parameters);
					if (typeof this[this.routes[i][1]] === 'function') {
						var args = Array.prototype.slice.call(parameters, 1);
						this[this.routes[i][1]].apply(this, args);
					}
					return;
				} else {
					console.log("Dit not found a route match on ", this.routes[i]);
				}

			}

			console.error("no match found for this route");

		},

		"setHash": function(hash) {

			console.log("Set hash", hash);
			this.routingEnabled = false;
			var that = this;

			window.location.hash = '#!' + hash;

			setTimeout(function() {
				that.routingEnabled = true;
			}, 0);
		}

	});

	return AppController;
});