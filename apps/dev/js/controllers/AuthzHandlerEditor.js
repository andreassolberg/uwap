define(function() {
	
	var AuthzHandlerEditor = Spine.Class.sub({
		init: function(container, handler, callback) {
			this.handler = handler;
			this.callback = callback;
			this.element = $("#authhandlereditortmpl").tmpl(handler);

			console.log("this element", this.element);
			container.append(this.element);

			this.changeHandlerType();

			$(this.element).on("change", ".handlerType", this.proxy(this.changeHandlerType));
			$(this.element).on("click" , ".saveAuthZHandler", this.proxy(this.save));

			// $(this.element).find("#newAppIdentifier")
			// 	.on("change", this.proxy(this.updateIdentifier))
			// 	.on("keyup", this.proxy(this.updateIdentifier));
			// $(this.element).find("#newAppName")
			// 	.on("change", this.proxy(this.checkIfReady))
			// 	.on("keyup", this.proxy(this.checkIfReady));

			// $(this.element).find(".createNewBtn")
			// 	.on("click", this.proxy(this.submit));
		},
		changeHandlerType: function(event) {
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
			$(this.element).find("div.control-group.authzproperty").hide();
			$(this.element).find("div.control-group.authzproperty." + type).show();

		},

		save: function() {
			var 
				obj = {},
				fields,
				that = this;

			obj.id = $(this.element).find("#handlerIdentifier").val();
			obj.title = $(this.element).find("#handlerTitle").val();
			obj.type = $(this.element).find("select.handlerType").val();

			fields = ['authorization', 'token', 'request', 'authorize', 'access', 'client_id', 'client_user', 'client_secret', 'token_hdr', 'token_val'];
			$.each(fields, function(i, field) {
				var val = $(that.element).find("#field_" + field).val();
				if (val) {
					obj[field] = val;
				}
			});

			// this.trigger("submit", obj);
			this.callback(obj);
			$(this.element).modal("hide");
			$(this.element).remove();
		},

		activate: function() {
			$(this.element).modal('show');
			$(this.element).find("#newAppIdentifier").focus();
		}
	});
	// newApp.include(Spine.Events);
	return AuthzHandlerEditor;

});