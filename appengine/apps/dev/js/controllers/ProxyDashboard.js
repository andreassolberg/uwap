define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),

		AuthorizationListController = require('./AuthorizationListController'),
		PendingAuthorizationListController = require('./PendingAuthorizationListController'),

		AuthorizationList = require('../models/AuthorizationList'),
		AuthorizedClient = require('../models/AuthorizedClient'),

		hb = require('uwap-core/js/handlebars')
		;


	var addScopeTmplText = require('uwap-core/js/text!templates/components/newScope.html');
	var addScopeTmpl = hb.compile(addScopeTmplText);
	var scopesTmplText = require('uwap-core/js/text!templates/components/scopes.html');
	var scopesTmpl = hb.compile(scopesTmplText);
	var endpointsTmplText = require('uwap-core/js/text!templates/components/endpoints.html');
	var endpointsTmpl = hb.compile(endpointsTmplText);


	var count_keys = function(myobj) {
		var c= 0;
		for (var k in myobj) if (myobj.hasOwnProperty(k)) c++;
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


	var ProxyDashboard = function(container, appconfig, templates) {
		// this.container = container;
		
		var that = this;

		this.appconfig = appconfig;
		this.templates = templates;

		this.container = $('<div class="eventlistener"></div>').appendTo(container);

		this.authorizationList = [];

		console.log("PROXY", appconfig);

		

		this.draw();

		this.authorizationlistcontroller = new AuthorizationListController(this.container.find("div#clientauthorizations"), function(data) {
			that.updateAppClients(data);
		});

		this.pendingauthorizationlistcontroller = new PendingAuthorizationListController(this.container.find("div#pendingclientauthorizations"), function(data) {
			that.updateAppClients(data);
		});

		this.getAppClients();




		$(this.container).on("click", "div.scopes tr.scope a.removeScope", 
			this.proxy(this.actRemoveScope));
		$(this.container).on("click", "button.addScope", 
			this.proxy(this.actAddScope));

		$(this.container).on("click", "ul.apis li.api button.actEdit", 
			this.proxy(this.actAPIEdit));
		$(this.container).on("click", "ul.apis li.api button.actSave", 
			this.proxy(this.actAPISave));

		$(this.container).on("change", "input#proxyBasicAccessPolicy", 
			this.proxy(this.actChangePolicy));

		$(this.container).on('click', '.actGrant', this.proxy(this.actGrant));
		$(this.container).on('click', '.actReject', this.proxy(this.actReject));

	};


	ProxyDashboard.prototype.updateAppClients = function(data) {

		var that = this;

		that.authorizationList = new AuthorizationList(data, that.appconfig);
		console.log("Get App Clients results", that.authorizationList);

		that.authorizationlistcontroller.setList(that.authorizationList);
		that.authorizationlistcontroller.draw();

		that.pendingauthorizationlistcontroller.setList(that.authorizationList);
		that.pendingauthorizationlistcontroller.draw();

	};



	ProxyDashboard.prototype.getAppClients = function() {
		var that = this;
		UWAP.appconfig.getAppClients(this.appconfig.id, function(data) {

			that.updateAppClients(data);

		} );
	};

	ProxyDashboard.prototype.getClientPendingRef = function(id) {
		throw new Error('getClientPendingRef()');
		// for(var i = 0; i < this.clients.pending.length; i++) {
		// 	if (this.clients.pending[i].client_id === id) return this.clients.pending[i];
		// }
	};

	ProxyDashboard.prototype.actGrant = function(e) {
		e.stopPropagation(); e.preventDefault();
		var that = this;
		var container = $(e.currentTarget).closest('tr.client');
		var client_id = container.data('clientid');
		var client = this.getClientPendingRef(client_id);

		var prefix = "rest_" + that.appconfig.id;
		var authz = {};
		authz[prefix] = true;
		
		container.find("input.grantScopeItem").each(function(i, item) {
			authz[prefix + '_' + $(item).data('scope')] = $(item).prop('checked');
		});

		UWAP.appconfig.authorizeClient(this.appconfig.id, client_id, authz, function(data) {
			console.log("AUTHORIZED COMPLETED...", data);

			that.getAppClients();		

		});

		console.log("Client identifier", client_id, client, authz);
	};

	ProxyDashboard.prototype.actReject = function(e) {
		e.stopPropagation(); e.preventDefault();
		var client_id = $(e.currentTarget).closest('tr.client').data('clientid');
		var client = this.getClientPendingRef(client_id);
		console.log("Client identifier", client_id, client);
	};

	ProxyDashboard.prototype.actAPIEdit = function(e) {
		e.stopPropagation();
		var el = $(e.currentTarget).parent('li.api');
		el.addClass('edit');
		console.log("Edit", el);
	};

	ProxyDashboard.prototype.actAPISave = function(e) {
		e.stopPropagation();
		var el = $(e.currentTarget).parent('li.api');
		el.removeClass('edit');
		console.log("Save", $(e.currentTarget).parent('li.api'));

		var endpoints = [
			el.find('input').val()
		];
		console.log("Endpoints is ", endpoints, el.find('input'));
		this.appconfig.proxy.endpoints = endpoints;
		this.updateProxy();
	};

	ProxyDashboard.prototype.actAddScope = function(e) {
		var that = this;
		e.stopPropagation();

		// Draw the modal editor for adding a new scope.
		var as = $(addScopeTmpl({}));
		as.appendTo("body").modal();
		as.on('shown', function() {
			as.find("input.newScopeValue").focus();
		});

		// When user has saved the new scope, obtain data and push.
		as.on("click", "#addScopeSubmit", function(e) {
			e.preventDefault(); e.stopPropagation();
			var scopeid = as.find("input.newScopeValue").val();
			var scopedef = {};

			scopedef.name = as.find("input.newScopeName").val();
			if (as.find("input.newScopePolicy").prop('checked')) {
				scopedef.policy = {auto: true};
			} else {
				scopedef.policy = {auto: false};
			}

			as.modal('hide');

			console.log("  ===== Before adding NEW Scope ", that.appconfig.proxy);

			if (!that.appconfig.proxy.scopes) {
				that.appconfig.proxy.scopes = {};
			}
			that.appconfig.proxy.scopes[scopeid] = scopedef;

			console.log("  ===== ADDING NEW Scope ", scopeid, scopedef, that.appconfig.proxy);
			// return;

			// that.appconfig.proxy.scopes.push(svalue);
			that.updateProxy();

		});
		as.on('hidden', function() {
			as.remove();
		});

		// var el = $(e.currentTarget).parent('li.api');
		// el.addClass('edit');
		console.log("add scope");
	};

	ProxyDashboard.prototype.actRemoveScope = function(e) {
		e.stopPropagation(); e.preventDefault();
		// var el = $(e.currentTarget).parent('li.api');
		// el.addClass('edit');
		console.log("remove scope");

		var t = $(e.currentTarget).closest("tr.scope").data('scope');
		console.log("currentTarget", t);

		delete this.appconfig.proxy.scopes[t];
		if (count_keys(this.appconfig.proxy.scopes) === 0) {
			delete this.appconfig.proxy.scopes;
		}

		console.log("DELETING SCOPE", t, this.appconfig);

		// var remaining = [];
		// for(var i = 0; i < this.appconfig.proxy.scopes.length; i++) {
		// 	if (this.appconfig.proxy.scopes[i] !== t) {
		// 		remaining.push(this.appconfig.proxy.scopes[i]);
		// 	}
		// }
		// this.appconfig.proxy.scopes = remaining;
		// console.log("removing ", t, "remaining", remaining);
		this.updateProxy();

	};

	ProxyDashboard.prototype.actChangePolicy = function(e) {
		e.stopPropagation(); e.preventDefault();
		var auto = $(e.currentTarget).prop('checked');
		console.log("Status is ", auto);

		this.appconfig.proxy.policy = {"auto": auto};
		this.updateProxy();

	};

	ProxyDashboard.prototype.proxy = function(func) {
		return $.proxy(func, this);
	};

	ProxyDashboard.prototype.updateStatus = function() {

	};


	
	ProxyDashboard.prototype.bootstrap = function() {
		var template = $(this.element).find("div.bootstrapform select#bootstrap_template").val();
		console.log("Bootstrapping with template " + template);
		UWAP.appconfig.bootstrap(this.appconfig.id, template, function() {
			alert("Successfully applied bootstrap template to your application.");
		});
	};

	ProxyDashboard.prototype.deleteApp = function() {
		var that = this;
		UWAP.appconfig.updateStatus(this.appconfig.id, {pendingDelete: true, operational: false, listing: false}, function(newstatus) {
			that.appconfig.status = newstatus;
			that.drawAppStatus();
		});
	};

	ProxyDashboard.prototype.updateProxy = function() {
		var that = this;
		UWAP.appconfig.updateProxy(this.appconfig.id, this.appconfig.proxy, function(proxy) {
			that.appconfig.proxy = proxy;
			that.drawScopes();
			that.drawEndpoints();
		});
	};

	ProxyDashboard.prototype.listingAdd = function() {
		var that = this;
		UWAP.appconfig.updateStatus(this.appconfig.id, {listing: true}, function(newstatus) {
			that.appconfig.status = newstatus;
			that.drawAppStatus();
		});
	};

	ProxyDashboard.prototype.listingRemove = function() {
		var that = this;
		UWAP.appconfig.updateStatus(this.appconfig.id, {listing: false}, function(newstatus) {
			that.appconfig.status = newstatus;
			that.drawAppStatus();
		});
	};

	ProxyDashboard.prototype.draw = function() {
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

		this.element = $(this.templates.proxydashboard(this.appconfig));

		
		console.log("this element", this.element);
		this.container.empty();
		this.container.append(this.element);

		this.drawScopes();
		this.drawPolicy();
		this.drawEndpoints();

		this.drawAuthzHandlers();
		this.drawAppStatus();
	};

	// ProxyDashboard.prototype.drawClients = function() {
	// 	console.log("draw authorizationList ", this.authorizationList);
	// 	var container = $(this.element).find('div#clientauthorizations');
	// 	container.empty();

	// 	console.log("  -----> View");
	// 	console.log(this.authorizationList.getView());

	// 	container.append(authzclientsTmpl(this.authorizationList.getView()));
	// 	// container.append(authzclientsTmpl(this.clientsAuthorized));

	// }

	ProxyDashboard.prototype.drawPolicy = function() {
		var auto = false;
		if (this.appconfig.proxy && this.appconfig.proxy.policy && this.appconfig.proxy.policy.auto) {
			auto = true;
		}

		$(this.element).find('input#proxyBasicAccessPolicy').attr('checked', auto);

	};

	ProxyDashboard.prototype.drawEndpoints = function() {
		var container = $(this.element).find('div.endpoints');
		container.empty();

		if(!this.appconfig.proxy) return;
		if(!this.appconfig.proxy.endpoints) return;

		container.append(endpointsTmpl(this.appconfig.proxy));
	};

	ProxyDashboard.prototype.drawScopes = function() {

		var container = $(this.element).find('div.scopes');
		container.empty();

		// var tmpl = hb.compile('<li class="scope" data-scope="{{scope}}"><code>{{scope}}</code> ' +
		// 	'<a href="#" style="display: inline" class="removeScope" title="Remove scope" >&times;</a></li>');
		// console.log(this.appconfig.proxy.scopes)

		if (!this.appconfig.proxy) return;
		if (!this.appconfig.proxy.scopes) return;

		container.append(scopesTmpl(this.appconfig.proxy.scopes));

		// $.each(this.appconfig.proxy.scopes, function(i, scope) {
		// 	container.append(tmpl({scope: scope}));
		// 	console.log("Processing scopes", i, scope)
		// });

	};

	ProxyDashboard.prototype.drawAppStatus = function() {

		$(this.element).find("div.appstatus div.appstatusMain").empty();
		$(this.element).find("div.appstatus div.listing").empty();
		$(this.element).find("div.appstatus div.deletion").empty();


		if (this.hasStatus(['operational'])) {
			$("div.appstatusMain").append('<p>Application is <span class="label label-success">operational</span>.</p>');
		} else {
			$("div.appstatusMain").append('<p>Application is <span class="label label-danger">not operational</span>.</p>');
		}

		if (this.hasStatus(['pendingDAV'])) {
			$("div.appstatus").append('<p>Application is beeing setup very recently, wait a few minutes and it should be operational.</p>');
		} 



		if (this.hasStatus(['pendingDelete'])) {
			$("div.deletion").append('<p>This application is <strong>scheduled for deletion</strong>.</p>');
		} else {
			$("div.deletion").append('<p><strong>Deleting an application</strong> will also delete all application files and data.</p>');
			$("div.deletion").append('<p><button class="btn btn-mini btn-danger appDelete">Delete application</button></p>');

			if (this.hasStatus(['listing'])) {
				$("div.listing").append('<p>Including your application in the <strong>public listing</strong> may increase traffic to your application.</p>');
				$("div.listing").append('<p>Your application <a href="https://store.uwap.org" target="_blank"><strong>is currently listed</strong></a>.</p>');
				$("div.listing").append('<p><button class="btn btn-mini btn-warning listingRemove">Remove from listing</button></p>');

			} else {
				$("div.listing").append('<p>Including your application in the <strong>public listing</strong> may increase traffic to your application.</p>');
				$("div.listing").append('<p>Your application is not listed.</p>');
				$("div.listing").append('<p><button class="btn btn-mini btn-success listingAdd">Add to listing</button></p>');
			}
		}
	};


	ProxyDashboard.prototype.hasStatus = function(statuses) {
		var i;
		for(i = 0; i < statuses.length; i++) {
			if (!in_array(statuses[i], this.appconfig.status)) {
				return false;
			}
		}
		return true;
	};


	ProxyDashboard.prototype.drawAuthzHandlers = function() {
		var that = this;
		$(this.element).find("tbody.authorizationhandlers").empty();

		console.log("About to draw authorizationhandlers");
		console.log(this.element.find("tbody.authorizationhandlers"));

		if (this.appconfig.handlers) {
			$.each(this.appconfig.handlers, function(hid, item) {
				// that.handlers[item.id];
				item.id = hid;
				that.handlers[item.id] = item;
				if(item.type == "oauth1" || item.type =="oauth2"){
					item.makeReset = true;
				}
//					handlertmpl = $("#authorizationhandlertmpl").tmpl(item);
				console.log('authorizationhandler template-making');
				handlertmpl = $(that.templates.authorizationhandler(item));
				$(that.element).find("tbody.authorizationhandlers").append(handlertmpl);
				console.log("App has handlers", that.appconfig.handlers, handlertmpl);
				// console.log(that.element.find("tbody.authorizationhandlers"));
			});

		} else {
			that.element.find("tbody.authorizationhandlers").empty()
				.append('<tr><td colspan="5">No authorization handlers registered so far.</td></tr>');
		}		
	};

	ProxyDashboard.prototype.handlerNew = function() {
		var obj = {"type": "oauth2"};
		var that = this;
		var handlerEditor = new AuthzHandlerEditor(this.container, this.appconfig, obj, true, function(item) {
			console.log("Done editing an authorization handler", item);
			UWAP.appconfig.updateAuthzHandler(that.appconfig.id, item, function(handlers) {
				console.log("Autorization handler completed.", handlers);
				that.appconfig.handlers = handlers;
				that.proxy(that.drawAuthzHandlers());

			}, function(error) {
				console.error("Error storing authorization handler edit.");
			});
			
		}, that.templates);
		handlerEditor.activate();
	};

	ProxyDashboard.prototype.handlerEdit = function(eventObject) {
//			var obj = $(eventObject.target).closest("tr").tmplItem().data;
		console.log(this.handlers);
		console.log($(eventObject.target).attr('editid'));
		var obj = this.handlers[$(eventObject.target).attr('editid')];
		obj.new = false;
		var that = this;
		console.log("HandlerEdit on object: ", obj);
		var handlerEditor = new AuthzHandlerEditor(this.container, this.appconfig, obj, false, function(item) {
			console.log("Done editing an authorization handler", item);
			UWAP.appconfig.updateAuthzHandler(that.appconfig.id, item, function(handlers) {
				console.log("Autorization handler completed.", handlers);
				that.appconfig.handlers = handlers;
				that.proxy(that.drawAuthzHandlers());

			}, function(error) {
				console.error("Error storing authorization handler edit.");
			});
			
		}, that.templates);
		handlerEditor.activate();
	};

	ProxyDashboard.prototype.handlerReset = function(eventObject) {
		console.log("handler", eventObject);
	};

	ProxyDashboard.prototype.handlerDelete = function(eventObject) {
//			var object = $(eventObject.target).closest("tr").tmplItem().data;
		var object = this.handlers[$(eventObject.target).attr('editid')];
		var that = this;
		console.log("HandlerDelete on object: ", object);

		UWAP.appconfig.deleteAuthzHandler(that.appconfig.id, object.id, function(handlers) {
			console.log("Autorization handler completed.", handlers);
			that.appconfig.handlers = handlers;
			that.proxy(that.drawAuthzHandlers());

		}, function(error) {
			console.error("Error storing authorization handler edit.");
		});
	};

	ProxyDashboard.prototype.submit = function() {
		var obj = {};

		obj.id = $(this.element).find("#newAppIdentifier").val();
		obj.name = $(this.element).find("#newAppName").val();
		obj.descr = $(this.element).find("#newAppDescr").val();
		obj.type = 'app';

		// this.trigger("submit", obj);
		this.callback(obj);
		$(this.element).modal("hide");
		$(this.element).remove();
	};

	ProxyDashboard.prototype.updateIdentifier = function() {
		var id = $(this.element).find("#newAppIdentifier").val();
		$(this.element).find(".newAppIdentifierMirror").html(id);

		if(this.verifytimer) clearTimeout(this.verifytimer);
		this.verifytimer = setTimeout(this.proxy(this.verifyIdentifier), 500);

		if (id !== this.verifiedidentifier) {
			$(this.element).find("span.idlabels").empty();
			this.verified = false;
		}
	};

	ProxyDashboard.prototype.checkIfReady = function() {
		console.log("check if ready");
		var name = $(this.element).find("#newAppName").val();
		if (name.length > 1 && this.verified) {
			console.log("READY");
			// $(this.element).find(".createNewBtn").attr("disabled", "disabled");
			$(this.element).find(".createNewBtn").removeClass("disabled");
		} else {
			console.log("NOT READY");
			// $(this.element).find(".createNewBtn").removeAttr("disabled");
			
			$(this.element).find(".createNewBtn").addClass("disabled");
		}
	};

	ProxyDashboard.prototype.verifyIdentifier = function() {
		var that = this;
		var id = $(this.element).find("#newAppIdentifier").val();

		if (id === '') {
			$(that.element).find("span.idlabels").empty().append('<span class="label label-important">Cannot be empty</span>');
			return;
		}

		UWAP.appconfig.check(id, function(success) {
			console.log("Success ", success);
			that.verifiedidentifier = id;

			console.log("idlabels", $(that.element), $(that.element).find("span.idlabels"));
			if(success) {
				that.verified = true;
				$(that.element).find("span.idlabels").empty().append('<span class="label label-success">Available</span>');
			} else {
				$(that.element).find("span.idlabels").empty().append('<span class="label label-important">Not available</span>');
			}

			that.checkIfReady();

		}, function(error) {
			console.log("Error ", error);
			that.verifiedidentifier = id;
			that.checkIfReady();
			$(that.element).find("span.idlabels").empty().append('<span class="label label-important">' + error + '</span>');
		});
	};

	ProxyDashboard.prototype.activate = function() {
		$(this.element).modal('show');
		$(this.element).find("#newAppIdentifier").focus();
	};

	return ProxyDashboard;



});