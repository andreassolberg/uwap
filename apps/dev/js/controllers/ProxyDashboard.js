define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core')
		;


	var in_array = function (key, array) {

		var i;
		if (typeof array === 'undefined' || !array.length) return false;
		for(i = 0; i < array.length; i++) {
			if (key === array[i]) return true;
		}
		return false;
	}


	var ProxyDashboard = function(container, appconfig, templates) {
		this.container = container;
		this.appconfig = appconfig;
		this.templates = templates;

		console.log("PROXY", appconfig);

		this.draw();

	}


	ProxyDashboard.prototype.proxy = function(func) {
		return $.proxy(func, this);
	}

	ProxyDashboard.prototype.updateStatus = function() {

	}
	
	ProxyDashboard.prototype.bootstrap = function() {
		var template = $(this.element).find("div.bootstrapform select#bootstrap_template").val();
		console.log("Bootstrapping with template " + template);
		UWAP.appconfig.bootstrap(this.appconfig.id, template, function() {
			alert("Successfully applied bootstrap template to your application.");
		});
	}

	ProxyDashboard.prototype.deleteApp = function() {
		var that = this;
		UWAP.appconfig.updateStatus(this.appconfig.id, {pendingDelete: true, operational: false, listing: false}, function(newstatus) {
			that.appconfig.status = newstatus;
			that.drawAppStatus();
		});
	}

	ProxyDashboard.prototype.listingAdd = function() {
		var that = this;
		UWAP.appconfig.updateStatus(this.appconfig.id, {listing: true}, function(newstatus) {
			that.appconfig.status = newstatus;
			that.drawAppStatus();
		});
	}
	ProxyDashboard.prototype.listingRemove = function() {
		var that = this;
		UWAP.appconfig.updateStatus(this.appconfig.id, {listing: false}, function(newstatus) {
			that.appconfig.status = newstatus;
			that.drawAppStatus();
		});
	}

	ProxyDashboard.prototype.draw = function() {
		console.log("DRAW", this.appconfig);
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
		this.element = $(this.templates['proxydashboard'].render(this.appconfig));

		
		console.log("this element", this.element);
		this.container.empty();
		this.container.append(this.element);

		this.drawAuthzHandlers();
		this.drawAppStatus();
	}

	ProxyDashboard.prototype.drawAppStatus = function() {

		$(this.element).find("div.appstatus div.appstatusMain").empty();
		$(this.element).find("div.appstatus div.listing").empty();
		$(this.element).find("div.appstatus div.deletion").empty();


		if (this.hasStatus(['operational'])) {
			$("div.appstatusMain").append('<p>Application is <span class="label label-success">operational</span>.</p>');
		} else {
			$("div.appstatusMain").append('<p>Application is <span class="label label-fail">not operational</span>.</p>');
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
	}


	ProxyDashboard.prototype.hasStatus = function(statuses) {
		var i;
		for(i = 0; i < statuses.length; i++) {
			if (!in_array(statuses[i], this.appconfig.status)) {
				return false;
			}
		}
		return true;
	}


	ProxyDashboard.prototype.drawAuthzHandlers = function() {
		var that = this;
		$(this.element).find("tbody.authorizationhandlers").empty();

		console.log("About to draw authorizationhandlers");
		console.log(this.element.find("tbody.authorizationhandlers"));

		if (this.appconfig.handlers) {
			$.each(this.appconfig.handlers, function(hid, item) {
				that.handlers[item.id];
				item.id = hid;
				that.handlers[item.id] = item;
				if(item.type == "oauth1" || item.type =="oauth2"){
					item.makeReset = true;
				}
//					handlertmpl = $("#authorizationhandlertmpl").tmpl(item);
				console.log('authorizationhandler template-making');
				handlertmpl = $(that.templates['authorizationhandler'].render(item));
				$(that.element).find("tbody.authorizationhandlers").append(handlertmpl);
				console.log("App has handlers", that.appconfig.handlers, handlertmpl);
				// console.log(that.element.find("tbody.authorizationhandlers"));
			});

		} else {
			that.element.find("tbody.authorizationhandlers").empty()
				.append('<tr><td colspan="5">No authorization handlers registered so far.</td></tr>');
		}		
	}

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
	}

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
	}

	ProxyDashboard.prototype.handlerReset = function(eventObject) {
		console.log("handler", eventObject);
	}

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
	}

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
	}

	ProxyDashboard.prototype.updateIdentifier = function() {
		var id = $(this.element).find("#newAppIdentifier").val();
		$(this.element).find(".newAppIdentifierMirror").html(id);

		if(this.verifytimer) clearTimeout(this.verifytimer);
		this.verifytimer = setTimeout(this.proxy(this.verifyIdentifier), 500);

		if (id !== this.verifiedidentifier) {
			$(this.element).find("span.idlabels").empty();
			this.verified = false;
		}
	}

	ProxyDashboard.prototype.checkIfReady = function() {
		console.log("check if ready");
		var name = $(this.element).find("#newAppName").val();
		if (name.length > 1 && this.verified) {
			console.log("READY")
			// $(this.element).find(".createNewBtn").attr("disabled", "disabled");
			$(this.element).find(".createNewBtn").removeClass("disabled");
		} else {
			console.log("NOT READY")
			// $(this.element).find(".createNewBtn").removeAttr("disabled");
			
			$(this.element).find(".createNewBtn").addClass("disabled");
		}
	}

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
	}

	ProxyDashboard.prototype.activate = function() {
		$(this.element).modal('show');
		$(this.element).find("#newAppIdentifier").focus();
	}

	return ProxyDashboard;



});