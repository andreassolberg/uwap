define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		hb = require('uwap-core/js/handlebars')
		;

	var authzclientsTmplText = require('uwap-core/js/text!templates/components/authorizedAPIs.html');
	var authzclientsTmpl = hb.compile(authzclientsTmplText);


	/*
	 * This controller controls the list of API/Proxies that are already granted access to for the active client.
	 */


	var APIAuthorizationListController = function(client, element, callback) {
		this.selected = null;
		this.clientid = null;

		this.client = client;

		this.callback = callback;
		this.element = element;


		$(this.element).on("click", "tr.client", $.proxy(this.select, this));

		$(this.element).on("click", ".request", $.proxy(this.request, this));
		$(this.element).on("click", ".revokeAll", $.proxy(this.revokeAll, this));

	}


	APIAuthorizationListController.prototype.getScopeSelection = function(targetid, target, include) {

		var scopeRequest = {};
		// console.log("Looking for scopes", target.find('input.scopeInput'));
		target.find('.scopeInput').each(function(i, el) {

			var scope = 'rest_' + targetid + '_' + $(el).data('scope');
			var checked = $(el).prop('checked');

			scopeRequest[scope] = (checked && include);

			// console.log("Found element", i, el);
			// console.log("Found " + scope);
		});

		scopeRequest['rest_' + targetid] = !!include;

		return scopeRequest;

	}

	APIAuthorizationListController.prototype.request = function(e) {

		e.stopPropagation(); e.preventDefault();
		var that = this;
		var target = $(e.currentTarget).closest('tr.clientDetails');
		var targetid = target.data('clientid');

		var scopeRequest = this.getScopeSelection(targetid, target, true);

		console.log("Client id", targetid, "REQUEST", scopeRequest);

		// requestScopes: function(clientid, scopes, callback, errorcallback
		UWAP.appconfig.requestScopes(this.client.get('id'), scopeRequest, that.callback);
	}

	APIAuthorizationListController.prototype.revokeAll = function(e) {

		e.stopPropagation(); e.preventDefault();
		var that = this;
		var target = $(e.currentTarget).closest('tr.clientDetails');
		var targetid = target.data('clientid');

		var scopeRequest = this.getScopeSelection(targetid, target, false);

		console.log("Client id", targetid, "revokeAll", scopeRequest); 

		UWAP.appconfig.requestScopes(this.client.get('id'), scopeRequest, that.callback);

		return;

		// var scopes = this.authorizationlist.getAuthorizedScopes(targetid);
		// var scoperequest = {};
		// for(var i = 0; i < scopes.length; i++) {
		// 	scoperequest[scopes[i]] = false;
		// }

		// var app = this.authorizationlist.getApp();
		// var appid = app.get('id');

		UWAP.appconfig.authorizeClient(appid, targetid, scoperequest, that.callback);

		// console.log("authorization list");
		// console.log("authorize scopes", scopes);
		// console.log("reject client", targetid);


	}


	APIAuthorizationListController.prototype.select = function(e) {
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



	APIAuthorizationListController.prototype.setList = function(authorizationlist) {
		this.authorizationlist = authorizationlist;
		console.log("> Initing authorization list controller");
		console.log(this.authorizationlist);
	}




	/**
	 * 
	 */
	APIAuthorizationListController.prototype.draw = function(func) {

		this.selected = null;
		this.clientid = null;

		console.log("------- draw authorizationList ", this.authorizationlist);
		var container = $(this.element).find('.items');
		container.empty();

		var view = this.authorizationlist.getView();

		console.log("  -----> View");
		console.log(view);
		
		container.append(authzclientsTmpl(view));

	}





	return APIAuthorizationListController;
})

