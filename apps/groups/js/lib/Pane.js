define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		utils = require('./utils'),
		Controller = require('./Controller')

		// hb = require('uwap-core/js/handlebars')
		;


	/*
	 * This controller controls 
	 */
	var Pane = Controller.extend({
		"init": function() {
			this.identifier = utils.guid();
			this.panecontroller = null;
			this._super();

			this.el.addClass('pane');
			this.el.data('paneID', this.identifier);
		},
		"registerPaneController": function(pc) {
			this.panecontroller = pc;
		},
		"activate": function() {
			console.log("ACTIVATE PANE");
			if (this.panecontroller === null) throw new Error('Cannot activate pane that is not added to a controller');
			this.panecontroller.activate(this.identifier);
		},
		"deactivate": function() {
			console.log("Deactivating pane with identifier " + this.identifier);
			// TODO trigger an event.
		}

	});

	return Pane;

});