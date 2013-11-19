define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		Class = require('uwap-core/js/class'),

		hb = require('uwap-core/js/handlebars')
		;

	
	var EventEmitter = Class.extend({
		"init": function() {
			this._callbacks = {};
			console.log("initiator (EventEmitter)")
		},
		"on": function(type, callback) {
			if (!this._callbacks[type]) {
				this._callbacks[type] = [];
			}
			this._callbacks[type].push(callback);
		},
		"emit": function(type) {
			if (!this._callbacks[type]) return;
			var args = Array.prototype.slice.call(arguments, 1);
			for(var i = 0; i < this._callbacks[type].length; i++) {
				this._callbacks[type][i].apply(this, args);
			}
		}
	});

	return EventEmitter;
});