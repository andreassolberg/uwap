define(function(require, exports, module) {

	var 

		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		jso = require('uwap-core/js/oauth'),
		models = require('uwap-core/js/models'),

		panes = require('controllers/panes'),

		AppController = require('../lib/AppController'),

		NewGroupController = require('./NewGroupController'),
		DeleteGroupController = require('./DeleteGroupController'),
		GroupListController = require('./GroupListController'),
		GroupEditController = require('./GroupEditController'),

		hb = require('uwap-core/js/handlebars'),
		moment = require('uwap-core/js/moment'),
		prettydate = require('uwap-core/js/pretty')
		;

	require('uwap-core/bootstrap3/js/bootstrap');	
	require('uwap-core/bootstrap3/js/collapse');
	require('uwap-core/bootstrap3/js/button');
	require('uwap-core/bootstrap3/js/dropdown');	


	require("uwap-core/js/uwap-people");



	var tmpl = {
		"grouplist": require('uwap-core/js/text!../../templates/grouplist.html')
	};
	var templates = {
		"grouplist": hb.compile(tmpl.grouplist),
	};
	

	var App = AppController.extend({
		"init": function(user) {

			var that = this;


			// Call contructor of the AppController(). Takes no parameters.
			this._super();

			this.user = user;

			console.log("Setting username is", this.user.name );
			$("span#username").html(this.user.name);

			// Setup all application controllers
			this.pc = new panes.PaneController(this.el.find('#panecontainer'));

			this.grouplistcontroller = new GroupListController(this.pc.get('grouplist'), user);
			this.groupeditcontroller = new GroupEditController(this.pc.get('groupedit'));

			this.grouplistcontroller.on('editGroup', $.proxy(this.editGroup, this));
			this.grouplistcontroller.on('deleteGroup', $.proxy(this.deleteGroup, this));

			this.newgroupcontroller = new NewGroupController();
			this.newgroupcontroller.on('save', $.proxy(this.saveNewGroup, this));
			this.newgroupcontroller.on('dismiss', function(group) {
				console.log("Opening a list");
				that.listGroups();
			});

			// Define routes..
			this.setupRoute(/^\/$/, "listGroups");
			this.setupRoute(/^\/group\/([a-zA-Z0-9_\-:]+)$/, "editGroupByID");
			this.setupRoute(/^\/new$/, "newGroup");

			this.route();

			setInterval(function(){ 
				$("span.ts").prettyDate(); 
			}, 8000);

		},

		"reloadGroups": function() {
			var that = this;
			UWAP.groups.listMyGroups(function(groups) {
				console.log("Got new set of updated groups", groups);

				var g = new models.Groups();
				g.addProps(groups);

				that.user.groups = g;
				that.grouplistcontroller.draw(false);
			});

		},

		"deleteGroup": function(group) {
			var that = this;
			var dgc = new DeleteGroupController();
			dgc.enable(group);
			dgc.on('save', function() {

				console.log("DELETE GROUP", group);
				UWAP.groups.removeGroup(group.id, function() {
					that.reloadGroups();
				});

			});
		},

		"saveNewGroup": function(group) {
			var that = this;

			console.log("About to store new group", group);
			UWAP.groups.addGroup(group, function(storedGroup) {

				console.log("Successfully stored new group", storedGroup);
				that.user.groups[storedGroup.id] = storedGroup;
				that.editGroup(storedGroup);

				that.reloadGroups();

			}, function(err) {
				console.error("Error creating new group");
			});

		},

		"listGroups": function(a) {
			this.setHash('/');
			this.grouplistcontroller.load();
		},
		"editGroupByID": function(groupid) {
			var group = this.user.groups.getByID(groupid);
			if (group === null) {
				console.error("Could not load this group: ", groupid);
				return;
			}
			this.editGroup(group);
		},
		"editGroup": function(group) {
			this.setHash('/group/' + group.id);
			this.groupeditcontroller.editGroup(group);
		},
		"newGroup": function() {
			this.setHash('/new');
			this.newgroupcontroller.enable();
		}

	});


	App.initialize = function() {

		var app;
		$("document").ready(function() {

			$('.dropdown-toggle').dropdown();

			UWAP.auth.require(function(data) {
				var user = new models.User(data);
				var g = new models.Groups();
				g.addProps(user.groups);
				user.groups = g;

				console.log("Groups object", g);
				app = new App(user);
			});
		});

	}




	return App;


});