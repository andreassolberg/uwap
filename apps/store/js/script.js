



function applisting(ui, o) {
	var k;
	for (k in o) {
		// appinfo(k, o[k]);
		var item = o[k];
		item.id = "uwap-" + k;
		item.logo = '/_/api/logo.php?app=' + k;
		item.link_run = 'http://' + k + '.app.bridge.uninett.no';
		item.link_moreinfo = 'http://' + k + '.app.bridge.uninett.no';
		ui.addItem(item);
	}
	ui.ready();
	// $(".appBox").equalHeights(30, 300);
	
}

// function feidemoreinfo(id, info) {

// 	var cur = $("#feide-" + id);

// 	// console.log("Info"); console.log(info);
// 	cur.find("p.descr").html(info.beskrivelse_no);
// 	cur.find("p.tilbyder").html(info.tilbyder);
// 	if (info.login_url) {
// 		cur.append('<a href="' + info.login_url + '" class="run-app-button">Login</a> ');
// 	}
// 	if  (info.mer_info) {
// 		cur.append('<a href="' + info.mer_info + '" class="run-app-button">More info</a> ');	
// 	}
// 	var ind = cur.index();
// 	// console.log("Item is index " + ind);
// 	if ((ind % 2) === 0) {
// 		// console.log("Odd " + (ind % 2))
// 		// console.log(cur.next().andSelf());
// 		cur.next().andSelf().equalHeights(30, 300);
// 	} else {
// 		// console.log("Even " + (ind % 2))
// 		// console.log(cur.prev().andSelf());
// 		cur.prev().andSelf().equalHeights(30, 300);
// 	}
	

// }

function feidemore(id, callback) {
	var url = 'https://tjenester.uninett.no/feide/api/get_sp_info?id=' + id;
	UWAP.data.get(url, null, function(o) {
		o.id = id;
		o.descr = o.beskrivelse_no; delete o.beskrivelse_no;
		o.provider = o.tilbyder; delete o.tilbyder; 
		o.link_run = o.login_url; delete o.login_url;
		o.link_moreinfo = o.mer_info; delete o.mer_info;
		callback(o);
	});
}


// function feideinfo(info) {

// 	var appbox = $('<li id="feide-' + info.id + '" class="appBox"></li>');
// 	appbox.append('<img src="/_/api/logo.php?app=test" class="appLogo" />');
// 	appbox.append('<h3>' + info.name + '</h3>');
// 	appbox.append('<p class="tilbyder"></p>');
// 	appbox.append('<p class="descr"></p>');
	
// 	// appbox.append('<a href="http://feide.no" class="run-app-button">Run app</a> ');
// 	// appbox.append('<a href="http://' + id + '.app.bridge.uninett.no" class="run-app-button">View info</a> ');

// 	feidemore(info.id);

// 	$("#appListing").append(appbox);
// }

function feidelisting(ui, o) {
	// console.log("FEIDE");
	// console.log(o);

	for(var i = 0; i < o.length; i++) {
		ui.addItem(o[i]);
		console.log("feide tjeneste"); console.log(o[i]);

		feidemore(o[i].id, function (updateditem) {
			ui.updateItem(updateditem);
		});

	}
	ui.ready();

	// $.each(o, function(i, item) {
	// 	feideinfo(item);
	// });
	
	// $(".appBox").equalHeights(30, 300);
}

$(document).ready(function() {
	UWAP.data.get("https://tjenester.uninett.no/feide/api/get_published_sp_list", null, function(data) {
		feidelisting(ui, data)
	});

	$("#radioProvider").buttonset();
	$("#radioTarget").buttonset();
	
	var ui = storeUI($("#appListingContainer"));

	UWAP.applisting.list(function(data) {
		applisting(ui, data);
	});

	$("#searchfield").uwapsearch(ui.filter);
	$("#searchfield").focus();
});

