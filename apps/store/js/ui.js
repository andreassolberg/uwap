
var storeUI = function(container) {

	var store = {};
	var obj = {};


	var source = $('<ul style="display: none" id="appSource"></li>').appendTo(container);
	var main = $('<ul id="appListing"></li>').appendTo(container);
	var destination = $('<ul style="display: none" id="destination"></li>').appendTo(container);

	var searchMatch = function(item, term) {

		if (item.name && item.name.toLowerCase().search(term.toLowerCase()) !== -1) return true;
		if (item.descr && item.descr.toLowerCase().search(term.toLowerCase()) !== -1) return true;
		// var key, i, keyword;

		// if (item.keywords) {
		// 	for(key in item.keywords) {
		// 		keyword = item.keywords[key];
		// 		for(i = 0; i < keyword.length; i++) {
		// 			if (keyword[i].toLowerCase().search(term.toLowerCase()) !== -1) return keyword[i];
		// 		}
		// 	}
		// }
		return false;
	};

	obj.clear = function() {
		store = {};
		source.empty();
		destination.empty();
	}

	obj.ready = function() {
		main.empty();
		source.find("li").clone().appendTo(main);
	}

	obj.addItem = function(item) {
		// var '[data-id='+id+']';

		store[item.id] = item;
		
		var appbox = $('<li class="appBox"></li>');
		if (item.logo) {
			appbox.append('<img src="' + item.logo + '" class="appLogo" />');	
		}
		
		appbox.append('<h3>' + item.name + '</h3>');
		appbox.append('<p class="descr">' + (item.descr || '') + '</p>');
		if (item.link_moreinfo) {
			appbox.append('<a href="' + item.link_moreinfo + '" class="run-app-button">More info</a> ');	
		}		
		if (item.link_run) {
			appbox.append('<a href="' + item.link_run + '" class="run-app-button">Run app</a> ');	
		}
		// appbox.append('<a href="http://' + item.id + '.app.bridge.uninett.no" class="run-app-button">View info</a> ');

		if (item.id) {
			appbox.attr("data-id", item.id);	
		}

		
		source.append(appbox);
	}
	obj.updateItem = function(item) {
		var src = source.find("[data-id=" + item.id + "]");
		var man = main.find("[data-id=" + item.id + "]");


		console.log("Update item"); console.log(item);
		console.log(src); console.log(man);

		if (item.descr) {
			src.find("p.descr").html(item.descr);
			man.find("p.descr").html(item.descr);
		}
		if (item.link_moreinfo) {
			src.append('<a href="' + item.link_moreinfo + '" class="run-app-button">More info</a> ');	
			man.append('<a href="' + item.link_moreinfo + '" class="run-app-button">More info</a> ');	
		}		
		if (item.link_run) {
			src.append('<a href="' + item.link_run + '" class="run-app-button">Run app</a> ');	
			man.append('<a href="' + item.link_run + '" class="run-app-button">Run app</a> ');	
		}

		
	}

	obj.get = function(item) {
		return source.find("[data-id=" + item.id + "]");
	}

	obj.filter = function(term) {
		var key;
		destination.empty();
		for(key in store) {
			if (searchMatch(store[key], term)) {
				destination.append(obj.get(store[key]).clone());
			}
		}
		main.quicksand(destination.find("li"), function() {
			console.log("Completed");
		});
	}




	return obj;
}