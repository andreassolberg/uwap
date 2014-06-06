
var Chat = function (chatcontainer, groups) {
	var that = this;

	this.chatcontainer = chatcontainer;
	this.groups = groups;

	setInterval($.proxy(this.updateChat(), this), 2000);
	$.proxy(this.updateChat(), this);


	this.chatcontainer.find("div#chatgroups").empty().
		append('<input type="checkbox" id="!public" name="!public"><label for="!public">Public</label> ');
	var i = 0, el, checked;
	if(this.groups) {
		for(var key in this.groups) {
			checked = '';
			if (i++ === 0) {
				checked = ' checked="checked" ';
			}

			el = $('<input type="checkbox" ' + checked + 'name="' + key + '" id="' + key + '"><label for="' + key + '">' + 
				this.groups[key] + '</label> ');
			$("div#chatgroups").append(el);
		}
	}

	this.chatcontainer.find("input#chatmsg").focus().bind("change", function(data) {

		var msg = chatcontainer.find("input#chatmsg").attr("value") ;
		// console.log("Chat msg: " + msg);

		if (msg === '') return false;

		var groups = [];
		that.chatcontainer.find("div#chatgroups input").each(function(i, item) {
			var grid = $(item).attr("name");
			if ($(item).prop("checked")) {
				groups.push(grid);
			}
			console.log("group found ", grid);
		});

		var msgobj = {
			"type": "msg",
			"msg": msg
		};
		if (groups) {
			msgobj["uwap-acl-read"] = groups;
		}

		UWAP.store.save(msgobj, function(d) {
			console.log("completed store function...");
			$.proxy(that.updateChat(), that);
		});

		chatcontainer.find("input#chatmsg").attr("value", "");
	});
};


Chat.prototype.getGroup = function(id) {
	if (id === '!public') return 'Public';
	if (this.groups[id]) return this.groups[id];
	return 'unknown group (' + id + ')';
}

Chat.prototype.updateChatResponse = function (data) {
	var that = this;
	console.log("chat result", data);
	$("div#chatoutput").empty();
	$.each(data, function(i, msg) {
		console.log("Chat entry", i, msg);
		var ce = $('<p><span style="color: #999">' + msg['uwap-userid'] + '</span> ' + msg.msg + '</p>');
		if (msg.hasOwnProperty("uwap-acl-read")) {
			$.each(msg["uwap-acl-read"], function(j, item) {
				console.log("ACL READ item", item);
				ce.append('<span style="margin: 0px 10px; background: #ccc; padding: 2px; border-radius: 3px">' + that.getGroup(item) + '</span>');
			});
		}
		console.log(ce);
		$("div#chatoutput").prepend(ce);
	});
}

Chat.prototype.updateChat = function () {

	UWAP.store.queryList({type: "msg"}, $.proxy(this.updateChatResponse, this));
}


// var groups = {};
// function getGroup(id) {
// 	if (id === '!public') return 'Public';
// 	if (groups[id]) return groups[id];
// 	return 'unknown group (' + id + ')';
// }

function loggedin(user) {
	console.log("Logged in.");
	$("div#out").prepend('<p>Logged in as <strong>' + user.name + '</strong> (<tt>' + user.userid + '</tt>)</p>');
	$("input#smt").attr('disabled', 'disabled');

	var gr = $('<dl></dl>')
	if(user.groups) {
		groups = user.groups;
		for(var key in user.groups) {
			gr.append('<dt>' + user.groups[key] + '</dt>');
			gr.append('<dd><tt>' + key + '</tt></dd>');

		}
	}
	// $("div#out").append('<p>Groups:</p>').append(gr);



	var chat = new Chat($("div#chatcontainer"), groups);

}



$("document").ready(function() {
	
	UWAP.auth.require(loggedin);

});