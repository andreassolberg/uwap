define(['./AuthzHandlerEditor'], function(AuthzHandlerEditor) {
	
	var in_array = function (key, array) {

		var i;
		if (typeof array === 'undefined' || !array.length) return false;
		for(i = 0; i < array.length; i++) {
			if (key === array[i]) return true;
		}
		return false;
	}

	var AppDashboard = Spine.Class.sub({
		init: function(container, appconfig) {

			var handlertmpl;
			var that = this;

			this.container = container;
			this.appconfig = appconfig;

			this.draw();		

			$(this.element).find("tbody.authorizationhandlers")
				.on("click", "a.handlerEdit", this.proxy(this.handlerEdit));
			$(this.element).find("tbody.authorizationhandlers")
				.on("click", "a.handlerReset", this.proxy(this.handlerReset));
			$(this.element).find("tbody.authorizationhandlers")
				.on("click", "a.handlerDelete", this.proxy(this.handlerDelete));

			$(this.element).on("click", "div.appstatus button.appDelete", 
				this.proxy(this.deleteApp));

			$(this.element).on("click", "div.appstatus div.listing button.listingAdd", 
				this.proxy(this.listingAdd));
			$(this.element).on("click", "div.appstatus div.listing button.listingRemove", 
				this.proxy(this.listingRemove));


			$(this.element).on('click', '#addNewAuthzHandle', this.proxy(this.handlerNew));

		},
		updateStatus: function() {

		},
		deleteApp: function() {
			var that = this;
			UWAP.appconfig.updateStatus(this.appconfig.id, {pendingDelete: true, operational: false, listing: false}, function(newstatus) {
				that.appconfig.status = newstatus;
				that.drawAppStatus();
			});
		},
		listingAdd: function() {
			var that = this;
			UWAP.appconfig.updateStatus(this.appconfig.id, {listing: true}, function(newstatus) {
				that.appconfig.status = newstatus;
				that.drawAppStatus();
			});
		},
		listingRemove: function() {
			var that = this;
			UWAP.appconfig.updateStatus(this.appconfig.id, {listing: false}, function(newstatus) {
				that.appconfig.status = newstatus;
				that.drawAppStatus();
			});
		},
		draw: function() {
			this.element = $("#appdashboardtmpl").tmpl(this.appconfig);
			console.log("this element", this.element);
			this.container.empty();
			this.container.append(this.element);

			this.drawAuthzHandlers();
			this.drawAppStatus();
		},
		drawAppStatus: function() {

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
		},
		hasStatus: function(statuses) {
			var i;
			for(i = 0; i < statuses.length; i++) {
				if (!in_array(statuses[i], this.appconfig.status)) {
					return false;
				}
			}
			return true;
		},

		drawAuthzHandlers: function() {
			var that = this;
			$(this.element).find("tbody.authorizationhandlers").empty();

			console.log("About to draw authorizationhandlers");
			console.log(this.element.find("tbody.authorizationhandlers"));

			if (this.appconfig.handlers) {
				$.each(this.appconfig.handlers, function(hid, item) {
					item.id = hid;
					handlertmpl = $("#authorizationhandlertmpl").tmpl(item);
					$(that.element).find("tbody.authorizationhandlers").append(handlertmpl);
					console.log("App has handlers", that.appconfig.handlers, handlertmpl);
					// console.log(that.element.find("tbody.authorizationhandlers"));
				});

			} else {
				that.element.find("tbody.authorizationhandlers").empty()
					.append('<tr><td colspan="5">No authorization handlers registered so far.</td></tr>');
			}		
		},
		handlerNew: function() {
			var obj = {"type": "oauth2"};
			var that = this;
			var handlerEditor = new AuthzHandlerEditor(this.container, obj, true, function(item) {
				console.log("Done editing an authorization handler", item);
				UWAP.appconfig.updateAuthzHandler(that.appconfig.id, item, function(handlers) {
					console.log("Autorization handler completed.", handlers);
					that.appconfig.handlers = handlers;
					that.proxy(that.drawAuthzHandlers());

				}, function(error) {
					console.error("Error storing authorization handler edit.");
				});
				
			});
			handlerEditor.activate();
		},
		handlerEdit: function(eventObject) {
			var obj = $(eventObject.target).closest("tr").tmplItem().data;
			obj.new = false;
			var that = this;
			console.log("HandlerEdit on object: ", obj);
			var handlerEditor = new AuthzHandlerEditor(this.container, obj, false, function(item) {
				console.log("Done editing an authorization handler", item);
				UWAP.appconfig.updateAuthzHandler(that.appconfig.id, item, function(handlers) {
					console.log("Autorization handler completed.", handlers);
					that.appconfig.handlers = handlers;
					that.proxy(that.drawAuthzHandlers());

				}, function(error) {
					console.error("Error storing authorization handler edit.");
				});
				
			});
			handlerEditor.activate();
		},
		handlerReset: function(eventObject) {
			console.log("handler", eventObject);
		},
		handlerDelete: function(eventObject) {
			var object = $(eventObject.target).closest("tr").tmplItem().data;
			var that = this;
			console.log("HandlerDelete on object: ", object);

			UWAP.appconfig.deleteAuthzHandler(that.appconfig.id, object.id, function(handlers) {
				console.log("Autorization handler completed.", handlers);
				that.appconfig.handlers = handlers;
				that.proxy(that.drawAuthzHandlers());

			}, function(error) {
				console.error("Error storing authorization handler edit.");
			});
		},
		submit: function() {
			var obj = {};

			obj.id = $(this.element).find("#newAppIdentifier").val();
			obj.name = $(this.element).find("#newAppName").val();
			obj.descr = $(this.element).find("#newAppDescr").val();
			obj.type = 'app';

			// this.trigger("submit", obj);
			this.callback(obj);
			$(this.element).modal("hide");
			$(this.element).remove();
		},
		updateIdentifier: function() {
			var id = $(this.element).find("#newAppIdentifier").val();
			$(this.element).find(".newAppIdentifierMirror").html(id);

			if(this.verifytimer) clearTimeout(this.verifytimer);
			this.verifytimer = setTimeout(this.proxy(this.verifyIdentifier), 500);

			if (id !== this.verifiedidentifier) {
				$(this.element).find("span.idlabels").empty();
				this.verified = false;
			}
		},

		checkIfReady: function() {
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
		},

		verifyIdentifier: function() {
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
		},
		activate: function() {
			$(this.element).modal('show');
			$(this.element).find("#newAppIdentifier").focus();
		}
	});
	// newApp.include(Spine.Events);
	return AppDashboard;

});