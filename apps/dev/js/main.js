define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		hb = require('uwap-core/js/handlebars'),

		UWAP = require('uwap-core/js/core'),
		appPicker = require('controllers/appPicker'),
		newApp = require('controllers/newApp'),
		newProxy = require('controllers/newProxy'),
		newClient = require('controllers/newClient'),
		frontpage = require('controllers/frontpage'),
		AppDashboard = require('controllers/AppDashboard'),
		ClientDashboard = require('controllers/ClientDashboard'),
		ProxyDashboard = require('controllers/ProxyDashboard'),
		ProxyViewDashboard = require('controllers/ProxyViewDashboard'),

		Proxy = require('models/Proxy'),
		Client = require('models/Client')
    	;
	
	require("uwap-core/js/uwap-people");

	require('uwap-core/bootstrap3/js/bootstrap');	
	require('uwap-core/bootstrap3/js/modal');	
	require('uwap-core/bootstrap3/js/dropdown');

	
	UWAP.utils.loadCSS('/css/style.css');

	console.log(" -----> LOADED handlebars", hb);

	var tmpl = {
		"appdashboard": require('uwap-core/js/text!templates/appdashboard.html'),
		"clientdashboard": require('uwap-core/js/text!templates/clientdashboard.html'),
		"proxydashboard": require('uwap-core/js/text!templates/proxydashboard.html'),
		"proxyviewdashboard": require('uwap-core/js/text!templates/proxyviewdashboard.html'),
		"authhandlereditor": require('uwap-core/js/text!templates/authhandlereditor.html'),
		"authorizationhandler":  require('uwap-core/js/text!templates/authorizationhandler.html'),
		"frontpage": require('uwap-core/js/text!templates/frontpage.html'),
		"newApp": require('uwap-core/js/text!templates/newApp.html'),
		"newProxy": require('uwap-core/js/text!templates/newProxy.html'),
		"newClient": require('uwap-core/js/text!templates/newClient.html'),
	};
	
	console.log('making templates compile');
	var templates = {
		"appdashboard": hb.compile(tmpl.appdashboard),
		"clientdashboard": hb.compile(tmpl.clientdashboard),
		"authhandlereditor": hb.compile(tmpl.authhandlereditor),
		"authorizationhandler": hb.compile(tmpl.authorizationhandler),
		"frontpage": hb.compile(tmpl.frontpage),
		"newApp": hb.compile(tmpl.newApp),
		"newProxy": hb.compile(tmpl.newProxy),
		"newClient": hb.compile(tmpl.newClient),
		"proxydashboard": hb.compile(tmpl.proxydashboard),
		"proxyviewdashboard": hb.compile(tmpl.proxyviewdashboard)
	};
	console.log('done compile');

	$("document").ready(function() {

		$('.dropdown-toggle').dropdown()

		var App = function(el, user) {
			
			this.el = el;
			this.user = user;

			this.picker = new appPicker($(".applicationlist"), $.proxy(this.actLoadApp, this));
			this.fpage = new frontpage($("div#appmaincontainer"), function(){}, templates);

			$("span#username").html(this.user.name);


			this.el.on("click", ".navDashboard", $.proxy(this.actFrontpage, this));
			this.el.on("click", ".newAppBtn", $.proxy(this.actNewApp, this));
			this.el.on("click", ".newProxyBtn", $.proxy(this.actNewProxy, this));
			this.el.on("click", ".newClientBtn", $.proxy(this.actNewClient, this));
		
			this.load();

			this.routingEnabled = true;
			$(window).bind('hashchange', $.proxy(this.route, this));
			this.route();

			// this.setNavigationBar([
			// 	{title: "Dashboard", href: "#!/"},
			// 	{title: "Foodle"}
			// ]);

		}


		/**
		 * setHash sets the # fragment of the url to a path without triggering the hashchange event.
		 * Example of use:
		 * 		this.setHash('/object/239487239847');
		 * @param {string} hash The hash path
		 */
		App.prototype.setHash = function(hash) {
			this.routingEnabled = false;
			window.location.hash = '#!' + hash;
			this.routingEnabled = true;
			return window.location.hash;
		}

		/**
		 * Perform routing, triggered by the hashchange event, and is also called on load.
		 * @param  {object} e Event object
		 * @return {[type]}   [description]
		 */
		App.prototype.route = function(e) {
			if (!this.routingEnabled) return;
			var hash = window.location.hash;

			// Assumes that the hash starts with #!/
			if (hash.length < 3) {
				hash = this.setHash('/');
			}
			hash = hash.substr(2);

			var parameters;

			if (hash.match(/^\/$/)) {

				this.load();

			} else if (parameters = hash.match(/^\/config\/([0-9a-z\-]+)$/)) {
				console.log("Item ", parameters[1]);

				this.actLoadApp(parameters[1]);

			} else if (parameters = hash.match(/^\/view\/([0-9a-z\-]+)$/)) {
				console.log("Item ", parameters[1]);

				this.actLoadAppRO(parameters[1]);

			} else if (parameters = hash.match(/^\/client\/([0-9a-z\-]+)$/)) {
				console.log("Item ", parameters[1]);

				this.actLoadClient(parameters[1]);

			} else {
				console.error('No match found for router...');
			}

			// console.log("HASH Change", window.location.hash);
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
					target.append('<li><a class="navDashboard" href="' + obj[i].href + '">' + obj[i].title + '</a> <span class="divider"></span></li>');
				}
			}
		}

		App.prototype.actFrontpage = function(event) {
			if (event) event.preventDefault();
			this.fpage.activate();
			this.picker.unselect();

			this.setHash('/');

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

		/**
		 * Loading a dashboard in read only information page. Does not require owner.
		 * This page displays information about the API as well as links to register a client.
		 * 
		 * @type {[type]}
		 */
		App.prototype.actLoadAppRO = function(appid) {

			var that = this;
			console.log("Selected an app:", appid);
			$("div#appmaincontainer").empty();

			UWAP.appconfig.getView(appid, function(appconfig) {

				var adash;
				console.log("Appconfig", appconfig);

				that.setHash('/view/' + appconfig.id);

				if (appconfig.type === 'app') {

					// adash = new AppDashboard($("div#appmaincontainer"), appconfig, templates);
					alert("not implemented public display of webapp dashboard yet."); return;

				} else if (appconfig.type === 'proxy') {

					// adash = new ProxyDashboard($("div#appmaincontainer"), new Proxy(appconfig), templates);
					rdash = new ProxyViewDashboard($("div#appmaincontainer"), new Proxy(appconfig), templates);

				} else {

					console.error('Does not reckognize this type of app');
					return;
				}

				that.setNavigationBar([
					{title: "Dashboard", href: "#!/"},
					{title: appconfig.name}
				]);

			});

		}

		App.prototype.actLoadClient = function(appid) {
			var that = this;
			console.log("Selected an app:", appid);
			$("div#appmaincontainer").empty();

			UWAP.appconfig.getClient(appid, function(appconfig) {

				var adash;
				console.log("Appconfig", appconfig);

				that.setHash('/client/' + appconfig["client_id"]);

				adash = new ClientDashboard($("div#appmaincontainer"), new Client(appconfig), templates);

				that.setNavigationBar([
					{title: "Dashboard", href: "#!/"},
					{title: appconfig["client_name"]}
				]);
			});
		};

		App.prototype.actLoadApp = function(appid, type) {
			var that = this;

			// console.log("actLoadApp", appid, type)

			if (type === 'client') return this.actLoadClient(appid);

			console.log("Selected an app:", appid);
			$("div#appmaincontainer").empty();

			UWAP.appconfig.get(appid, function(appconfig) {

				var adash;
				console.log("Appconfig", appconfig);

				that.setHash('/config/' + appconfig.id);

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

		/**
		 * Registering a new WebApp.
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
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
		
		/**
		 * Registering a new API Gatekeeper.
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
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

		/**
		 * Registering a new client
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		App.prototype.actNewClient = function(event) {
			var that = this;
			if (event) event.preventDefault();

			console.log("Initiating new Client...");
			var na = new newClient(that.el, function(no) {
				
				console.log("About to store a new client", no); 

				UWAP.appconfig.storeClient(no, function() {
					console.log("Successully stored new app");

					UWAP.appconfig.list(function(list) {
						that.picker.addList(list);
						// that.picker.selectClient(no.client_id);
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

