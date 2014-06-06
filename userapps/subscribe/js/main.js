define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),


		moment = require('uwap-core/js/moment'),
		prettydate = require('uwap-core/js/pretty')
		;

	
	require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap3/js/bootstrap');	
	require('uwap-core/bootstrap3/js/collapse');
	require('uwap-core/bootstrap3/js/button');
	require('uwap-core/bootstrap3/js/dropdown');	


	$("document").ready(function() {
		

		

		var App = function(el, user, groups, subscriptions) {
			var that = this;
			this.el = el;

			this.setauth(user, groups, subscriptions);

			this.el.on('click', '.actSubscribe', $.proxy(this.subscribe, this));
			this.el.on('click', '.actUnsubscribe', $.proxy(this.unsubscribe, this));


		}
		App.prototype.load = function() {
			var that = this;
			UWAP.groups.listPublic(function(items) {

				console.log("Loaded public groups", items);

				var table = that.el.find('.t');
				table.empty();

				console.log(that.user);

				$.each(items, function(groupid, item) {

					if (that.groups[item.id]) {
						// return;
					}

					if (that.subscriptions[item.id]) {
						item.subscribed = true;
					} else {
						item.subscribed = false;
					}
					table.append($("#groupItem").tmpl(item));
					// table.append('<tr><td>' + item.title + '</td></tr>');
				});

			});
		}

		App.prototype.reloadSubscriptions = function(callback) {

			var that = this;
			UWAP.groups.listSubscriptions(function(subscriptions) {

				that.subscriptions = subscriptions;
				that.load();

			});

		}


		App.prototype.subscribe = function(e) {
			var that = this;
			if (e) e.preventDefault();

			var targetItem = $(e.currentTarget).closest('tr.group');
			var item = targetItem.tmplItem().data;

			console.log("Subscribe to ", item);

			UWAP.groups.subscribe(item.id, function() {

				that.reloadSubscriptions();
				
			});

		}

		App.prototype.unsubscribe = function(e) {
			var that = this;
			if (e) e.preventDefault();

			var targetItem = $(e.currentTarget).closest('tr.group');
			var item = targetItem.tmplItem().data;

			console.log("Subscribe to ", item);

			UWAP.groups.unsubscribe(item.id, function() {

				that.reloadSubscriptions();

				// UWAP.auth.require($.proxy(that.setauth, that));

			});

		}

		App.prototype.setauth = function(user, groups, subscriptions) {
			this.user = user;
			this.groups = groups;
			this.subscriptions = subscriptions;

			$(".myname").empty().append(user.name);

			this.load();

		}







		setInterval(function(){ 
			$("span.ts").prettyDate(); 
		}, 8000);


		UWAP.auth.require(function(user) {

			$(".loader-hideOnLoad").hide();
			$(".loader-showOnLoad").show();
			$("span#username").html(user.name);
			$('.dropdown-toggle').dropdown();

			UWAP.groups.listMyGroups(function(groups) {

				UWAP.groups.listSubscriptions(function(subscriptions) {

					var app = new App($("body"), user, groups, subscriptions);


				});


			});


		});



	});

});

