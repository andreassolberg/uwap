define([

], function() {

	var PostController = function(el) {
		this.el = el;
		this.type = null;

		
		this.el.on("click", ".actPost", $.proxy(this.actPost, this));
		this.el.find("button.posttype").tooltip();

		this.el.find("button.posttype").on('click', $.proxy(this.actTypeSelect, this));

		this.el.find("button#btn-message").button('toggle').click();

		
	} 

	PostController.prototype.actTypeSelect = function(e) {
		e.preventDefault();
		var id = $(e.currentTarget).attr('id');

		
		console.log("Selected something", id);

		var mapping = {
			'btn-link': 'link',
			'btn-article': 'article',
			'btn-file': 'file',
			'btn-message': 'message',
		}
		var target = mapping[id];

		if (!target) {
			return;
		}
		this.type = target;

		this.el.find("div.postc").hide();
		console.log("opening " + target)
		this.el.find("div.postc.post-" + target).show();

		this.el.find("div.postc.post-" + target + " .focusfield").focus();

	}

	PostController.prototype.actPost = function(e) {
		e.preventDefault();
		var msg = {
			message: str,
			"class": [this.type]
		}
		var groups = [];
		this.el.find("div.groups input:checked").each(function(i, item) {
			groups.push($(item).attr('value'));
		});
		msg['groups'] = groups;


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

		console.log("Pushing obj", msg); // return;
		// this.post(msg);

		if (this.callback) {
			this.callback(msg);
			this.el.find("textarea").val("").focus();
			postcontainer.find("textarea").val("");
			postcontainer.find("input").val("");
			this.el.find("div.postc.post-" + this.type + " .focusfield").focus();
		}
		
	}
	PostController.prototype.setgroups = function(groups) {
		var that = this;
		console.log("groups", groups);
		this.groups = groups;
		this.el.find("div.groups").empty();
		$.each(groups, function(i, item) {
			that.el.find("div.groups").append('<label class="checkbox inline"><input type="checkbox" id="grp_' + i + '" value="' + i + '">' + item + '</label>');
			$("ul#navfilter").append('<li><a id="entr_' + i + '" href="#"><span class="icon icon-folder-open"></span> ' + item + '</a></li>');
		});
	}

	PostController.prototype.onPost = function(callback) {
		this.callback = callback;
	}

	return PostController;

});

