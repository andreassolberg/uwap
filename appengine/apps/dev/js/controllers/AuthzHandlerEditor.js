define(function() {
	
	var AuthzHandlerEditor = function(container, appconfig, handler, isnew, callback, templates) {
		this.handler = handler;
		this.callback = callback;
		this.isnew = isnew;
		this.appconfig = appconfig;
		this.templates = templates;

		this.handler.redirect_uri2 = UWAP.utils.getEngineURL('/_/oauth2callback');
		this.handler.redirect_uri1 = UWAP.utils.getEngineURL('/_/oauth1callback');

		console.log('making authhandlereditor');
		this.element = $(this.templates['authhandlereditor'](this.handler));
		
		container.append(this.element);

		if (this.handler.type) {
			$(this.element).find("select.handlerType").val(this.handler.type);
		}
		if (this.handler.tokentransport) {
			$(this.element).find("select#field_tokentransport").val(this.handler.tokentransport);
		}
		$(this.element).find("#handlerTitle").focus();
		this.changeHandlerType();
		
		if(!isnew) {
			$(this.element).find("#handlerIdentifier").attr("disabled", "disabled");
		}

		$(this.element).on("change", ".handlerType", this.proxy(this.changeHandlerType));
		$(this.element).on("click" , ".saveAuthZHandler", this.proxy(this.save));
	}

	AuthzHandlerEditor.prototype.proxy = function(func) {
		return $.proxy(func, this);
	}

	AuthzHandlerEditor.prototype.changeHandlerType = function(event) {
		var 
			type = $(this.element).find("select.handlerType").val(),
			prev = $(this.element).data("handlerType");


		// Set the correct class of the form controller, depending on type.
		if (type !== prev) {
			console.log("Type has changed from previous", prev, type);
			$(this.element).removeClass("handlerType_" + prev);
			$(this.element).addClass("handlerType_" + type);
		}
		$(this.element).data("handlerType", type);


		// Enable disable the relevant fields
		$(this.element).find("div.form-group.authzproperty").hide();
		$(this.element).find("div.form-group.authzproperty." + type).show();
	}

	AuthzHandlerEditor.prototype.save = function() {
		var 
			obj = {},
			fields,
			that = this;

		obj.id = $(this.element).find("#handlerIdentifier").val();
		obj.title = $(this.element).find("#handlerTitle").val();
		obj.type = $(this.element).find("select.handlerType").val();

		fields = ['authorization', 'token', 'request', 'tokentransport', 'authorize', 'access', 'client_id', 'client_user', 'client_secret', 'token_hdr', 'token_val', 'defaultexpire', 'defaultscopes'];
		$.each(fields, function(i, field) {
			var val = $(that.element).find("#field_" + field).val();
			if (val) {
				obj[field] = val;
			}
		});

		console.log("About to callback(", obj, ")");
		this.callback(obj);
		$(this.element).modal("hide");
	}

	AuthzHandlerEditor.prototype.activate = function() {
		$(this.element).modal('show');
		$(this.element).find("#newAppIdentifier").focus();
	}



	return AuthzHandlerEditor;

});