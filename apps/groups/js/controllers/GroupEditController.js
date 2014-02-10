define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		hb = require('uwap-core/js/handlebars')
		Class = require('uwap-core/js/class'),
		models = require('uwap-core/js/models'),

		Controller = require('../lib/Controller'),
		MemberListController = require('./MemberListController')

		;

	require("uwap-core/js/uwap-people");


	var template = hb.compile(require('uwap-core/js/text!../../templates/editgroup.html'));

	var GroupEditController = Controller.extend({

		"init": function(pane) {
			this.pane = pane;
			this._super(this.pane.el);



			this.ebind('click', '.actEditInfo', '_evntEditInfo');
			this.ebind('click', '.actSaveInfo', '_evntSaveInfo');



		},

		"_evntEditInfo": function(e) {
			e.stopPropagation(); e.preventDefault();

			console.log("enable editing group info");
			
			this.el.find('div.showInfo').hide();
			this.el.find('div.editInfo').show();

		},

		"_evntSaveInfo": function(e) {
			e.stopPropagation(); e.preventDefault();
			var that = this;

			var obj = {};
			obj.title = this.el.find('input#groupname').val();
			obj.description = this.el.find('#groupdescription').val();
			obj.listable = this.el.find('#grouplisting').prop('checked');

			this.group.title = obj.title;
			this.group.description = obj.description;
			this.group.listable = obj.listable;


			UWAP.groups.updateGroup(this.group.id, obj, function(data) {
				console.log("Success saving group update...", data);

				that.el.find('div.showInfo').show();
				that.el.find('div.editInfo').hide();

				that.draw();
			})

		},


		"editGroup": function(group) {
			var that = this;
			this.group = new models.Group(group);
			this.pane.activate();
			this.draw();

			this.memberlistcontroller = new MemberListController(this.el.find('.memberlist'));
			that.memberlistcontroller.on('delete', $.proxy(that.actDelete, that));
			that.memberlistcontroller.on('demote', $.proxy(that.actDemote, that));
			that.memberlistcontroller.on('promote', $.proxy(that.actPromote, that));

			this.loadMembers();
		},

		"loadMembers": function() {
			var that = this;
			UWAP.groups.getMembers(this.group.id, function(data) {
				var members = new models.GroupMembers(data);
				that.memberlistcontroller.setMembers(members);
				console.log("group members successfully loaded, will be drawed...", data);
			});
		},


		"actDemote": function(user) {
			console.log("actDemote", user, this);
			that = this;
			UWAP.groups.updateMember(this.group.id, user.userid, 'member', function(data) {
				that.loadMembers();
			});
		},
		"actPromote": function(user) {
			console.log("actPromote", user, this.group);
			that = this;
			UWAP.groups.updateMember(this.group.id, user.userid, 'admin', function(data) {
				that.loadMembers();
			});
		},
		"actDelete": function(user) {
			console.log("actDelte", user);
			that = this;
			UWAP.groups.removeMember(this.group.id, user.userid, function(data) {
				that.loadMembers();
			});
		},

		"addMember": function(item) {
			var that = this;
			console.log("adding member ", item);

			var obj = {};
			if (item.name) obj.name = item.name;
			if (item.userid) obj.userid = item.userid;
			if (item.mail) obj.mail = item.mail;

			UWAP.groups.addMember(this.group.id, obj, function(data) {
				that.loadMembers();
			});
		},

		"draw": function() {
			var that = this;
			var view = this.group.getView();
			console.log("Draw group", view);
			this.el.empty().append(template(view));

			var ps = $("#peoplesearchContainer").focus().peopleSearch({
				callback: function(item) {
					// console.log("Adding item", item);
					that.addMember(item);
					ps.find('input').focus();
				}
			});

		}


	});

	return GroupEditController;
});