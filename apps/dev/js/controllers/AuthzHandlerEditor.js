define(function() {
	
	var AuthzHandlerEditor = Spine.Class.sub({
		init: function(container, handler, callback) {
			this.handler = handler;
			this.callback = callback;
			this.element = $("#authhandlereditortmpl").tmpl(handler);

			console.log("this element", this.element);
			container.append(this.element);

			// this.checkIfReady();

			$(this.element).find("#newAppIdentifier")
				.on("change", this.proxy(this.updateIdentifier))
				.on("keyup", this.proxy(this.updateIdentifier));
			$(this.element).find("#newAppName")
				.on("change", this.proxy(this.checkIfReady))
				.on("keyup", this.proxy(this.checkIfReady));

			$(this.element).find(".createNewBtn")
				.on("click", this.proxy(this.submit));
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

		activate: function() {
			$(this.element).modal('show');
			$(this.element).find("#newAppIdentifier").focus();
		}
	});
	// newApp.include(Spine.Events);
	return AuthzHandlerEditor;

});