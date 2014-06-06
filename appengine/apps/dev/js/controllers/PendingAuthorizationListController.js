define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		hb = require('uwap-core/js/handlebars')
		;



	var pendingAuthzclientsTmplText = require('uwap-core/js/text!templates/components/pendingAuthorization.html');
	var pendingAuthzclientsTmpl = hb.compile(pendingAuthzclientsTmplText);


	/*
	 * This controller controls the todo list of pending incomming requests from clients that 
	 * would like access to scopes within this API/Proxy
	 */

	var PendingAuthorizationListController = function(element, callback) {

		this.callback = callback;
		this.element = element;

		$(this.element).on("click", ".actGrant", $.proxy(this.actGrant, this));
		$(this.element).on("click", ".actReject", $.proxy(this.actReject, this));


	}



	PendingAuthorizationListController.prototype.actGrant = function(e) {
		e.stopPropagation(); e.preventDefault();
		var that = this;

		var targetid = $(e.currentTarget).closest('div.client').data('clientid');


		var scopes = this.authorizationlist.getRequestedScopes(targetid);
		var scoperequest = {};
		for(var i = 0; i < scopes.length; i++) {
			scoperequest[scopes[i]] = true;
		}
		var app = this.authorizationlist.getApp();
		console.log("App", app);

		console.log("UWAP.appconfig.authorizeClient(", app.get('id'), targetid, scopes, "...");

		UWAP.appconfig.authorizeClient(app.get('id'), targetid, scoperequest, function(data) {

			that.callback(data);

		});

		console.log("authorization list");
		console.log("authorize scopes", scopes);
		console.log("Grant client", targetid);
	}

	PendingAuthorizationListController.prototype.actReject = function(e) {
		e.stopPropagation(); e.preventDefault();
		var targetid = $(e.currentTarget).closest('div.client').data('clientid');
		var that = this;

		var scopes = this.authorizationlist.getRequestedScopes(targetid);
		var scoperequest = {};
		for(var i = 0; i < scopes.length; i++) {
			scoperequest[scopes[i]] = false;
		}

		var app = this.authorizationlist.getApp();
		var appid = app.get('id');

		UWAP.appconfig.authorizeClient(appid, targetid, scoperequest, function(data) {

			that.callback(data);

		});

		console.log("authorization list");
		console.log("authorize scopes", scopes);
		console.log("reject client", targetid);

	}





	PendingAuthorizationListController.prototype.setList = function(authorizationlist) {
		this.authorizationlist = authorizationlist;
		console.log("> Initing authorization list controller");
		console.log(this.authorizationlist);
	}






	/**
	 * 
	 */
	PendingAuthorizationListController.prototype.draw = function(func) {

		this.selected = null;
		this.clientid = null;

		console.log("------- draw authorizationList ", this.authorizationlist);
		var container = $(this.element);
		container.empty();

		var view = this.authorizationlist.getView();

		console.log("  -----> View");
		console.log(view);


		container.append(pendingAuthzclientsTmpl(view));

	}


	return PendingAuthorizationListController;
})

