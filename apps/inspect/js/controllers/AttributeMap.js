define(function(require, exports, module) {

	var 

		$ = require('jquery'),
		models = require('uwap-core/js/models'),
		Class = require('uwap-core/js/class'),
		hb = require('uwap-core/js/handlebars')
		;

	var t = require('uwap-core/js/text!templates/attributemap.html');
	var template = hb.compile(t);

	// console.log(t);
	// console.log(template);

	var AttributeMap = Class.extend({
		"init": function(container, map, callback) {
			var that = this;
			this.container = container;
			this.map = map;
			this.callback = callback;

			console.log("inited attributemap");

			this.container.empty().append(template({}));

			container.on('click', '#smt', function(event) {
				event.preventDefault(); event.stopPropagation();
				that.submit();
			});
			
			this.loadGroups();
		},

		"loadGroups": function() {
			UWAP.groups.listMyGroups(function(g) {
				console.log("Got groups,", g);
				var c = $("#groupselector");
				$.each(g, function(i, el) {
					c.append('<option value="' + el.id + '">' + el.title + '</option>');
				});
			});
		},

		"submit": function() {

			var obj = {};
			obj.groupid = $("#groupselector").val();

			console.log("Selector", obj);

			this.callback(obj);
		}


	});





	return AttributeMap;

});
