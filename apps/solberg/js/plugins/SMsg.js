define(function(require, exports, module) {
	
	var
		moment = require('uwap-core/js/moment');
		
	var SMsg = function(container) {
		this.container = container;
		this.load();
		setInterval($.proxy(this.load, this), 3*60*1000); // 3 minutes
	};

	SMsg.prototype.load = function() {
		var url = "http://app.solweb.no/solberg/msg.php";
		UWAP.data.get(url, {handler: "solberg"}, $.proxy(this.response, this));
	}
	SMsg.prototype.response = function(c) {
		var 
			i, el;

		console.log("msg response");
		console.log(c);
		$(this.container).empty();

		for(i = 0; i < c.length; i++) {
			console.log("entry", c[i]);
			el = $('<div class="msgentry">' + c[i].name + '</div>');
			el.prepend('<img src="/img/note.png" style="" />');
			$(this.container).append(el);
		}
	}


	return SMsg;

});