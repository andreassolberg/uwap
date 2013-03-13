define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core')
		;


	var Proxy = function(opts) {
		for(var key in opts) {
			this[key] = opts[key];
		}


		if (this.proxies) {
			this.proxiesArr = [];
			for(var key in this.proxies) {
				this.proxies[key]['id'] = key;
				this.proxiesArr.push(this.proxies[key]);
			}
		}

		if (this['user-stats']) {
			this.count = this['user-stats']['count'];
		} else {
			this.count = null;
		}
	}


	return Proxy;
});