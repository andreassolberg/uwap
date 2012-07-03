define(function() {
	
	var frontpage = Spine.Class.sub({
		init: function(container, callback) {


			this.callback = callback;
			this.container = container;
			this.element = $("#frontpage").tmpl();

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
		activate: function() {
			this.container.append(this.element);
		}
	});

	frontpage.include(Spine.Events);
	return frontpage;

});