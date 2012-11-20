define(function() {
	
	var newProxy = function(container, callback) {

		this.container = container;
		this.callback = callback;
		
		this.verifiedidentifier = null;
		this.verified = false;
		this.verifytimer = null;

		this.element = $("#newProxyTemplate").tmpl();

		console.log("this element", this.element);
		$("div#modalContainer").append(this.element);

		this.checkIfReady();

		this.element.on('keyup change', '#newProxyIdentifier', $.proxy(this.updateIdentifier, this));
		this.element.on('keyup change', '#newProxyName', $.proxy(this.checkIfReady, this));
		this.element.on('click', '.createNewBtn', $.proxy(this.submit, this));


	};


	newProxy.prototype.submit = function() {

		var obj = {};

		obj.id = $(this.element).find("#newProxyIdentifier").val();
		obj.name = $(this.element).find("#newProxyName").val();
		obj.descr = $(this.element).find("#newProxyDescr").val();
		obj.type = 'app';

		// this.trigger("submit", obj);
		this.callback(obj);
		$(this.element).modal("hide");
		$(this.element).remove();
	};

	newProxy.prototype.updateIdentifier = function() {
		var id = $(this.element).find("#newProxyIdentifier").val();
		$(this.element).find(".newProxyIdentifierMirror").html(id);

		if(this.verifytimer) clearTimeout(this.verifytimer);
		this.verifytimer = setTimeout($.proxy(this.verifyIdentifier, this), 500);

		if (id !== this.verifiedidentifier) {
			$(this.element).find("span.idlabels").empty();
			this.verified = false;
		}
	};

	newProxy.prototype.checkIfReady = function() {
		console.log("check if ready");
		var name = $(this.element).find("#newProxyName").val();
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

	newProxy.prototype.verifyIdentifier = function() {
		var that = this;
		var id = $(this.element).find("#newProxyIdentifier").val();

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

	newProxy.prototype.activate = function() {
		$(this.element).modal('show');
		$(this.element).find("#newProxyIdentifier").focus();
	}

	// newProxy.include(Spine.Events);
	return newProxy;

});