define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		Class = require('uwap-core/js/class'),
		Controller = require('../lib/Controller'),
		hb = require('uwap-core/js/handlebars')
		
		;

	var tmpl =require('uwap-core/js/text!../../templates/grouptypeselector.html');
	var template = hb.compile(tmpl);



	var GroupListFilter = Controller.extend({
		"init": function(groups) {

			// console.log("initiator (GroupListFilter)");
			// UWAP.utils.stack('initiator (GroupListController)');

			var that = this;

			this.filter = {};			
			this.groups = groups;

			this.el = $('#filterContainer');

			this._super();


			this.el.on('change', this.el.find('input:radio[name="grouptype"]'), $.proxy(this.updateFilterFromControls, this));

			// $(this.el).on("click", "tr.item", $.proxy(this._evntSelect, this));
			// $(this.el).on("click", ".actEditGroup", $.proxy(this._evntEditGroup, this));
			// $(this.el).on("click", ".actDeleteGroup", $.proxy(this._evntDeleteGroup, this));

		},

		"updateFilterFromControls": function() {
			var that = this;


			var newGroupType = $('input[name="grouptype"]:checked').val();
			if (newGroupType === '_all') {
				delete that.filter.groupType;
			} else {
				that.filter.groupType = newGroupType;
			}

			var newRoleType = $('input[name="roletype"]:checked').val();
			if (newRoleType === '_all') {
				delete that.filter.roleType;
			} else {
				that.filter.roleType = newRoleType;
			}


			that.emit('filterUpdate', that.filter);
		},

		"matchFilter": function(item) {
			// console.log("Matching filter for g");
			if (this.filter && this.filter.groupType) {
				console.log("Matching filter for group type", item.groupType.id, '===', this.filter.groupType);
				if (item && item.groupType && item.groupType.id && item.groupType.id === this.filter.groupType) {
					// return true;
				} else {
					return false;
				}
			}
			if (this.filter && this.filter.roleType) {
				console.log("Matching filter for role type", item.vootRole.basic, '===', this.filter.roleType);
				if (item && item.vootRole && item.vootRole.basic && item.vootRole.basic === this.filter.roleType) {
					// return true;
				} else {
					return false;
				}
			}
			return true;

		},

		"load": function() {

			var that = this;
			var groupTypes = this.groups.getGroupTypes();

			console.log("Group types,", groupTypes, this.filter);


			var roleTypes = {
				"member": {
					"displayName": "Member"
				},
				"admin": {
					"displayName": "Admin"
				},
				"owner": {
					"displayName": "Owner"
				}
			}

			// this.el.empty();
			// this.el.append('<div>All group types</div>');


			var countersTypes = {};
			var countersRoles = {};
			var total = 0;


			var groups = this.groups.getView();



			$.each(groups.Resources, function(i, item) {
				// console.log("GROUP " + i, item);
				total++;
				if (item.groupType && item.groupType.id) {
					if (!countersTypes[item.groupType.id]) {
						countersTypes[item.groupType.id] = 0;
					}
					countersTypes[item.groupType.id]++;
				}
				if (item.vootRole && item.vootRole.basic) {
					if (!countersRoles[item.vootRole.basic]) {
						countersRoles[item.vootRole.basic] = 0;
					}
					countersRoles[item.vootRole.basic]++;
				}
			});

			$.each(groupTypes, function(i, item) {
				groupTypes[i].counter = 0;
				if (countersTypes[i]) {
					groupTypes[i].counter = countersTypes[i];
				}
			});

			$.each(roleTypes, function(i, item) {
				roleTypes[i].counter = 0;
				if (countersRoles[i]) {
					roleTypes[i].counter = countersRoles[i];
				}
			});



			var obj = {
				"groupTypes": groupTypes,
				"roleTypes": roleTypes,
				"total": total,
				"countersTypes": countersTypes,
				"countersRoles": countersRoles
			};
			console.log("Draw object", obj);

			this.el.empty().append(template(obj));



			// console.log(this.groups.getView());

			// $.each(this.groups) {
			// }

		}

	});



	return GroupListFilter;
})

