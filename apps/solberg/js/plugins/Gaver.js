define(function(require, exports, module) {
	
	var
		moment = require('uwap-core/js/moment');
		
	var Gaver = function(container) {
		this.container = container;
		this.load();
		setInterval($.proxy(this.load, this), 3*60*1000); // 3 minutes
	};

	Gaver.prototype.load = function() {
		var url = "http://app.solweb.no/sdash/gaver.php";
		UWAP.data.get(url, {handler: "solberg"}, $.proxy(this.response, this));
	}
	Gaver.prototype.response = function(r) {
		$(this.container).empty();

		for(var i = 0; i < r.length; i++) {
			var item = r[i];
			if (i > 5) break;

			var el = $('<div class="gaveentry"></div>');
			el.addClass(item.completed);
			el.append('<div class="name">' + item.name + '</div>');
			if (item.prettydue) {
				el.append('<div class="until">' + item.prettydue + '</div>');	
			}
			
			$(this.container).append(el);
		}
	}


	return Gaver;

});