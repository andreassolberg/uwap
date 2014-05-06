define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		Class = require('uwap-core/js/class'),
		ModalController = require('../lib/ModalController'),

		hb = require('uwap-core/js/handlebars')
		;

	var template = hb.compile(require('uwap-core/js/text!../../templates/newgroup.html'));


	/*
	 * This controller controls 
	 */
	var NewGroupController = ModalController.extend({
		"init": function() {

			this.template = template;
			console.log("initiator (NewGroupController)");
			this._super();

		},

		"obtainObject": function() {
			var obj = {};
			obj.displayName = this.el.find('input#groupname').val();
			obj.description = this.el.find('#groupdescription').val();
			obj['public'] = this.el.find('#grouplisting').prop('checked');

			return obj;
		}

	});


	return NewGroupController;

});