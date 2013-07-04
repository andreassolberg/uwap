define(function(require, exports, module) {
	

	var 
		$ = require('jquery'),
		jQuery = $,
		UWAP = require('uwap-core/js/core'),

		Client = require('models/Client'),
		Proxy = require('models/Proxy'),

		hb = require('uwap-core/js/handlebars'),
		uwapsearch = require('search')
		;

	var proxyTmplText = require('uwap-core/js/text!templates/components/proxyListing.html');
	var proxyTmpl = hb.compile(proxyTmplText);


	var ClientDashboard = function(container, appconfig, templates) {

		var handlertmpl;
		var that = this;
		this.handlers = {};
		this.currentTerm = null;

		this.container = $('<div class="eventlistener"></div>').appendTo(container);
		
		this.appconfig = appconfig;
		this.templates = templates;

		this.draw();

		$("#proxysearch").uwapsearch($.proxy(this.search, this), function() {
			return $("#proxysearch").val();
		});

		$(this.container).on('click', '.actRequestAccess', $.proxy(this.actRequestAccess, this));
		$(this.container).on('click', '.actRemoveAccess', $.proxy(this.actRemoveAccess, this));
		$(this.container).on('click', '.actRequestAccessGeneric', $.proxy(this.actRequestAccessGeneric, this));
		$(this.container).on('click', '.actRemoveAccessGeneric', $.proxy(this.actRemoveAccessGeneric, this));

	};


	ClientDashboard.prototype.actRequestAccessGeneric = function(event) {
		event.preventDefault(); event.stopPropagation();
		var that = this;
		var scope = $(event.currentTarget).closest('.scope').data('scope');
		UWAP.appconfig.addClientScopes(this.appconfig['client_id'], [scope], function(data) {
			console.log("client added scopes");
			that.appconfig = new Client(data);
			that.draw();
		});

		console.log("Request Access", this.appconfig['client_id'], scopes);


	}

	ClientDashboard.prototype.actRemoveAccessGeneric = function(event) {
		event.preventDefault(); event.stopPropagation();
		var that = this;
		var scope = $(event.currentTarget).closest('.scope').data('scope');
		UWAP.appconfig.removeClientScopes(this.appconfig['client_id'], [scope], function(data) {
			console.log("client remove scopes");
			that.appconfig = new Client(data);
			that.draw();
		});

		console.log("remove Access", this.appconfig['client_id'], scope);


	}



	ClientDashboard.prototype.actRequestAccess = function(event) {
		event.preventDefault(); event.stopPropagation();
		var that = this;
		var t = $(event.currentTarget).closest('.proxy');
		var proxyid = t.data('clientid');
		var scopes = ["rest_" + proxyid];
		t.find('input.grantScopeItem').each(function(i, item) {
			if ($(item).attr('checked')) {
				scopes.push('rest_' + proxyid + '_' + $(item).data('scope'));
			}
		});

		$("#proxysearch").val('');
		$("div#searchres").empty();
		UWAP.appconfig.addClientScopes(this.appconfig['client_id'], scopes, function(data) {
			console.log("client added scopes");
			that.appconfig = new Client(data);
			that.draw();
		});

		console.log("Request Access", this.appconfig['client_id'], scopes);


	}

	ClientDashboard.prototype.actRemoveAccess = function(event) {
		event.preventDefault(); event.stopPropagation();
		var that = this;
		var t = $(event.currentTarget).closest('.appscope');
		var scope = t.data('scope');

		UWAP.appconfig.removeClientScopes(this.appconfig['client_id'], [scope], function(data) {
			console.log("client remove scopes");
			that.appconfig = new Client(data);
			that.draw();
		});

		console.log("remove Access", this.appconfig['client_id'], scope);



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


		this.element = $(this.templates['clientdashboard'](this.appconfig));

		
		console.log("this element", this.element);
		this.container.empty().append(this.element);

	};






	return ClientDashboard;

});