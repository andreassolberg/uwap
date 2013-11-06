define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		hb = require('uwap-core/js/handlebars')
		;

	var authzclientsTmplText = require('uwap-core/js/text!templates/components/authorizedClients.html');
	var authzclientsTmpl = hb.compile(authzclientsTmplText);


	/*
	 * This controller controls the list of API/Proxies that are already granted access to for the active client.
	 */


	var AuthorizationListController = function(element, callback) {
		this.selected = null;
		this.clientid = null;

		this.callback = callback;
		this.element = element;


		$(this.element).on("click", "tr.client", $.proxy(this.select, this));

		$(this.element).on("click", ".revokeScope", $.proxy(this.revokeScope, this));
		$(this.element).on("click", ".revokeAll", $.proxy(this.revokeAll, this));

	}


	AuthorizationListController.prototype.revokeScope = function(e) {

		e.stopPropagation(); e.preventDefault();

		var targetid = $(e.currentTarget).closest('tr.clientDetails').data('clientid');
		var that = this;
		var scope = $(e.currentTarget).closest('tr.scope').data('scope');
		if (scope !== null) {
			console.log("Scope", scope);
		} else {
			console.log("Generic scope");
		}

		var app = this.authorizationlist.getApp();
		var appid = app.get('id');
		var scopeid = "rest_" + appid;
		if (scope !== null) {
			scopeid += '_' + scope;
		}
		var scoperequest = {};
		scoperequest[scopeid] = false;

		UWAP.appconfig.authorizeClient(appid, targetid, scoperequest, function(data) {

			that.callback(data);

		});

		console.log("revokeScope client", targetid);
	}

	AuthorizationListController.prototype.revokeAll = function(e) {

		e.stopPropagation(); e.preventDefault();
		var that = this;
		var targetid = $(e.currentTarget).closest('tr.clientDetails').data('clientid');

		console.log("revoke all client", targetid);


		var scopes = this.authorizationlist.getAuthorizedScopes(targetid);
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


	AuthorizationListController.prototype.select = function(e) {
		e.stopPropagation(); e.preventDefault();

		var target = $(e.currentTarget);
		var clientid = target.data('clientid');

		// console.log("Selected an entry");
		// console.log('clientid ', clientid, ' was ', this.clientid);

		if (this.clientid !== clientid) {

			if (this.clientid !== null) {
				console.log("= = = = =  CLEARING UP", this.selected);
				this.selected.removeClass('open').next().removeClass('open');
			}

			this.clientid = clientid;
			this.selected = target;

			target.addClass('open').next().addClass('open');
		} else {

			this.selected.removeClass('open').next().removeClass('open');
			this.selected = null; this.clientid = null;

		}

	}



	AuthorizationListController.prototype.setList = function(authorizationlist) {
		this.authorizationlist = authorizationlist;
		console.log("> Initing authorization list controller");
		console.log(this.authorizationlist);
	}




	/**
	 * 
	 */
	AuthorizationListController.prototype.draw = function(func) {

		this.selected = null;
		this.clientid = null;

		console.log("------- draw authorizationList ", this.authorizationlist);
		var container = $(this.element);
		container.empty();

		var view = this.authorizationlist.getView();

		console.log("  -----> View");
		console.log(view);


		
		container.append(authzclientsTmpl(view));

	}





	return AuthorizationListController;
})

