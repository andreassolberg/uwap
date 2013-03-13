define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		hogan = require('uwap-core/js/hogan'),
		UWAP = require('uwap-core/js/core'),
		appPicker = require('controllers/appPicker'),
		newApp = require('controllers/newApp'),
		newProxy = require('controllers/newProxy'),
		frontpage = require('controllers/frontpage'),
		AppDashboard = require('controllers/AppDashboard'),
		ProxyDashboard = require('controllers/ProxyDashboard'),

		Proxy = require('models/Proxy')
    	;
	
	require("uwap-core/js/uwap-people");

	require('uwap-core/bootstrap/js/bootstrap');	
	require('uwap-core/bootstrap/js/bootstrap-modal');	
	require('uwap-core/bootstrap/js/bootstrap-dropdown');

	
	var tmpl = {
		"appdashboard": require('uwap-core/js/text!templates/appdashboard.html'),
		"proxydashboard": require('uwap-core/js/text!templates/proxydashboard.html'),
		"authhandlereditor": require('uwap-core/js/text!templates/authhandlereditor.html'),
		"authorizationhandler":  require('uwap-core/js/text!templates/authorizationhandler.html'),
		"frontpage": require('uwap-core/js/text!templates/frontpage.html'),
		"newApp": require('uwap-core/js/text!templates/newApp.html'),
		"newProxy": require('uwap-core/js/text!templates/newProxy.html')
	};
	
	console.log('making templates compile');
	var templates = {
		"appdashboard": hogan.compile(tmpl.appdashboard),
		"authhandlereditor": hogan.compile(tmpl.authhandlereditor),
		"authorizationhandler": hogan.compile(tmpl.authorizationhandler),
		"frontpage": hogan.compile(tmpl.frontpage),
		"newApp": hogan.compile(tmpl.newApp),
		"newProxy": hogan.compile(tmpl.newProxy),
		"proxydashboard": hogan.compile(tmpl.proxydashboard)
	};
	console.log('done compile');

	$("document").ready(function() {

		var App = function(el, user) {
			
			
			this.el = el;
			this.user = user;

			this.picker = new appPicker($("ul.applicationlist"));
			this.fpage = new frontpage($("div#appmaincontainer"), function(){}, templates);

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
//			alert('Trying to list apps');
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

				var adash;
				console.log("Appconfig", appconfig);

				if (appconfig.type === 'app') {

					adash = new AppDashboard($("div#appmaincontainer"), appconfig, templates);

				} else if (appconfig.type === 'proxy') {

					adash = new ProxyDashboard($("div#appmaincontainer"), new Proxy(appconfig), templates);

				} else {

					console.error('Does not reckognize this type of app');
					return;
				}

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
			}, templates);
			
			na.activate();
		};
		
		App.prototype.actNewProxy = function(event) {
			var that = this;
			if (event) event.preventDefault();

			console.log("Initiating new Proxy...");
			var na = new newProxy(that.el, function(no) {
				
				// console.log("About to store a new proxy", no); return;

				UWAP.appconfig.store(no, function() {
					console.log("Successully stored new app");

					UWAP.appconfig.list(function(list) {
						that.picker.addList(list);
						that.picker.selectApp(no.id);
					});

				}, function(err) {
					console.log("Error storing new app.");
				});
			}, templates);
			na.activate();
		}

		var app;


		UWAP.auth.require(function(user) {

			console.log("Logged in", user);
			app = new App($("body"), user);

		});


	});

});

