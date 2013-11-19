define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		Class = require('uwap-core/js/class'),
		ModalController = require('../lib/ModalController'),

		hb = require('uwap-core/js/handlebars')
		;

	var template = hb.compile(require('uwap-core/js/text!../../templates/deletegroup.html'));


	/*
	 * This controller controls 
	 */
	var DeleteGroupController = ModalController.extend({
		"init": function() {

			this.template = template;
			console.log("initiator (DeleteGroupController)");
			this._super();

		},

		"obtainObject": function() {
			var obj = {};
			return obj;
		}

	});


	return DeleteGroupController;

});