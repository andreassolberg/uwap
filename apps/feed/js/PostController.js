define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		moment = require('uwap-core/js/moment'),
		hogan = require('uwap-core/js/hogan')
		;

	require('uwap-core/bootstrap/plugins/datepicker/bootstrap-datepicker');
	
	var tmpl = {
		"post": require('uwap-core/js/text!templates/post.html')
	};
	
	var PostController = function(pane) {
		this.pane = pane;
		this.el = pane.el;
		this.type = null;
		this.groups = null;

		this.selectedGroups = {};

		this.templates = {
			"post": hogan.compile(tmpl.post)
		};


		$(this.templates.post.render({today: moment().add('days', 1).format('DD-MM-YYYY')  })).appendTo(this.el);

		
		this.el.on("click", ".actPost", $.proxy(this.actPost, this));

		this.el.find("button.posttype").on('click', $.proxy(this.actTypeSelect, this));

		this.el.on('click', '#sharewithgrouplist a.actShareWith', $.proxy(this.shareWith, this));
		this.el.on('click', 'a.resetsharedwith', $.proxy(this.resetShareWith, this));
		

		this.el.find("button#btn-message").button('toggle').click();

		this.el.find('.datepicker').datepicker({weekStart: 1});
		this.el.on('load',$.proxy(this.responsiveEventUI, this));

		this.el.on('click', '.responsiveEventUI', $.proxy(this.responsiveEventUI, this));
		this.responsiveEventUI();

	} 

	PostController.prototype.resetShareWith = function(e) {
		e.preventDefault();

		var sharespan = this.el.find('.shareitems');
		sharespan.empty();

		this.selectedGroups = {};

		this.el.find('#sharewithgrouplist li').removeClass('disabled');

	}

	PostController.prototype.responsiveEventUI = function() {

		var postcontainer = this.el.find("div.postc.post-event");


		if (postcontainer.find(".field-deadline").prop('checked')) {
			postcontainer.find(".section-deadline").show();
		} else {
			postcontainer.find(".section-deadline").hide();
			// console.log("Hide section deadlonie", postcontainer.find(".section-deadline"))
		}

		if (postcontainer.find(".field-maxparticipants").prop('checked')) {
			postcontainer.find(".section-max").show();
		} else {
			postcontainer.find(".section-max").hide();
			// console.log("Hide section max", postcontainer.find(".section-max"))
		}

		if (postcontainer.find(".field-allowsignup").prop('checked')) {
			postcontainer.find(".section-signup").show();
		} else {
			postcontainer.find(".section-signup").hide();
			// console.log("Hide section signup")
		}

	}

	PostController.prototype.shareWith = function(e) {
		e.preventDefault();

		var currentListItem = $(e.currentTarget).closest('li');
		var groupid = currentListItem.data('groupid');
		var sharespan = this.el.find('.shareitems');

		if (this.selectedGroups[groupid] && this.selectedGroups[groupid] === true) return;

		this.selectedGroups[groupid] = true;

		sharespan.append('<span class="label sharedwithgroup">' + this.groups[groupid] + '</span>');
		currentListItem.addClass('disabled');

		// console.log('Adding group', groupid)
	}

	PostController.prototype.getGroups = function(e) {
		var groups = [];
		for(var k in this.selectedGroups) {
			if (this.selectedGroups.hasOwnProperty(k) && this.selectedGroups[k]) {
				groups.push(k);
			}
		}
		return groups;
	}	

	PostController.prototype.actTypeSelect = function(e) {
		e.preventDefault();
		var id = $(e.currentTarget).attr('id');

		
		// console.log("Selected something", id);

		var mapping = {
			'btn-link': 'link',
			'btn-article': 'article',
			'btn-file': 'file',
			'btn-message': 'message',
			'btn-event': 'event'
		}
		var target = mapping[id];

		if (!target) {
			return;
		}
		this.type = target;

		this.el.find("div.postc").hide();
		// console.log("opening " + target)
		this.el.find("div.postc.post-" + target).show();

		this.el.find("div.postc.post-" + target + " .focusfield").focus();
	}


	PostController.prototype.parseDate = function(str) {
		console.log("parseDate(" + str + ")");
		return moment(str, "DD-MM-YYYY HH:mm").unix();
	}

	PostController.prototype.actPost = function(e) {
		e.preventDefault();
		var msg = {
			"class": [this.type]
		}

		msg['groups'] = this.getGroups();


		msg.promoted = this.el.find('input.field-promoted').prop("checked");
		msg.public = this.el.find('input.field-public').prop("checked");
		// if (public) {
		// 	msg['groups'].push('!public');
		// }
		// console.error("PRomoted", this.el.find('input.field-promoted'));

		var postcontainer = this.el.find("div.postc.post-" + this.type);

		switch(this.type) {

			case 'message':
				msg.message = postcontainer.find(".field-message").val();
				break;

			case 'article':
				msg.title = postcontainer.find(".field-title").val();
				msg.message = postcontainer.find(".field-message").val();
				break;

			case 'file':
				msg.message = postcontainer.find(".field-message").val();
				msg.files = [{
					"href": postcontainer.find(".field-fileurl").val(),
					"filename": postcontainer.find(".field-filename").val()
				}];
				break;

			case 'event':
				msg.title = postcontainer.find(".field-title").val();
				msg.message = postcontainer.find(".field-message").val();
				msg.dtstart = this.parseDate(postcontainer.find(".field-eventdate").val() + ' ' + postcontainer.find(".field-eventtime").val());
				msg.location = {
					address: postcontainer.find(".field-location-address").val(),
					local: postcontainer.find(".field-location-local").val()
				};

				if (postcontainer.find(".field-allowsignup").prop("checked")) {
					var signup = {"allow": true};

					if (postcontainer.find(".field-deadline").prop("checked")) {
						signup.deadline = this.parseDate(postcontainer.find(".field-deadlinedate").val() + ' ' + postcontainer.find(".field-deadlinetime").val());
					}
					if (postcontainer.find(".field-maxparticipants").prop("checked")) {
						signup.max = postcontainer.find(".field-maxparticipants-value").val();
					}

					msg.signup = signup;
				}
				break;

			case 'link':
				msg.message = postcontainer.find(".field-message").val();
				msg.links = [
					{
						href: postcontainer.find(".field-link").val(),
						text: postcontainer.find(".field-title").val()
					}
				];
				break;
		}

		var str = this.el.find("textarea").val();

		if (!msg.groups || msg.groups.length < 1) {
			alert('You must share with one or more groups!');
			return;
		} 

		console.log("Pushing obj", msg); // return;
		// this.post(msg);
		// return;
		if (this.callback) {
			this.callback(msg);
			console.log("POSTING A NEW MESSAGE ", msg); 
			this.el.find("textarea").val("").focus();
			postcontainer.find("textarea").val("");
			postcontainer.find("input").val("");
			this.el.find("div.postc.post-" + this.type + " .focusfield").focus();
		}
		
	}
	PostController.prototype.setgroups = function(groups) {
		var that = this;
		var grouplist = this.el.find('#sharewithgrouplist');

		this.groups = groups;
		this.selectedGroups = {};

		grouplist.empty();

		$.each(groups, function(i, item) {
			// that.el.find("div.groups").append('<label class="checkbox inline"><input type="checkbox" id="grp_' + i + '" value="' + i + '">' + item + '</label>');
			$('<li><a class="actShareWith" href="#">' + item + '</a></li>').data('groupid', i).appendTo(grouplist);
		});
	}

	PostController.prototype.onPost = function(callback) {
		this.callback = callback;
	}

	return PostController;

});

