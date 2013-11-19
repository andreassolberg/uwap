/**
 * @package UWAP
 * @description UWAP People Search. Dropdown search on organizations.
 * @author Andreas Åkre Solberg
 * @copyright Andreas Åkre Solberg, UNINETT AS.
 * @version 1.0
 */

define(['jquery'], function ($) {

	var uwap_people_template = '<form class="uwap-people-form" role="form">' + 
		'<div class="row"><div class="col-md-8"><div class="form-group">' + 
			'<input type="text" class="form-control" id="uwap-people-search" placeholder="Search for a person to add...">' + 
		'</div></div>' + 
		'<div class="col-md-4"><div class="form-group">' + 
			'<select id="uwap-people-orgs" class="form-control"></select>' + 
		'</div></div></div>' + 
	'</form>';


	var waiter = function (setCallback) {
		var my = {};

		// Number of milliseconds to wait for more events.
		my.delay = 400;
		my.counter = 0;

		// Call back to fire, when the waiter is pinged, and waited for the timeout 
		// (without subsequent events).
		my.callback = setCallback;

		// Ping
		function ping () {
			var xa = arguments;
			//console.log('Search box detected a change. Executing refresh...')
			my.counter++;
			setTimeout(function() {
				if (--my.counter === 0) {
					my.callback(xa[0]);
				}
			}, my.delay);
		}

		my.ping = ping;
		return my;
	}

	var PeopleSearch = function(container, settings) {
		var that = this;
		this.container = $(container);
		this.container.empty().append(uwap_people_template);
		this.inputEl = this.container.find('#uwap-people-search');

		this.settings = settings;


		var dropdownContainer = $('<div class="dropdown" style="margin-top: -20px; margin-bottom: 20px"></div>');
		var list = $('<ul id="peoplelist" class="dropdown-menu" role="menu" style="display: block; width: 400px" aria-labelledby="dLabel"></ul>')
			.appendTo(dropdownContainer);

		this.container.append(dropdownContainer);

		this.inputEl.val('').focus();

		this.lc = new ListController(list, settings, function() {
			$(that.inputEl).val('').focus();
		});

		this.waiter = waiter($.proxy(this.query, this));

		$(this.inputEl).on('keyup', function() {
			var q = $(that.inputEl).val();
			// console.log('search: ', q);
			if (q.length < 2) {
				that.lc.hide();
				return;
			}
			// console.log('Query');
			// that.query(q, this.lc);
			that.waiter.ping(q);
		});

		UWAP.people.listRealms(function(r) {
			// console.log("realms: ", r);

			// var c2 = $('<div class="realms"></div>');
			var l2 = $('<select style="margin-bottom: -3px; margin-left: 4px " class="pull-right realmlist"></select>');

			var realmsSelectEl = that.container.find('#uwap-people-orgs');


			$.each(r, function(i, item) {
				// console.log("REALM FOUND", i, item);
				if (item["default"]) {
					realmsSelectEl.append('<option selected="selected" value="' + item.realm + '">' + item.name + '</option>');	
				} else {
					realmsSelectEl.append('<option value="' + item.realm + '">' + item.name + '</option>');	
				}
				
			});

			realmsSelectEl.on('change', $.proxy(function() {
				console.log("CHANGE")
				that.q();
				that.inputEl.focus();
			}, that));

		});

	}

	PeopleSearch.prototype.getRealm = function() {
		var realmlist = $(this.container).find('select#uwap-people-orgs');
		// console.log("realmlist", realmlist);
		return realmlist.val();
	}
	PeopleSearch.prototype.q = function() {
		var that = this;
		var q = $(this.inputEl).val();
		// console.log('search: ', q);
		if (q.length < 2) {
			that.lc.hide();
			return;
		}
		// console.log('Query');
		that.query(q, this.lc);
	};
	PeopleSearch.prototype.query = function(q) {
		var that = this;
		console.log("Performing a search query on [" + q + "] where realm is [" + this.getRealm() + "]");

		UWAP.people.query(that.getRealm(), q, function(data) {
			console.log("Query result is ", data);
			that.lc.clean();

			if (!data.people) return;
			if (data.people.length < 1) return;

			$.each(data.people, function(i, item) {
				if(item.userid){
					that.lc.add(item);
				}
			});
		});

	}


	var ListController = function(el, settings, callback) {
		var that = this;
		this.el = el;
		this.settings = settings;

		$(this.el).on('click', '.uwap-person', function(e) {
			e.preventDefault();
			console.log("Click on event!");
			
			var person = $(e.currentTarget).data('src');
			that.settings.callback(person)
			callback(person);

			that.hide();
		});
		this.hide();
	}
	ListController.prototype.hide = function() {
		this.el.empty();
		this.el.hide();
	}
	ListController.prototype.clean = function() {
		this.el.empty();
		this.el.show();
	}
	ListController.prototype.add = function(item) {

		var e = $('<li class="uwap-person" style="width: 400px; clear: both"><a href=""></a></li>');
		var el = e.find('a');

		if (item.jpegphoto) {
			el.append('<img class="img-polaroid" style="margin: 5px; float: left; height: 64px; width: 64px; '
					+'border: 1px solid #ccc" src="data:image/jpeg;base64,' 
					+ encodeURIComponent(item.jpegphoto)
					+ '" />');
		} else {
			el.append('<img class="img-polaroid" style="margin: 5px; float: left; height: 64px; width: 64px; '
					+'border: 1px solid #ccc" src="/img/placeholder.png" />');
			// Got from here: http://www.veodin.com/wp-content/uploads/2012/01/placeholder.png
		}
		var iName = $('<h4 style="margin: 0px;">' + item.name + ' </h4>').appendTo(el);

		// $('<button type="button" class="btn btn-success btn-mini">Add</button>').appendTo(iName).click(function(){
		// 	gr.addMember(item);
		// 	e.remove();
		// });
		e.data('src', item);
		$(this.el).append(e);

		var e2 = '<p style="margin: 0px"><span style="margin-right: 15px;"><i class="glyphicon glyphicon-briefcase"></i> ' +
		item.o + '</span></p>';
		e2 += '<p style="margin: 0px"><span style="margin-right: 15px;"><i class="glyphicon glyphicon-user"></i> ' +
		item.userid + '</span></p>';
		e2 += '<p style="margin: 0px"><span style="margin-right: 15px;"><i class="glyphicon glyphicon-envelope"></i> ' +
		item.mail + '</span></p>';
		// e2 += '<span>' + JSON.stringify(item) + '</span>';
		el.append(e2);

	}




	$.fn.peopleSearch = function( options ) {  

		// Create some defaults, extending them with any options that were provided
		var settings = $.extend( {
			'callback'         : null,
			'background-color' : 'blue'
		}, options);

		return this.each(function() {
			var ps = new PeopleSearch(this, settings);
		});

	};


});
