define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		models = require('uwap-core/js/models'),

		Class = require('uwap-core/js/class'),
		Controller = require('../lib/Controller'),

		hb = require('uwap-core/js/handlebars')
		;

	var tmpl = {
		"grouplist": require('uwap-core/js/text!../../templates/memberlist.html')
	};
	var templates = {
		"grouplist": hb.compile(tmpl.grouplist),
	};



	/*
	 * This controller controls 
	 */
	var MemberListController = Controller.extend({
		"init": function(el, user) {

			console.log("initiator (MemberListController)")

			this.user = user;

			this.itemid = null;
			this.selected = null;

			this.members = null;

			this.el = el;

			this._super(this.el);

			$(this.el).on("click", ".actDelete", $.proxy(this._evntDelete, this));
			$(this.el).on("click", ".actDemote", $.proxy(this._evntDemote, this));
			$(this.el).on("click", ".actPromote", $.proxy(this._evntPromote, this));

		},


		"setMembers": function(members) {
			if (!members instanceof models.GroupMembers) { throw "Invalid groupmembers object provided to MemberListController"; }
			this.members = members;
			this.draw();
		},

		"draw": function() {
			var view = this.members.getView();
			console.log("group member view", view);
			this.el.empty().append(templates.grouplist(view));
		},


		"_evntDelete": function(e) {
			e.stopPropagation(); e.preventDefault();
			var itemid = $(e.currentTarget).closest('tr.item').data('itemid');
			var item = this.members.getByUserID(itemid);
			this.emit("delete", item);
		},
		"_evntPromote": function(e) {
			e.stopPropagation(); e.preventDefault();
			var itemid = $(e.currentTarget).closest('tr.item').data('itemid');
			var item = this.members.getByUserID(itemid);
			this.emit("promote", item);
		},
		"_evntDemote": function(e) {
			e.stopPropagation(); e.preventDefault();
			var itemid = $(e.currentTarget).closest('tr.item').data('itemid');
			var item = this.members.getByUserID(itemid);
			this.emit("demote", item);
		},

	});



	return MemberListController;
})

