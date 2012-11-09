define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),


		moment = require('uwap-core/js/moment'),
		prettydate = require('uwap-core/js/pretty')
		;

	
	require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap/js/bootstrap');
	require('uwap-core/bootstrap/js/bootstrap-collapse');
	require('uwap-core/bootstrap/js/bootstrap-button');
	require('uwap-core/bootstrap/js/bootstrap-dropdown');

	// require('uwap-core/bootstrap/js/bootstrap-modal');
    // require('uwap-core/bootstrap/js/bootstrap-tooltip');
	// require('uwap-core/bootstrap/js/bootstrap-transition');
	// require('uwap-core/bootstrap/js/bootstrap-alert');
	// require('uwap-core/bootstrap/js/bootstrap-scrollspy');
	// require('uwap-core/bootstrap/js/bootstrap-tab');
	// require('uwap-core/bootstrap/js/bootstrap-popover');
	// require('uwap-core/bootstrap/js/bootstrap-carousel');
	// require('uwap-core/bootstrap/js/bootstrap-typeahead');

	$("document").ready(function() {
		

		

		var App = function(el, user) {
			var that = this;
			this.el = el;

			this.setauth(user);

			this.el.on('click', '.actSubscribe', $.proxy(this.subscribe, this));
			this.el.on('click', '.actUnsubscribe', $.proxy(this.unsubscribe, this));

			this.load();

		}
		App.prototype.load = function() {
			var that = this;
			UWAP.groups.listPublic(function(items) {
				var table = that.el.find('.t');
				table.empty();
				$.each(items, function(i, item) {
					table.append($("#groupItem").tmpl(item));
					// table.append('<tr><td>' + item.title + '</td></tr>');
				});
			});
		}


		App.prototype.subscribe = function(e) {
			var that = this;
			if (e) e.preventDefault();

			var targetItem = $(e.currentTarget).closest('tr.group');
			var item = targetItem.tmplItem().data;

			console.log("Subscribe to ", item);

			UWAP.groups.subscribe(item.id, function() {
				that.load();
			});

		}

		App.prototype.unsubscribe = function(e) {
			var that = this;
			if (e) e.preventDefault();

			var targetItem = $(e.currentTarget).closest('tr.group');
			var item = targetItem.tmplItem().data;

			console.log("Subscribe to ", item);

			UWAP.groups.unsubscribe(item.id, function() {
				that.load();
			});

		}

		App.prototype.setauth = function(user) {
			this.user = user;
			this.groups = user.groups;

			$(".myname").empty().append(user.name);

			
		}







		setInterval(function(){ 
			$("span.ts").prettyDate(); 
		}, 8000);


		UWAP.auth.require(function(user) {

			var app = new App($("body"), user)
			

		});



	});

});

