define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		Class = require('uwap-core/js/class'),
		Pane = require('../lib/Pane'),

		GroupListFilter = require('./GroupListFilter'),

		hb = require('uwap-core/js/handlebars')
		
		;

	var tmpl = {
		"grouplist": require('uwap-core/js/text!../../templates/grouplist2.html')
	};
	var templates = {
		"grouplist": hb.compile(tmpl.grouplist),
	};



	/*
	 * This controller controls 
	 */
	var GroupListController = Pane.extend({
		"init": function(groups) {

			console.log("initiator (GroupListController)");
			// UWAP.utils.stack('initiator (GroupListController)');

			var that = this;
			this.groups = groups;

			this.itemid = null;
			this.selected = null;

			this._super();

			this.filter = {};

			this.filtercontroller = new GroupListFilter(groups);
			this.filtercontroller.on('filterUpdate', function(newFilter) {
				that.filter = newFilter;
				that.draw();
			})


			$(this.el).on("click", "tr.item", $.proxy(this._evntSelect, this));
			$(this.el).on("click", ".actEditGroup", $.proxy(this._evntEditGroup, this));
			$(this.el).on("click", ".actDeleteGroup", $.proxy(this._evntDeleteGroup, this));

		},


		"load": function() {
			console.log("DRAW");

			this.draw(true);
			this.filtercontroller.load();
			// this.activate();
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

		"getFilteredList": function() {

			var groupCandidates = this.groups.getView();
			var groups = {};

			for(var key in groupCandidates.Resources) {
				if (this.filtercontroller.matchFilter(groupCandidates.Resources[key])) {
					groups[key] = groupCandidates.Resources[key];
				}
			}

			return groups;
			
		},


		"draw": function(act) {
			var obj = {
				groups: this.getFilteredList(),
				subscriptions: null
			};

			console.log(" -----> About to draw", obj, this.groups);

			this.el.empty().append(templates.grouplist(obj));


			if (act) {
				this.activate();
			}
			
		},

		"_evntDeleteGroup": function(e) {
			e.stopPropagation(); e.preventDefault();
			var itemid = $(e.currentTarget).closest('tr').data('itemid');
			var item = this.groups.getByID(itemid);
			this.emit("deleteGroup", item);
		},

		"_evntEditGroup": function(e) {
			
			e.stopPropagation(); e.preventDefault();
			var itemid = $(e.currentTarget).closest('tr').data('itemid');
			if (this.groups)

			var item = this.groups.getByID(itemid);
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

