define(['./AuthzHandlerEditor'], function(AuthzHandlerEditor) {
	
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


			$(this.element).on('click', '#addNewAuthzHandle', this.proxy(this.handlerNew));

			// $(this.element).find("#newAppIdentifier")
			// 	.on("change", this.proxy(this.updateIdentifier))
			// 	.on("keyup", this.proxy(this.updateIdentifier));
			// $(this.element).find("#newAppName")
			// 	.on("change", this.proxy(this.checkIfReady))
			// 	.on("keyup", this.proxy(this.checkIfReady));

			// $(this.element).find(".createNewBtn")
			// 	.on("click", this.proxy(this.submit));
		},

		draw: function() {
			this.element = $("#appdashboardtmpl").tmpl(this.appconfig);
			console.log("this element", this.element);
			this.container.empty();
			this.container.append(this.element);

			this.drawAuthzHandlers();
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