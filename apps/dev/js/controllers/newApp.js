define(function() {
	
	var newApp = function(container, callback, templates) {

		this.container = container;
		this.callback = callback;
		this.templates = templates;
		
		this.verifiedidentifier = null;
		this.verified = false;
		this.verifytimer = null;

//		this.element = $("#newAppTemplate").tmpl();
		console.log('newApp.html');
		this.element = $(templates['newApp'].render());

		console.log("this element", this.element);
		$("div#modalContainer").empty().append(this.element);

		this.checkIfReady();

		this.element.on('keyup change', '#newAppIdentifier', $.proxy(this.updateIdentifier, this));
		this.element.on('keyup change', '#newAppName', $.proxy(this.checkIfReady, this));
		this.element.on('click', '.createNewBtn', $.proxy(this.submit, this));


	};


	newApp.prototype.submit = function() {

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

	newApp.prototype.updateIdentifier = function() {
		var id = $(this.element).find("#newAppIdentifier").val();
		$(this.element).find(".newAppIdentifierMirror").html(id);

		if(this.verifytimer) clearTimeout(this.verifytimer);
		this.verifytimer = setTimeout($.proxy(this.verifyIdentifier, this), 500);

		if (id !== this.verifiedidentifier) {
			$(this.element).find("span.idlabels").empty();
			this.verified = false;
		}
	};

	newApp.prototype.checkIfReady = function() {
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

	newApp.prototype.verifyIdentifier = function() {
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

	newApp.prototype.activate = function() {
		$(this.element).modal('show');
		$(this.element).find("#newAppIdentifier").focus();
	}

	// newApp.include(Spine.Events);
	return newApp;

});