define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		hb = require('uwap-core/js/handlebars')
		;

	var authzclientsTmplText = require('uwap-core/js/text!templates/components/authorizedClients.html');
	var authzclientsTmpl = hb.compile(authzclientsTmplText);

	var AuthorizationListController = function(element, callback) {
		this.selected = null;
		this.clientid = null;

		// this.authorizationlist = authorizationlist;
		this.callback = callback;
		this.element = element;

		console.log("Initing authorization list controller");

		$(this.element).on("click", "tr.client", $.proxy(this.select, this));
	}


	AuthorizationListController.prototype.setList = function(authorizationlist) {
		this.authorizationlist = authorizationlist;
	}



	AuthorizationListController.prototype.select = function(e) {
		e.stopPropagation(); e.preventDefault();

		var target = $(e.currentTarget);
		var clientid = target.data('clientid');

		console.log("Selected an entry");
		console.log('clientid ', clientid, ' was ', this.clientid);

		if (this.clientid !== clientid) {

			if (this.clientid !== null) {
				console.log("= = = = =  CLEARING UP", this.selected);
				this.selected.removeClass('open').next().removeClass('open');
			}

			this.clientid = clientid;
			this.selected = target;

			target.addClass('open').next().addClass('open');
		} else {

			this.selected.removeClass('open').next().removeClass('open');
			this.selected = null; this.clientid = null;

		}

		


	}


	/**
	 * 
	 */
	AuthorizationListController.prototype.draw = function(func) {

		console.log("------- draw authorizationList ", this.authorizationlist);
		var container = $(this.element);
		container.empty();

		console.log("  -----> View");
		console.log(this.authorizationlist.getView());

		container.append(authzclientsTmpl(this.authorizationlist.getView()));

	}

	/**
	 * Utility function that finds the element of a specific Application ID
	 * @param  {[type]} appid [description]
	 * @return {[type]}       [description]
	 */
	// AuthorizationListController.prototype.findElement = function(appid) {
	// 	var found = null;
	// 	$.each($(this.element).find("a"), function(i, item) {
	// 		if ($(item).data('itemid') === appid) found = $(item);
	// 	});
	// 	console.log("  ----> findElement ", appid, found);
	// 	return found;
	// }




	return AuthorizationListController;
})

