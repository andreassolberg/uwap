define(function(require, exports, module) {

	var 
		$ = require('jquery'),


		AuthorizationList = require('../models/AuthorizationList'),
		AuthorizedClient = require('../models/AuthorizedClient'),

		ListPager = require('./ListPager'),

		hb = require('uwap-core/js/handlebars'),
		uwapsearch = require('search')
		;

	var authzclientsTmplText = require('uwap-core/js/text!templates/components/authorizedAPIs.html');
	var authzclientsTmpl = hb.compile(authzclientsTmplText);


	/*
	 * This controller controls the list of available public APIs that the client may subscribe to.
	 */


	var PublicAuthorizationListController = function(client, element, callback) {

		var that = this;

		this.selected = null;
		this.clientid = null;

		this.client = client;

		this.callback = callback;
		this.element = element;

		this.limit =  5;

		var pagerElement = this.element.find("div.listpager");
		this.listPager = new ListPager(pagerElement, this.limit, $.proxy(this.selectPage, this));

		$(this.element).on("click", "tr.client", $.proxy(this.select, this));

		$(this.element).on("click", ".request", $.proxy(this.request, this));
		$(this.element).on("click", ".revokeAll", $.proxy(this.revokeAll, this));



		this.element.find('#apiSearch').uwapsearch($.proxy(this.search, this), function() {
			return that.element.find('#apiSearch').val();
		});

	};


	PublicAuthorizationListController.prototype.search = function(q) {
		console.log("Q", q);

		var query = this.listPager.getQuery();

		if (q !== '') {
			query.query = q;
		}

		this.update(query);

	}

	PublicAuthorizationListController.prototype.selectPage = function(query) {

		this.update(query);

	}

	PublicAuthorizationListController.prototype.update = function(query) {

		var that = this;

		var query = query || {};
		query.limit = this.limit;


		UWAP.appconfig.getPublicAPIs(this.client.get('id'), query, function(data) {
			console.log("Set data for publicAPIlisting", data);

			var publicAPIlist = new AuthorizationList(data, that.client);

			console.log("Public API LIST", publicAPIlist);
	
			that.listPager.setSet(publicAPIlist);

			that.setList(publicAPIlist);
			that.draw();
		});

	}

	PublicAuthorizationListController.prototype.getScopeSelection = function(targetid, target, include) {

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


	PublicAuthorizationListController.prototype.request = function(e) {

		e.stopPropagation(); e.preventDefault();
		var that = this;
		var target = $(e.currentTarget).closest('tr.clientDetails');
		var targetid = target.data('clientid');

		var scopeRequest = this.getScopeSelection(targetid, target, true);

		console.log("Client id", targetid, "REQUEST", scopeRequest);

		// requestScopes: function(clientid, scopes, callback, errorcallback
		UWAP.appconfig.requestScopes(this.client.get('id'), scopeRequest, that.callback);
	}

	PublicAuthorizationListController.prototype.revokeAll = function(e) {

		e.stopPropagation(); e.preventDefault();
		var that = this;
		var target = $(e.currentTarget).closest('tr.clientDetails');
		var targetid = target.data('clientid');

		var scopeRequest = this.getScopeSelection(targetid, target, false);

		console.log("Client id", targetid, "revokeAll", scopeRequest); 

		UWAP.appconfig.requestScopes(this.client.get('id'), scopeRequest, that.callback);

	}

	PublicAuthorizationListController.prototype.select = function(e) {
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



	PublicAuthorizationListController.prototype.setList = function(authorizationlist) {
		this.authorizationlist = authorizationlist;
		console.log("> Initing authorization list controller PUBLIC API");
		console.log(this.authorizationlist);
	}




	/**
	 * 
	 */
	PublicAuthorizationListController.prototype.draw = function(func) {

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





	return PublicAuthorizationListController;
})

