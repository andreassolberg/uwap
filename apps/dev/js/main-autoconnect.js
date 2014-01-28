define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		hb = require('uwap-core/js/handlebars'),

		UWAP = require('uwap-core/js/core'),
		// appPicker = require('controllers/appPicker'),
		// newApp = require('controllers/newApp'),
		// newProxy = require('controllers/newProxy'),
		// newClient = require('controllers/newClient'),
		// frontpage = require('controllers/frontpage'),
		// AppDashboard = require('controllers/AppDashboard'),
		// ClientDashboard = require('controllers/ClientDashboard'),
		// ProxyDashboard = require('controllers/ProxyDashboard'),
		// ProxyViewDashboard = require('controllers/ProxyViewDashboard'),

		Proxy = require('models/Proxy'),
		Client = require('models/Client')
    	;
	
	require("uwap-core/js/uwap-people");

	require('uwap-core/bootstrap3/js/bootstrap');	
	require('uwap-core/bootstrap3/js/modal');	
	require('uwap-core/bootstrap3/js/dropdown');

	UWAP.utils.loadCSS('/css/style.css');

	// console.log(" -----> LOADED handlebars", hb);

	var tmpl = {
		"autoconnect":require('uwap-core/js/text!templates/autoconnect.html'), 
		"autoconnectExisting":require('uwap-core/js/text!templates/autoconnect-existing.html')
		// "appdashboard": require('uwap-core/js/text!templates/appdashboard.html'),
		// "clientdashboard": require('uwap-core/js/text!templates/clientdashboard.html'),
		// "proxydashboard": require('uwap-core/js/text!templates/proxydashboard.html'),
		// "proxyviewdashboard": require('uwap-core/js/text!templates/proxyviewdashboard.html'),
		// "authhandlereditor": require('uwap-core/js/text!templates/authhandlereditor.html'),
		// "authorizationhandler":  require('uwap-core/js/text!templates/authorizationhandler.html'),
		// "frontpage": require('uwap-core/js/text!templates/frontpage.html'),
		// "newApp": require('uwap-core/js/text!templates/newApp.html'),
		// "newProxy": require('uwap-core/js/text!templates/newProxy.html'),
		// "newClient": require('uwap-core/js/text!templates/newClient.html'),
	};
	
	// console.log('making templates compile');
	var templates = {
		"autoconnect": hb.compile(tmpl.autoconnect),
		"autoconnectExisting": hb.compile(tmpl.autoconnectExisting)
		// "appdashboard": hb.compile(tmpl.appdashboard),
		// "clientdashboard": hb.compile(tmpl.clientdashboard),
		// "authhandlereditor": hb.compile(tmpl.authhandlereditor),
		// "authorizationhandler": hb.compile(tmpl.authorizationhandler),
		// "frontpage": hb.compile(tmpl.frontpage),
		// "newApp": hb.compile(tmpl.newApp),
		// "newProxy": hb.compile(tmpl.newProxy),
		// "newClient": hb.compile(tmpl.newClient),
		// "proxydashboard": hb.compile(tmpl.proxydashboard),
		// "proxyviewdashboard": hb.compile(tmpl.proxyviewdashboard)
	};
	// console.log('done compile');

	$("document").ready(function() {



		var App = function(el, user) {
			
			this.el = el;
			this.main = el.find("#main");
			this.user = user;
			this.data = null;

			$("span#username").html(this.user.name);

			this.el.on("click", ".newClientBtn", $.proxy(this.actNewClient, this));
		
			// console.log("Registering handler to receive message...");
			window.addEventListener("message", $.proxy(this.receiveMessage, this), false);
			// window.addEventListener("message", $.proxy(this.receiveMessage, this), false);
			parent.postMessage({"msg": "ready"}, '*');
		}


		App.prototype.receiveMessage = function(event) {

			// console.log("[Widget] Received message in widget", event.data);
			event.data.origin = event.origin;
			this.setData(event.data);

		};


		App.prototype.setData = function(data) {
			var that = this;
			// console.log("set data", data);

			if (!data.metadata.redirect_uri) {
				return alert('Received configuration but redirect_uri was missing.');
			}

			this.data = data;

			var hashUserID = UWAP.utils.hash(this.user.userid);
			var hashRedirectURI = UWAP.utils.hash(this.data.metadata.redirect_uri);

			this.data.metadata.id = 'uwap-ac-' + hashUserID + '-' + hashRedirectURI;
			// console.log("Creating UserID " + this.user.userid + ' ' + hashUserID  );
			// console.log("Creating hashRedirectURI " + this.data.metadata.redirect_uri + ' ' + hashRedirectURI  );
			// console.log("Generated identifier " + this.data.metadata.id);


			UWAP.appconfig.check(that.data.metadata.id, function(idAvailable) {

				if (idAvailable instanceof UWAP.Error) {
					console.error("Not able to register new application for this reason", idAvailable);
					return;
				}

				var alreadyRegistered = !idAvailable;

				if (alreadyRegistered) {

					UWAP.appconfig.get(that.data.metadata.id, function(appconfig) {
						if (appconfig instanceof UWAP.Error) {
							console.error("Not able to register new application for this reason", appconfig);
							return;
						}

						// console.log("Loaded id " + that.data.metadata.id);
						// console.log(appconfig);

						that.main.empty();
						$(templates['autoconnectExisting'](appconfig)).appendTo(that.main);

						window.parent.postMessage({
							"msg": "appconfig",
							"data": appconfig
						}, that.data.origin);

					});

				} else {

					that.draw();

				}



			});


		}

		App.prototype.draw = function() {
			this.main.empty();
			$(templates['autoconnect'](this.data.metadata)).appendTo(this.main);
			$("#newClientName").focus();

		}



		/**
		 * Registering a new client
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		App.prototype.actNewClient = function(event) {
			var that = this;
			if (event) event.preventDefault();

			// console.log("Initiating new Client...");


			var obj = {};

			// obj.id = $(this.element).find("#newClientIdentifier").val();
			obj['id'] = $(this.main).find("#newClientID").val();
			obj.name = $(this.main).find("#newClientName").val();
			obj.descr = $(this.main).find("#newClientDescr").val();
			obj.redirect_uri = [$(this.main).find("#newClientRedirectURI").val()];
			obj.type = 'client';
			
			// Scopes in initial storage of a new client is not yet supported. New clients are automatically granted
			// userinfo scope, and scopes can be requested later...
			obj.scopes_requested = ['userinfo', 'feedread', 'feedwrite', 'longterm'];

			// console.log("New client is ", obj);


			UWAP.appconfig.store(obj, function(newclient) {

				// console.log("Successully stored new app", newclient);
				// console.log("About to store new client"); alert("stored"); return;

				window.parent.postMessage({
					"msg": "appconfig",
					"data": newclient
				}, that.data.origin);


			}, function(err) {
				console.error("Error storing new app.");
			});

			
		}


		App.prototype.actLoadClient = function(appid) {
			var that = this;
			// console.log("Selected an app:", appid);
			$("div#appmaincontainer").empty();

			UWAP.appconfig.getClient(appid, function(appconfig) {

				var adash;
				// console.log("Appconfig", appconfig);

				that.setHash('/client/' + appconfig["client_id"]);

				adash = new ClientDashboard($("div#appmaincontainer"), new Client(appconfig), templates);

				that.setNavigationBar([
					{title: "Dashboard", href: "#!/"},
					{title: appconfig["client_name"]}
				]);
			});
		};






		$("document").ready(function() {

			function authpopup(callback) {
				var url = UWAP.utils.getAppURL('/auth.html');
				newwindow=window.open(url,'uwap-auth','height=600,width=800');
				if (window.focus) {newwindow.focus()};

				var timer = setInterval(function() {   
				    if(newwindow.closed) {  
				        clearInterval(timer);  
				        callback();
				    }  
				}, 1000);

				return false;
			}



			UWAP.auth.checkPassive(function(user) {

				$("#share-widget-main").show();
				// console.log("LOADING APP WITH USER", user);
				var app = new App($("body"), user);
				// app.setauth(user);

			}, function() {
				$('#notauthorized').show();
				$('#notauthorized').on('click', 'button', function(e) {
					e.preventDefault();
					authpopup(function() {

						UWAP.auth.checkPassive(function(user) {

							$('#notauthorized').hide();
							$("#share-widget-main").show();

							var app = new App($("body"), user)
							// console.log("LOADING APP WITH USER", user);
							// app.setauth(user);

						});

					});

				});
			});

		}); // ready() end


	});

});

