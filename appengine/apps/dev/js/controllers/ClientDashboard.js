define(function(require, exports, module) {
	

	var 
		$ = require('jquery'),
		jQuery = $,
		UWAP = require('uwap-core/js/core'),

		Client = require('models/Client'),
		Proxy = require('models/Proxy'),

		APIAuthorizationListController = require('./APIAuthorizationListController'),
		PublicAuthorizationListController = require('./PublicAuthorizationListController'),

		AuthorizationList = require('../models/AuthorizationList'),
		AuthorizedClient = require('../models/AuthorizedClient'),

		hb = require('uwap-core/js/handlebars')
		;

	var proxyTmplText = require('uwap-core/js/text!templates/components/proxyListing.html');
	var proxyTmpl = hb.compile(proxyTmplText);


	var ClientDashboard = function(container, appconfig, templates, providerconfig) {

		var that = this;



		this.handlers = {};
		this.currentTerm = null;

		this.container = $('<div class="eventlistener"></div>').appendTo(container);
		
		this.appconfig = appconfig;
		this.templates = templates;
		this.providerconfig = providerconfig;

		this.draw();

		this.authorizedAPIlisting = new APIAuthorizationListController(this.appconfig, this.container.find("div#authorizedAPIContainer"), $.proxy(this.update, this));
		this.publicAPIlisting = new PublicAuthorizationListController(this.appconfig, this.container.find("div#availableAPIContainer"), $.proxy(this.update, this));



		UWAP.appconfig.getAuthorizedAPIs(appconfig.get('id'), function(data) {
			console.log("Set data for authorizedAPIlisting", data);

			that.authorizedAPIlist = new AuthorizationList(data, that.appconfig);

			that.authorizedAPIlisting.setList(that.authorizedAPIlist);
			that.authorizedAPIlisting.draw();
		});

		this.publicAPIlisting.update();

		// UWAP.appconfig.getPublicAPIs(appconfig.get('id'), null, function(data) {
		// 	console.log("Set data for publicAPIlisting", data);

		// 	that.publicAPIlist = new AuthorizationList(data, that.appconfig);

		// 	console.log("Public API LIST", that.publicAPIlist);

		// 	that.publicAPIlisting.setList(that.publicAPIlist);
		// 	that.publicAPIlisting.draw();
		// });


		// $("#proxysearch").uwapsearch($.proxy(this.search, this), function() {
		// 	return $("#proxysearch").val();
		// });
		

		$(this.container).on('click', '.actRequestAccess', $.proxy(this.actRequestAccess, this));
		$(this.container).on('click', '.actRemoveAccess', $.proxy(this.actRemoveAccess, this));
		$(this.container).on('click', '.actRequestAccessGeneric', $.proxy(this.actRequestAccessGeneric, this));
		$(this.container).on('click', '.actRemoveAccessGeneric', $.proxy(this.actRemoveAccessGeneric, this));

	};

	ClientDashboard.prototype.update = function(appconfig) {
		var that = this;
		var client = new Client(appconfig);
		this.appconfig = client;

		// this.draw();

		UWAP.appconfig.getAuthorizedAPIs(this.appconfig.get('id'), function(data) {
			console.error("UPDATE ===== Set data for authorizedAPIlisting", data);
			console.log(that.appconfig);

			that.authorizedAPIlist = new AuthorizationList(data, that.appconfig);

			that.authorizedAPIlisting.setList(that.authorizedAPIlist);
			that.authorizedAPIlisting.draw();
		});

		this.publicAPIlisting.update();



	}


	ClientDashboard.prototype.actRequestAccessGeneric = function(event) {
		event.preventDefault(); event.stopPropagation();
		var that = this;
		var scoperequest = {};
		scoperequest[$(event.currentTarget).closest('.scope').data('scope')] = true;
		UWAP.appconfig.requestScopes(this.appconfig.get('id'), scoperequest, function(data) {
			console.log("client added scopes");
			that.appconfig = new Client(data);
			that.draw();
		});

		console.log("Request Access", this.appconfig['client_id'], scopes);


	}

	ClientDashboard.prototype.actRemoveAccessGeneric = function(event) {
		event.preventDefault(); event.stopPropagation();
		var that = this;
		var scoperequest = {};
		scoperequest[$(event.currentTarget).closest('.scope').data('scope')] = false;
		UWAP.appconfig.requestScopes(this.appconfig.get('id'), scoperequest, function(data) {
			console.log("client remove scopes");
			that.appconfig = new Client(data);
			that.draw();
		});

		console.log("remove Access", this.appconfig.get('id'), scoperequest);


	}



	ClientDashboard.prototype.actRequestAccess = function(event) {
		event.preventDefault(); event.stopPropagation();
		var that = this;
		var t = $(event.currentTarget).closest('.proxy');
		var proxyid = t.data('clientid');

		var scoperequest = {};
		scoperequest["rest_" + proxyid] = true;
		t.find('input.grantScopeItem').each(function(i, item) {
			if ($(item).attr('checked')) {
				scoperequest['rest_' + proxyid + '_' + $(item).data('scope')] = true;
			}
		});

		$("#proxysearch").val('');
		$("div#searchres").empty();
		UWAP.appconfig.requestScopes(this.appconfig.get('id'), scoperequest, function(data) {
			console.log("client added scopes");
			that.appconfig = new Client(data);
			that.draw();
		});

		console.log("Request Access", this.appconfig.get('id'), scoperequest);


	}

	ClientDashboard.prototype.actRemoveAccess = function(event) {
		event.preventDefault(); event.stopPropagation();
		var that = this;
		var t = $(event.currentTarget).closest('.appscope');
		var scope = t.data('scope');

		var scoperequest = {};
		scoperequest[scope] = false;

		UWAP.appconfig.requestScopes(this.appconfig.get('id'), scoperequest, function(data) {
			console.log("client remove scopes");
			that.appconfig = new Client(data);
			that.draw();
		});

		console.log("remove Access", this.appconfig.get('id'),  scoperequest);



	}


	ClientDashboard.prototype.search = function(term) {
		console.log("Search term", term, this.currentTerm);

		

		if (term === this.currentTerm) return;
		this.currentTerm = term;

		if (term === '') {
			$("div#searchres").empty();
			return;
		}


		UWAP.appconfig.query({search: term}, function(data) {
			$("div#searchres").empty();
			var items = [];
			$.each(data, function(i, item) {
				items.push(new Proxy(item));
			});
			console.log("Result", proxyTmpl, items);
			$("div#searchres").append(proxyTmpl(items));
		});
	}


	ClientDashboard.prototype.draw = function() {
		console.log("DRAW", this.appconfig, this.appconfig.getAppScopes());

		var clientview = this.appconfig.getView();
		console.log("CLIENT VIEW CLIENT VIEW ", clientview);

		clientview.providerconfig = this.providerconfig;

		this.element = $(this.templates['clientdashboard'](clientview));

		
		console.log("this element", this.element);
		this.container.empty().append(this.element);

	};






	return ClientDashboard;

});