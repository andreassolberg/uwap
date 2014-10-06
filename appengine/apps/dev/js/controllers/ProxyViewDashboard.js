define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),

		AuthorizedClient = require('../models/AuthorizedClient'),

		hb = require('uwap-core/js/handlebars')
		;


	console.log(" -----> LOADED handlebars", hb);

	var addScopeTmplText = require('uwap-core/js/text!templates/components/newScope.html');
	var addScopeTmpl = hb.compile(addScopeTmplText);
	var scopesTmplText = require('uwap-core/js/text!templates/components/scopes.html');
	var scopesTmpl = hb.compile(scopesTmplText);
	var endpointsTmplText = require('uwap-core/js/text!templates/components/endpoints.html');
	var endpointsTmpl = hb.compile(endpointsTmplText);

	var authzclientsTmplText = require('uwap-core/js/text!templates/components/authorizedClients.html');
	var authzclientsTmpl = hb.compile(authzclientsTmplText);

	var count_keys = function(myobj) {
		var c= 0, k;
		for (k in myobj) if (myobj.hasOwnProperty(k)) c++;
		return c;
	};


	var in_array = function (key, array) {

		var i;
		if (typeof array === 'undefined' || !array.length) return false;
		for(i = 0; i < array.length; i++) {
			if (key === array[i]) return true;
		}
		return false;
	};

	//container, appconfig, templates, providerconfig
	var ProxyViewDashboard = function(app, container, appconfig, templates, providerconfig) {
		var that = this;
		this.app = app;
		this.container = container;
		this.appconfig = appconfig;
		this.templates = templates;
		this.providerconfig = providerconfig;

		this.clients = [];

		console.log("Proxy view dashboard opens...", appconfig);

		$(this.container).on('click', '.newClient', $.proxy(function() {
			that.app.actNewClient();
		}, this));

		this.draw();
		this.getAppClients();

		// $(this.container).on('click', '.actGrant', this.proxy(this.actGrant));
		// $(this.container).on('click', '.actReject', this.proxy(this.actReject));

	};

	ProxyViewDashboard.prototype.getAppClients = function() {
		var that = this;
		UWAP.appconfig.getAppClients(this.appconfig.id, function(data) {
			var ni;
			var clientsPending = [];
			var clientsAuthorized = [];
			for(var i = 0; i < data.clients.length; i++) {
				ni = new AuthorizedClient(that.appconfig, data.clients[i]);
				if (ni.isPending()) {
					clientsPending.push(ni);	
				} else {
					clientsAuthorized.push(ni);
				}
				
			}
			console.log("Get App Clients results", data);
			that.clients = {
				"pending": clientsPending,
				"authorized": clientsAuthorized,
			};
			that.drawClients();
		} );
	};

	ProxyViewDashboard.prototype.getClientPendingRef = function(id) {
		for(var i = 0; i < this.clients.pending.length; i++) {
			if (this.clients.pending[i].client_id === id) return this.clients.pending[i];
		}
	};


	ProxyViewDashboard.prototype.proxy = function(func) {
		return $.proxy(func, this);
	};

	ProxyViewDashboard.prototype.updateStatus = function() {

	};



	ProxyViewDashboard.prototype.draw = function() {
		// console.log("DRAW", this.appconfig);
		// this.appconfig.sizeH = this.appconfig['files-stats'].sizeH;
		// this.appconfig.capacityH = this.appconfig['files-stats'].capacityH;
		// this.appconfig.usage = this.appconfig['files-stats'].usage;
		// this.appconfig.count = this.appconfig['user-stats'].count;
		// this.appconfig.appstats = (this.appconfig['appdata-stats'] != null);
		if(this.appconfig.appstats){
			this.appconfig.appsizeH = this.appconfig['appdata-stats'].sizeH;
			this.appconfig.appcapacityH = this.appconfig['appdata-stats'].capacityH;
			this.appconfig.appusage = this.appconfig['appdata-stats'].usage;
		}


		var clientview = this.appconfig.getView();
		clientview.providerconfig = this.providerconfig;
		this.element = $(this.templates.proxyviewdashboard(clientview));
		
		console.log("this element", this.element);
		console.log("this clientview", clientview);
		this.container.empty();
		this.container.append(this.element);

		this.drawAppStatus();

	};


	ProxyViewDashboard.prototype.drawClients = function() {
		console.log("draw clients ", this.clients);
		var container = $(this.element).find('div#clientauthorizations');
		container.empty();

		container.append(authzclientsTmpl(this.clients));

	};





	ProxyViewDashboard.prototype.drawAppStatus = function() {

		// $(this.element).find("div.appstatus div.appstatusMain").empty();
		// $(this.element).find("div.appstatus div.listing").empty();
		// $(this.element).find("div.appstatus div.deletion").empty();


		// if (this.hasStatus(['operational'])) {
		// 	$("div.appstatusMain").append('<p>Application is <span class="label label-success">operational</span>.</p>');
		// } else {
		// 	$("div.appstatusMain").append('<p>Application is <span class="label label-danger">not operational</span>.</p>');
		// }

		// if (this.hasStatus(['pendingDAV'])) {
		// 	$("div.appstatus").append('<p>Application is beeing setup very recently, wait a few minutes and it should be operational.</p>');
		// } 



		// if (this.hasStatus(['pendingDelete'])) {
		// 	$("div.deletion").append('<p>This application is <strong>scheduled for deletion</strong>.</p>');
		// } else {
		// 	$("div.deletion").append('<p><strong>Deleting an application</strong> will also delete all application files and data.</p>');
		// 	$("div.deletion").append('<p><button class="btn btn-mini btn-danger appDelete">Delete application</button></p>');

		// 	if (this.hasStatus(['listing'])) {
		// 		$("div.listing").append('<p>Including your application in the <strong>public listing</strong> may increase traffic to your application.</p>');
		// 		$("div.listing").append('<p>Your application <a href="https://store.uwap.org" target="_blank"><strong>is currently listed</strong></a>.</p>');
		// 		$("div.listing").append('<p><button class="btn btn-mini btn-warning listingRemove">Remove from listing</button></p>');

		// 	} else {
		// 		$("div.listing").append('<p>Including your application in the <strong>public listing</strong> may increase traffic to your application.</p>');
		// 		$("div.listing").append('<p>Your application is not listed.</p>');
		// 		$("div.listing").append('<p><button class="btn btn-mini btn-success listingAdd">Add to listing</button></p>');
		// 	}
		// }
	};


	ProxyViewDashboard.prototype.hasStatus = function(statuses) {
		var i;
		for(i = 0; i < statuses.length; i++) {
			if (!in_array(statuses[i], this.appconfig.status)) {
				return false;
			}
		}
		return true;
	};


	return ProxyViewDashboard;



});