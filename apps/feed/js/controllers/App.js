define(function(require, exports, module) {

	var 

		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		jso = require('uwap-core/js/oauth'),

		models = require('uwap-core/js/models'),

		FeedController = require('./FeedController'),
		ItemController = require('./ItemController'),

		PostController = require('./PostController'),

		FeedSelector = require('./FeedSelector'),

		// GroupSelectorController = require('GroupSelectorController'),
		// GroupSelectorControllerBar = require('GroupSelectorControllerBar'),

		NotificationsController = require('./NotificationsController'),

		panes = require('controllers/panes'),

		moment = require('uwap-core/js/moment'),
		hogan = require('uwap-core/js/hogan'),
		prettydate = require('uwap-core/js/pretty')
		;


	require('uwap-core/bootstrap3/js/bootstrap');	
	require('uwap-core/bootstrap3/js/collapse');
	require('uwap-core/bootstrap3/js/button');
	require('uwap-core/bootstrap3/js/dropdown');	


	var tmpl = {
		"subscriptionList": require('uwap-core/js/text!templates/subscriptionList.html'),
		"upcomingItem":  require('uwap-core/js/text!templates/upcomingItem.html')
	};




	var App = function(el) {
		var that = this;
		this.el = el;
		this.groups = {};

		$('.dropdown-toggle').dropdown()


		this.templates = {
			"subscriptionList": hogan.compile(tmpl.subscriptionList),
			"upcomingItem": hogan.compile(tmpl.upcomingItem)
		};

		// this.navbar = new panes.NavBar(this.el.find('#navbar'));


		// Pane Controller
		this.pc = new panes.PaneController(this.el.find('#panecontainer'));

		// The main newsfeed pane is the one containing the default feed.
		this.mainnewsfeedPane = this.pc.get('newsfeed');
		this.mainnewsfeed = new FeedController(this.mainnewsfeedPane, this);
		this.mainnewsfeedPane.activate();
		this.mainnewsfeedPane.on('activate', function() {
			// that.setHash('/');
		});
		this.mainnewsfeedPane.on('deactivate', function() {
			$("#viewbarcontroller").hide();
		});
		this.mainnewsfeedPane.on('activate', function() {
			$("#viewbarcontroller").show();

		});


		// The post pane is the pane that allows you to post a new item.
		this.postpane = this.pc.get('post');
		this.postcontroller = new PostController(this.postpane);
		this.postcontroller.onPost(function(d) {
			that.mainnewsfeed.post(d);
			that.mainnewsfeedPane.activate();
		});

		this.postpane.on('deactivate', function() {
			$("#postEnable").show();
		});
		this.postpane.el.on('click', '#postDisableBtn', $.proxy(function() {
			this.mainnewsfeedPane.activate();
			this.postDisable();
		}, this));


		// The group controller is the dropdown that allows you to selec tgroup.
		// this.groupcontrollerbar = new GroupSelectorControllerBar(this.el.find('#feedmenu'));
		// this.groupcontrollerbar.onSelect($.proxy(this.mainnewsfeed.setSelector, this.mainnewsfeed));

		this.feedselector = new FeedSelector(this, this.el.find('#feedselector'));
		this.feedselector.onSelect($.proxy(this.mainnewsfeed.setSelector, this.mainnewsfeed));


		// Single item controller is ....
		this.singleitemcontroller = null;

		// Load the upcoming feed. That means event in near future.
		UWAP.feed.upcoming({}, function(data) {
			// console.log("Upcoming response"); console.log(data);
			if (!data.items) return;

			var container = $("#upcoming").empty();
			$.each(data.items, function(i, item) {
				// var h = $("#itemUpcomingTmpl").tmpl(item);
				// 
				var itemview = that.mainnewsfeed.getItemView(item);
				$(that.templates.upcomingItem.render(itemview)).data('object', item).appendTo(container);
				// container.append(h);
			});

		});



		// this.groupcontroller = new GroupSelectorController(this.el.find('ul#navfilter'));
		// this.groupcontroller.onSelect($.proxy(this.mainnewsfeed.setSelector, this.mainnewsfeed));


		// The notifications controller is the dropdown of notifications in the header navbar.
		this.notificationsController = new NotificationsController(this, this.el.find('#feednotifications'));


		// Routing
		this.routingEnabled = true;
		$(window).bind('hashchange', $.proxy(this.route, this));
		this.route();



		$(".loader-hideOnLoad").hide();
		$(".loader-showOnLoad").show();

		this.loadSubscriptions();

		this.el.on('click', '.actSubscribe', $.proxy(this.subscribe, this));
		this.el.on('click', '.actUnsubscribe', $.proxy(this.unsubscribe, this));


		$("body").on('touchstart', 'a.dropdown-toggle', function(e) {
		  e.stopPropagation();
		  console.error("POP");
		});
		$("body").on('touchstart', '.dropdown-menu a', function(e) {
		  e.stopPropagation();
		  console.error("POP");
		});


		this.el.on('click', '#authmenu #actLogout', function(e) {
			if (e) e.preventDefault();
			jso.jso_wipe();
			$(".loader-showOnLoad").hide();
		})


		$(window).on("message", function(e) {
			var data = e.originalEvent.data;  // Should work.
			console.log("UWAP Feed Received postMessage message from one of the iframes", data);

			if(data.action === 'setSize') {
				
				var menupadding = data.extra + 50;
				$("#connect-widget").height(data.size + 34 + menupadding);
				$("#connect-widget").css('margin-bottom', -menupadding);
				console.log("RESIZE", data, "set to ", $("#connect-widget").height());
			}

		});


		setInterval(function(){ 
			$("span.ts").prettyDate(); 
		}, 8000);

	}

	App.prototype.loadSubscriptions = function() {
		var that = this;
		UWAP.groups.listPublic(function(items) {
			var table = $("#subscribeproposals");
			table.empty();
			$.each(items, function(i, item) {
				// table.append($("#groupItem").tmpl(item));
				if (that.subscriptions[item.id]) {
					item.subscribed = true;
				} else {
					item.subscribed = false;
				}
				$(that.templates.subscriptionList.render(item)).data('object', item).appendTo(table);
				// table.append('<tr><td>' + item.title + '</td></tr>');
			});
		});
	}


	App.prototype.subscribe = function(e) {
		var that = this;
		if (e) {
			e.preventDefault();
			e.stopPropagation();
		}

		var targetItem = $(e.currentTarget).closest('div.group');
		var item = targetItem.data('object');

		// console.log("Subscribe to ", item);

		UWAP.groups.subscribe(item.id, function() {
			UWAP.auth.require(function(user) {
				that.user = user;
				that.loadSubscriptions();
				that.mainnewsfeed.load();
			});
			
			
		});

	}

	App.prototype.unsubscribe = function(e) {
		var that = this;
		if (e) {
			e.preventDefault();
			e.stopPropagation();
		}

		var targetItem = $(e.currentTarget).closest('div.group');
		var item = targetItem.data('object');

		// console.log("unsubscribe to ", item);

		UWAP.groups.unsubscribe(item.id, function() {
			UWAP.auth.require(function(user) {
				that.user = user;
				that.loadSubscriptions();
				that.mainnewsfeed.load();
			});
		});

	}



	App.prototype.postEnable = function(e) {
		var that = this;
		if (e) e.preventDefault();
		this.postpane.activate();
		$("#postEnable").hide();
		
		// that.navbar.set([
		// 	{'title': 'Newsfeed', 'href': '/'},
		// 	{'title': 'Post new content', 'href': '/'}
		// ]);				
		
	}

	App.prototype.postDisable = function(e) {
		// e.preventDefault();
		// this.mainnewsfeed.activate();

		$("#postEnable").show();

		// this.mainnewsfeedPane.on('activate', function() {
		// 	that.navbar.set([
		// 		{'title': 'Newsfeed', 'href': '/'}
		// 	]);				
		// });
	}




	App.prototype.setHash = function(hash) {
		this.routingEnabled = false;
		var that = this;

		window.location.hash = '#!' + hash;
		console.log("Setting hash to " + hash);

		// var e = new Error('dummy');
		// var stack = e.stack.replace(/^[^\(]+?[\n$]/gm, '')
		//     .replace(/^\s+at\s+/gm, '')
		//     .replace(/^Object.<anonymous>\s*\(/gm, '{anonymous}()@')
		//     .split('\n');
		// console.log(stack);

		setTimeout(function() {
			that.routingEnabled = true;
		}, 0); // Add to end of current events stack, in order to work with immediate callback oriented functions...
		
		
	}

	App.prototype.route = function(e) {

		console.log("Routing", this.routingEnabled);
		if (!this.routingEnabled) return;
		// console.log("Routing continue", this.routingEnabled);


		var hash = window.location.hash;
		// console.log("Routing...");
		if (hash.length < 3) {
			this.setHash('/');
			hash = window.location.hash;
		}
		hash = hash.substr(2);
		// console.log("Checking hash " + hash);

		var parameters;

		if (hash.match(/^\/$/)) {

			this.mainnewsfeed.setSelector({});
			this.mainnewsfeed.load();

		} else if (parameters = hash.match(/^\/post$/)) {

			this.postEnable();

		} else if (parameters = hash.match(/^\/user\/([0-9A-Za-z\-:_@]+)$/)) {
			// console.log("Group ", parameters[1]);

			// this.mainnewsfeed.setSelector({user: parameters[1]});
			// this.mainnewsfeed.load();

			this.feedselector.selectUser(parameters[1]);


		} else if (parameters = hash.match(/^\/group\/([0-9A-Za-z\-:_]+)$/)) {
			// console.log("Group ", parameters[1]);

			this.feedselector.selectGroup(parameters[1]);

			// this.mainnewsfeed.setSelector({group: parameters[1]});
			// this.mainnewsfeed.load();

		} else if (parameters = hash.match(/^\/item\/([0-9a-z]+)$/)) {
			// console.log("Item ", parameters[1]);

			this.openSingleItem(parameters[1]);

			// this.setSelector({_id: parameters[1]});
			// this.load();

		} else {
			console.error('No match found for router...');
		}

		// console.log("HASH Change", window.location.hash);
	}



	App.prototype.openSingleItem = function(id) {
		var that = this;

		console.log("Open single item", id);

		if (this.singleitemcontroller === null) {
			var osipane = this.pc.get('singleitem');
			this.singleitemcontroller = new ItemController(osipane, this);


			// this.singleitemcontroller.setgroups(this.user.groups);
			// this.singleitemcontroller.setsubscriptions(this.user.subscriptions);
			// this.singleitemcontroller.setuser(this.user);


		}

		// this.navbar.set([
		// 	{'title': 'Newsfeed', 'href': '/'},
		// 	{'title': 'View post', 'href': '/'},
		// ]);

		// console.log("App.prototype.openSingleItem", id)
		this.singleitemcontroller.load(id);

	}


	App.prototype.setauth = function(user, groups, subscriptions) {
		this.user = user;

		if (groups.Resources) {
			for(var i = 0; i < groups.Resources.length; i++) {
				this.groups[groups.Resources[i].id] = groups.Resources[i];
			}			
		}

		// this.groups = groups;
		this.subscriptions = subscriptions;

		$(".myname").empty().append(user.name);


		

		this.postcontroller.setgroups(this.groups);
		// this.groupcontrollerbar.setgroups(user.groups);


		// console.error('setauth groups', user, this.groups, subscriptions);
		console.error('setauth groups', this.groups);

		this.feedselector.setgroups(this.groups, subscriptions);

		this.mainnewsfeed.setgroups(this.groups);
		this.mainnewsfeed.setsubscriptions(subscriptions);
		this.mainnewsfeed.setuser(user);

	}

	App.prototype.getUser = function () {
		return this.user;
	}
	App.prototype.getGroups = function () {
		return this.groups;
	}


	App.init = function() {
		var app;
		$("document").ready(function() {
			// console.log("App.init()");

			// TODO: Load these in async
			UWAP.auth.require(function(userdata) {
				
				UWAP.groups.listMyGroups(function(groups) {

					UWAP.groups.listSubscriptions(function(subscriptions) {
						app = new App($("body"));
						var user = new models.User(userdata);
						app.setauth(user, groups, subscriptions);

					});

				});

			});
		});
	};


	return App;


});