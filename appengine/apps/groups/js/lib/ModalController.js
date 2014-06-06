define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		Class = require('uwap-core/js/class'),
		Controller = require('../lib/Controller'),

		hb = require('uwap-core/js/handlebars')
		;

	var template = hb.compile(require('uwap-core/js/text!../../templates/newgroup.html'));


	/*
	 * This controller controls 
	 */
	var ModalController = Controller.extend({
		"init": function() {

			console.log("initiator (ModalController)")

			this.el = $("<div></div>").appendTo($("div#modalContainer"));

			this._super(this.el);

			this.el.on("click", ".actDismiss", $.proxy(this._evntDismiss, this));
			this.el.on("click", ".actSave", $.proxy(this._evntSave, this));

		},

		"obtainObject": function() {
			return {};
			var obj = {};
			obj.title = this.el.find('input#groupname').val();

			return obj;
		},


		"enable": function(data) {
			this.el.empty();
			this.modalElement = $(this.template(data));
			this.modalElement
				.appendTo(this.el)
				.modal('show');

			this.modalElement.find(".inputFocus").focus();
		},
		"dismiss": function() {
			console.log("About to dismiss a modal element", $(this.modalElement));
			$(this.modalElement).modal('hide');
			this.el.empty();
			$(".modal-backdrop").remove();
		},

		"_evntSave": function(e) {
			e.stopPropagation(); e.preventDefault();
			console.log("Event save()");
			var obj = this.obtainObject();
			this.emit("save", obj);
			this.dismiss();
		},
		"_evntDismiss": function(e) {
			e.stopPropagation(); e.preventDefault();
			console.log("Event dismiss()");

			this.dismiss();
			this.emit('dismiss');
		}
	});

	return ModalController;

});