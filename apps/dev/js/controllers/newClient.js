define(function() {
	
	var newClient = function(container, callback, templates) {

		this.container = container;
		this.callback = callback;
		this.templates = templates;
		
		this.verifiedidentifier = null;
		this.verified = false;
		this.verifytimer = null;

//		this.element = $("#newClientTemplate").tmpl();
		console.log('newClient.html');
		this.element = $(templates['newClient']());

		console.log("this element", this.element);
		$("div#modalContainer").empty().append(this.element);

		this.checkIfReady();

		this.element.on('keyup change', '#newClientName', $.proxy(this.checkIfReady, this));
		this.element.on('click', '.createNewBtn', $.proxy(this.submit, this));

	};


	newClient.prototype.submit = function() {

		var obj = {};

		// obj.id = $(this.element).find("#newClientIdentifier").val();
		obj.client_name = $(this.element).find("#newClientName").val();
		obj.descr = $(this.element).find("#newClientDescr").val();
		obj.redirect_uri = [$(this.element).find("#newClientRedirectURI").val()];
		obj.type = 'client';

		// this.trigger("submit", obj);
		this.callback(obj);
		$(this.element).modal("hide");

		// $(this.element).remove();
	};



	newClient.prototype.checkIfReady = function() {
		console.log("check if ready");
		var name = $(this.element).find("#newClientName").val();
		if (name.length > 1) {
			console.log("READY")
			// $(this.element).find(".createNewBtn").attr("disabled", "disabled");
			$(this.element).find(".createNewBtn").removeClass("disabled");
		} else {
			console.log("NOT READY")
			// $(this.element).find(".createNewBtn").removeAttr("disabled");
			
			$(this.element).find(".createNewBtn").addClass("disabled");
		}
	}



	newClient.prototype.activate = function() {
		$(this.element).modal('show');
		$(this.element).find("#newClientName").focus();
	}

	// newClient.include(Spine.Events);
	return newClient;

});