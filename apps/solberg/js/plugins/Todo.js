define(['../libs/moment'], function(moment) {

	var Todo = function(container) {
		this.container = container;
		this.load();
		setInterval($.proxy(this.load, this), 3*60*1000); // 3 minutes
	};

	Todo.prototype.load = function() {
		var url = "http://app.solweb.no/solberg/todo.php";
		UWAP.data.get(url, {handler: "solberg"}, $.proxy(this.response, this));
	}
	Todo.prototype.response = function(c) {
		var 
			i, el, pri;

		console.log("Todo response");
		console.log(c);
		$(this.container).empty();
		return;

		for(i = 0; i < c.length; i++) {
			console.log("entry", c[i]);
			// pri = c[i].task.priority || 'na';
			if (!c[i].task) continue;
			var priority = ' <span class="priority priority' + c[i].task.priority + '">' + c[i].task.priority + '</span> ';
			el = $('<div class="todoentry">' + priority + c[i].name + '</div>');
			el.prepend('<img src="/img/todo.png" style=" margin-top: -2px" />');
			$(this.container).append(el);
		}
	}


	return Todo;

});