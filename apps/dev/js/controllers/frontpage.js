define(function() {

	var frontpage = function(container, callback, templates) {
		this.callback = callback;
		this.container = container;
//		this.element = $("#frontpage").tmpl();
		this.element = $(templates['frontpage'].render());

		container.append(this.element);

		// this.checkIfReady();
		// this.element.on('click', '.createNewBtn', $.proxy(this.submit, this));

	}

	frontpage.prototype.activate = function() {
		console.log("Activating frontapge", this, this.container);
		this.container.empty();
		this.container.append(this.element);
	}


	// frontpage.include(Spine.Events);
	return frontpage;

});