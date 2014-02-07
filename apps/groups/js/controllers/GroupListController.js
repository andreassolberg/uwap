define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		Class = require('uwap-core/js/class'),
		Controller = require('../lib/Controller'),
		hb = require('uwap-core/js/handlebars')
		
		;

	var tmpl = {
		"grouplist": require('uwap-core/js/text!../../templates/grouplist.html')
	};
	var templates = {
		"grouplist": hb.compile(tmpl.grouplist),
	};



	/*
	 * This controller controls 
	 */
	var GroupListController = Controller.extend({
		"init": function(pane, user) {

			console.log("initiator (GroupListController)");
			// UWAP.utils.stack('initiator (GroupListController)');

			this.user = user;

			this.itemid = null;
			this.selected = null;

			this.pane = pane;

			this._super(this.pane.el);

			$(this.pane.el).on("click", "tr.item", $.proxy(this._evntSelect, this));
			$(this.pane.el).on("click", ".actEditGroup", $.proxy(this._evntEditGroup, this));
			$(this.pane.el).on("click", ".actDeleteGroup", $.proxy(this._evntDeleteGroup, this));

		},


		"load": function() {
			this.draw();
			this.pane.activate();
		},

		// "reload": function() {
		// 	var that = this;
		// 	UWAP.auth.require(function(data) {
		// 		var user = new models.User(data);
		// 		var g = new models.Groups();
		// 		g.addProps(user.groups);
		// 		user.groups = g;

		// 		that.user = user;
		// 		that.draw();
		// 	});
		// },

		"draw": function(act) {
			var obj = {
				groups: this.user.groups.getView(),
				subscriptions: null
			};

			console.log("About to draw", obj);

			this.el.empty().append(templates.grouplist(obj));

			if (typeof act === 'undefined') {
				act = true;
			}

			if (act) {
				this.pane.activate();	
			}
			
		},

		"_evntDeleteGroup": function(e) {
			e.stopPropagation(); e.preventDefault();
			var itemid = $(e.currentTarget).closest('tr.itemDetails').data('itemid');
			var item = this.user.groups.getByID(itemid);
			this.emit("deleteGroup", item);
		},

		"_evntEditGroup": function(e) {
			e.stopPropagation(); e.preventDefault();
			var itemid = $(e.currentTarget).closest('tr.itemDetails').data('itemid');
			var item = this.user.groups.getByID(itemid);
			this.emit("editGroup", item);
		},

		"_evntSelect": function(e) {
			e.stopPropagation(); e.preventDefault();

			console.log("Select.");

			var target = $(e.currentTarget);
			var itemid = target.data('itemid');

			console.log("Selected an entry", itemid);
			// console.log('clientid ', clientid, ' was ', this.clientid);

			if (this.itemid !== itemid) {

				if (this.itemid !== null) {
					console.log("= = = = =  CLEARING UP", this.selected);
					this.selected.removeClass('open').next().removeClass('open');
				}

				this.itemid = itemid;
				this.selected = target;

				target.addClass('open').next().addClass('open');
			} else {

				this.selected.removeClass('open').next().removeClass('open');
				this.selected = null; this.itemid = null;

			}
		},


	});



	return GroupListController;
})

