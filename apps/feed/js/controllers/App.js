define(function(require, exports, module) {

	var 

		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		jso = require('uwap-core/js/oauth'),

		FeedController = require('controllers/FeedController'),
		ItemController = require('controllers/ItemController'),

		PostController = require('PostController'),

		GroupSelectorController = require('GroupSelectorController'),
		GroupSelectorControllerBar = require('GroupSelectorControllerBar'),

		NotificationsController = require('NotificationsController'),

		panes = require('controllers/panes'),

		moment = require('uwap-core/js/moment'),
		hogan = require('uwap-core/js/hogan'),
		prettydate = require('uwap-core/js/pretty')
		;

	require('uwap-core/bootstrap/js/bootstrap');
	require('uwap-core/bootstrap/js/bootstrap-collapse');
	require('uwap-core/bootstrap/js/bootstrap-button');
	require('uwap-core/bootstrap/js/bootstrap-dropdown');
	// require('uwap-core/bootstrap/js/bootstrap-modal');
    // require('uwap-core/bootstrap/js/bootstrap-tooltip');
	// require('uwap-core/bootstrap/js/bootstrap-transition');
	// require('uwap-core/bootstrap/js/bootstrap-alert');
	// require('uwap-core/bootstrap/js/bootstrap-scrollspy');
	// require('uwap-core/bootstrap/js/bootstrap-tab');
	// require('uwap-core/bootstrap/js/bootstrap-popover');
	// require('uwap-core/bootstrap/js/bootstrap-carousel');
	// require('uwap-core/bootstrap/js/bootstrap-typeahead');


	var tmpl = {
		"subscriptionList": require('uwap-core/js/text!templates/subscriptionList.html'),
		"upcomingItem":  require('uwap-core/js/text!templates/upcomingItem.html')
	};




	var App = function(el) {
		var that = this;
		this.el = el;
		this.groups = {};



		this.templates = {
			"subscriptionList": hogan.compile(tmpl.subscriptionList),
			"upcomingItem": hogan.compile(tmpl.upcomingItem)
		};

		this.navbar = new panes.NavBar(this.el.find('#navbar'));


		this.pc = new panes.PaneController(this.el.find('#panecontainer'));
		this.mainnewsfeedPane = this.pc.get('newsfeed');
		this.mainnewsfeed = new FeedController(this.mainnewsfeedPane, this);
		this.mainnewsfeedPane.activate();

		this.singleitemcontroller = null;

		UWAP.feed.upcoming({}, function(data) {
			console.log("Upcoming response"); console.log(data);
			if (!data.items) return;

			var container = $("#upcoming").empty();
			$.each(data.items, function(i, item) {
				// var h = $("#itemUpcomingTmpl").tmpl(item);
				$(that.templates.upcomingItem.render(item)).data('object', item).appendTo(container);
				// container.append(h);
			});


		});


		// this.groupcontroller = new GroupSelectorController(this.el.find('ul#navfilter'));
		// this.groupcontroller.onSelect($.proxy(this.mainnewsfeed.setSelector, this.mainnewsfeed));

		this.groupcontrollerbar = new GroupSelectorControllerBar(this.el.find('#feedmenu'));
		this.groupcontrollerbar.onSelect($.proxy(this.mainnewsfeed.setSelector, this.mainnewsfeed));

		this.postpane = this.pc.get('post');

		this.postcontroller = new PostController(this.postpane);
		this.postcontroller.onPost($.proxy(this.mainnewsfeed.post, this.mainnewsfeed));

		this.notificationsController = new NotificationsController(this.el.find('#feednotifications'));

		this.routingEnabled = true;
		$(window).bind('hashchange', $.proxy(this.route, this));
		this.route();

		this.mainnewsfeedPane.on('activate', function() {
			that.navbar.set([
				{'title': 'Newsfeed', 'href': '/'}
			]);		
		});

		this.mainnewsfeedPane.on('deactivate', function() {
			$("#viewbarcontroller").hide();
		});
		this.mainnewsfeedPane.on('activate', function() {
			$("#viewbarcontroller").show();
		});


		this.postpane.on('deactivate', function() {
			$("#postEnable").show();

		});
		// $("#postEnable").on('click', '#postEnableBtn', $.proxy(this.postEnable, this));
		this.postpane.el.on('click', '#postDisableBtn', $.proxy(function() {
			this.mainnewsfeedPane.activate();
			this.postDisable();
		}, this));

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
				$(that.templates.subscriptionList.render(item)).data('object', item).appendTo(table);
				// table.append('<tr><td>' + item.title + '</td></tr>');
			});
		});
	}


	App.prototype.subscribe = function(e) {
		var that = this;
		if (e) e.preventDefault();

		var targetItem = $(e.currentTarget).closest('div.group');
		var item = targetItem.data('object');

		console.log("Subscribe to ", item);

		UWAP.groups.subscribe(item.id, function() {
			that.loadSubscriptions();
			that.mainnewsfeed.load();
		});

	}

	App.prototype.unsubscribe = function(e) {
		var that = this;
		if (e) e.preventDefault();

		var targetItem = $(e.currentTarget).closest('div.group');
		var item = targetItem.data('object');

		console.log("unsubscribe to ", item);

		UWAP.groups.unsubscribe(item.id, function() {
			that.loadSubscriptions();
			that.mainnewsfeed.load();
		});

	}



	App.prototype.postEnable = function(e) {
		var that = this;
		if (e) e.preventDefault();
		this.postpane.activate();
		$("#postEnable").hide();
		
		that.navbar.set([
			{'title': 'Newsfeed', 'href': '/'},
			{'title': 'Post new content', 'href': '/'}
		]);				
		
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
		window.location.hash = '#!' + hash;
		// console.log("Setting hash to " + hash);
		this.routingEnabled = true;
	}

	App.prototype.route = function(e) {
		if (!this.routingEnabled) return;
		var hash = window.location.hash;
		// console.log("Routing...");
		if (hash.length < 3) {
			this.setHash('/');
		}
		hash = hash.substr(2);
		// console.log("Checking hash " + hash);

		var parameters;

		if (hash.match(/^\/$/)) {

			this.mainnewsfeed.load();

		} else if (parameters = hash.match(/^\/post$/)) {

			this.postEnable();

		} else if (parameters = hash.match(/^\/item\/([0-9a-z]+)$/)) {
			console.log("Item ", parameters[1]);

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

		if (this.singleitemcontroller === null) {
			var osipane = this.pc.get('singleitem');
			this.singleitemcontroller = new ItemController(osipane, this);
		}

		this.navbar.set([
			{'title': 'Newsfeed', 'href': '/'},
			{'title': 'View post', 'href': '/'},
		]);

		console.log("App.prototype.openSingleItem", id)
		this.singleitemcontroller.load(id);

	}


	App.prototype.setauth = function(user) {
		this.user = user;
		this.groups = user.groups;

		$(".myname").empty().append(user.name);

		// console.error('setauth');

		this.postcontroller.setgroups(user.groups);
		this.groupcontrollerbar.setgroups(user.groups);
		this.mainnewsfeed.setgroups(user.groups);
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
			console.log("App.init()");
			UWAP.auth.require(function(user) {
				console.log("Is authenticated, now start the app.");
				app = new App($("body"))
				app.setauth(user);
			});
		});
	};


	return App;


});