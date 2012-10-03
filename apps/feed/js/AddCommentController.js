define([

], function() {

	var AddCommentController = function(user, item, el) {
		this.user = user;
		this.item = item;
		this.el = el;

		this.el.append($("#postCommentTmpl").tmpl({user: user}));
	
		this.el.on('click', '.actPostComment', $.proxy(this.actPostComment, this));

		this.el.find("textarea").focus();

		// this.el.on("click", ".actPost", $.proxy(this.actPost, this));
		// this.el.find("button.posttype").tooltip();
		// this.el.find("button.posttype").on('click', $.proxy(this.actTypeSelect, this));
		// this.el.find("button#btn-message").button('toggle').click();
		
	} 

	AddCommentController.prototype.actPostComment = function(e) {
		e.preventDefault();
		var msg = {
			"class": "comment"
		};
		// var groups = [];
		// this.el.find("div.groups input:checked").each(function(i, item) {
		// 	groups.push($(item).attr('value'));
		// });
		// msg['groups'] = this.item.groups;
		msg.groups = this.item.groups;
		msg.message = this.el.find('textarea').val();
		msg.inresponseto = this.item.id;

		console.log("Pushing obj", msg); // return;
		console.log("user", this.user);
		console.log("item", this.item);
		
		if (this.callback) {
			this.callback(msg);
			this.el.find("textarea").val("").focus();
		}

	}

	AddCommentController.prototype.onPost = function(callback) {
		this.callback = callback;
	}

	return AddCommentController;

});

