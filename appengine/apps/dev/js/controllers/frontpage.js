define(function() {

	var frontpage = function(container, callback, templates) {
		this.callback = callback;
		this.container = container;
		this.element = $(templates.frontpage());

		container.append(this.element);

	};

	frontpage.prototype.activate = function() {
		console.log("Activating frontapge", this, this.container);
		this.container.empty();
		this.container.append(this.element);
	};

	return frontpage;

});