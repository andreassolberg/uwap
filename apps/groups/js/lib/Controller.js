define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		Class = require('uwap-core/js/class'),
		EventEmitter = require('./EventEmitter'),

		hb = require('uwap-core/js/handlebars')
		;

	
	var Controller = EventEmitter.extend({
		"init": function(el) {
			this.el = el;
			console.log("initiator (Controller)")

			this._super();
		},
		"ebind": function(type, filter, func) {
			this.el.on(type, filter, $.proxy(this[func], this));
		}
	});

	return Controller;
});