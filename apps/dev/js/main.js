define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		appPicker = require('controllers/appPicker'),
		newApp = require('controllers/newApp'),
		newProxy = require('controllers/newProxy'),
		frontpage = require('controllers/frontpage'),
		AppDashboard = require('controllers/AppDashboard')
    	;
	
	require("uwap-core/js/uwap-people");

    require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap/js/bootstrap');	
	require('uwap-core/bootstrap/js/bootstrap-modal');	
	require('uwap-core/bootstrap/js/bootstrap-dropdown');


	$("document").ready(function() {

		var App = function(el, user) {
			this.el = el;
			this.user = user;

			this.picker = new appPicker($("ul.applicationlist"));
			this.fpage = new frontpage($("div#appmaincontainer"));

			$("span#username").html(this.user.name);

			this.picker.bind('selected', $.proxy(this.actLoadApp, this));

			this.el.on("click", "a.navDashboard", $.proxy(this.actFrontpage, this));
			this.el.on("click", ".newAppBtn", $.proxy(this.actNewApp, this));
			this.el.on("click", ".newProxyBtn", $.proxy(this.actNewProxy, this));
		

			this.load();

			// this.setNavigationBar([
			// 	{title: "Dashboard", href: "#!/"},
			// 	{title: "Foodle"}
			// ]);

		}


/*
		<ul id="navigationlist" class="breadcrumb">
			<li><a class="navDashboard" href="#">Dashboard</a> <span class="divider">/</span></li>
			<li class="active">Foodle</li>
		</ul>
 */
		App.prototype.setNavigationBar = function(obj) {
			var target = this.el.find('#navigationlist');
			target.empty();
			for(var i = 0; i < obj.length; i++) {
				if (i === obj.length-1) {
					target.append('<li class="active">' + obj[i].title + '</li>');
				} else {
					target.append('<li><a class="navDashboard" href="' + obj[i].href + '">' + obj[i].title + '</a> <span class="divider">/</span></li>');
				}
			}
		}

		App.prototype.actFrontpage = function(event) {
			if (event) event.preventDefault();
			this.fpage.activate();
			this.picker.unselect();

			this.setNavigationBar([
				{title: "Dashboard", href: "#!/"}
			]);
		};

		App.prototype.load = function() {
			var that = this;
			// this.picker.empty();
			UWAP.appconfig.list(function(list) {
				console.log("List of apps", list);
				that.picker.addList(list);
			});
		};

		App.prototype.actLoadApp = function(appid) {
			var that = this;
			console.log("Selected an app:", appid);
			$("div#appmaincontainer").empty();

			UWAP.appconfig.get(appid, function(appconfig) {
				var adash = new AppDashboard($("div#appmaincontainer"), appconfig);

				console.log("Appconfig", appconfig);

				that.setNavigationBar([
					{title: "Dashboard", href: "#!/"},
					{title: appconfig.name}
				]);
			});
		};

		App.prototype.actNewApp = function(event) {
			var that = this;
			if (event) event.preventDefault();

			console.log("Initiating new app...")

			var na = new newApp(that.el, function(no) {
				
				UWAP.appconfig.store(no, function() {
					console.log("Successully stored new app");

					UWAP.appconfig.list(function(list) {
						that.picker.addList(list);
						that.picker.selectApp(no.id);
					});

				}, function(err) {
					console.log("Error storing new app.");
				});
			});
			
			na.activate();
		};
		
		App.prototype.actNewProxy = function(event) {
			var that = this;
			if (event) event.preventDefault();

			var na = new newProxy(that.el, function(no) {
				
				UWAP.appconfig.store(no, function() {
					console.log("Successully stored new app");

					UWAP.appconfig.list(function(list) {
						that.picker.addList(list);
						that.picker.selectApp(no.id);
					});

				}, function(err) {
					console.log("Error storing new app.");
				});
			});
			na.activate();
		}

		var app;


		UWAP.auth.require(function(user) {

			console.log("Logged in", user);
			app = new App($("body"), user);

		});


	});

});

