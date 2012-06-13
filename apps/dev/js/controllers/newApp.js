define(function() {
	
	var newApp = Spine.Class.sub({
		init: function(container, callback) {
			this.verifiedidentifier = null;
			this.verified = false;
			this.verifytimer = null;

			this.callback = callback;
			this.element = $("#newAppTemplate").tmpl();
			console.log("this element", this.element);
			$("div#modalContainer").append(this.element);

			this.checkIfReady();

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
	return newApp;

});