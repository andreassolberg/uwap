define(['jquery'], function ($) {

	var PeopleSearch = function(inputEl, settings) {
		var that = this;

		this.inputEl = inputEl;
		this.settings = settings;

		$(this.inputEl).wrap('<form class="navbar-search pull-left"/>');
		this.formWrapEl = $(this.inputEl).parent();

		this.formWrapEl
			.wrap('<div class="uwap-people-main" />')
			.wrap('<div class="navbar"></div>')
			.wrap('<div class="navbar-inner"></div>');

		this.mainEl = $(this.formWrapEl).closest('div.uwap-people-main');


		var container = $('<div class="dropdown" style="margin-top: -20px"></div>');
		var list = $('<ul id="peoplelist" class="dropdown-menu" role="menu" style="display: block" aria-labelledby="dLabel"></ul>')
			.appendTo(container);
		$(this.mainEl).append(container);

		$(that.inputEl).val('').focus();

		this.lc = new ListController(list, settings, function() {
			$(that.inputEl).val('').focus();
		});

		$(this.inputEl).on('keyup', function() {
			var q = $(that.inputEl).val();
			// console.log('search: ', q);
			if (q.length < 2) {
				that.lc.hide();
				return;
			}
			// console.log('Query');
			that.query(q, this.lc);
		});

		UWAP.people.listRealms(function(r) {
			console.log("realms: ", r);

			// var c2 = $('<div class="realms"></div>');
			var l2 = $('<select style="margin-bottom: -3px; margin-left: 4px " class="pull-right realmlist"></select>');

			$.each(r, function(i, item) {
				console.log("REALM FOUND", i, item);
				if (item.default) {
					l2.append('<option selected="selected" value="' + item.realm + '">' + item.name + '</option>');	
				} else {
					l2.append('<option value="' + item.realm + '">' + item.name + '</option>');	
				}
				
			});

			$(that.formWrapEl).append(l2);

			l2.on('change', $.proxy(function() {
				console.log("CHANGE")
				this.q();
				this.inputEl.focus();
			}, that));

		});

	}

	PeopleSearch.prototype.getRealm = function() {
		console.log("realmlist", $(this.el).parent().find('select.realmlist'));
		return $(this.inputEl).parent().find('select.realmlist').val();
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
		console.log("getRealm is ", this.getRealm());

		UWAP.people.query(that.getRealm(), q, function(data) {
			that.lc.clean();
			$.each(data, function(i, item) {
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

		var e = $('<li class="uwap-person" style="clear: both"><a href=""></a></li>');
		var el = e.find('a');

		if (item.jpegphoto) {
			el.append('<img class="img-polaroid" style="margin: 5px; float: left; max-width: 64px; max-height:'
					+'64px; border: 1px solid #ccc" src="data:image/jpeg;base64,' 
					+ item.jpegphoto
					+ '" />');
		} else {
			el.append('<img class="img-polaroid" style="margin: 5px; float: left; max-width: 64px; max-height:'
					+'64px; border: 1px solid #ccc" src="/img/placeholder.png" />');
			// Got from here: http://www.veodin.com/wp-content/uploads/2012/01/placeholder.png
		}
		var iName = $('<h4 style="margin: 0px;">' + item.name + ' </h4>').appendTo(el);

		// $('<button type="button" class="btn btn-success btn-mini">Add</button>').appendTo(iName).click(function(){
		// 	gr.addMember(item);
		// 	e.remove();
		// });
		e.data('src', item);
		$(this.el).append(e);

		var e2 = '<p style="margin: 0px"><span style="margin-right: 15px;"><i class="icon-briefcase"></i> ' +
		item.o + '</span></p>';
		e2 += '<p style="margin: 0px"><span style="margin-right: 15px;"><i class="icon-user"></i> ' +
		item.userid + '</span></p>';
		e2 += '<p style="margin: 0px"><span style="margin-right: 15px;"><i class="icon-envelope"></i> ' +
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
