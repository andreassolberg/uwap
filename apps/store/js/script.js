
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

function feidemore(id, callback) {
	var url = 'https://tjenester.uninett.no/feide/api/get_sp_info?id=' + id;
	UWAP.data.get(url, null, function(o) {
		o.id = id;
		o.logo = 'feide/' + sha1(o.entityid) + '.png';
		o.descr = o.beskrivelse_no; delete o.beskrivelse_no;
		o.provider = o.tilbyder; delete o.tilbyder; 
		o.link_run = o.login_url; delete o.login_url;
		o.link_moreinfo = o.mer_info; delete o.mer_info;
		callback(o);
	});
}


function feidelisting(ui, o) {
	// console.log("FEIDE");
	// console.log(o);

	for(var i = 0; i < o.length; i++) {
		ui.addItem(o[i]);
		// console.log("feide tjeneste"); console.log(o[i]);

		// if (i > 5) continue;

		feidemore(o[i].id, function (updateditem) {
			ui.updateItem(updateditem);
		});

	}
	ui.ready();
	// $(".appBox").equalHeights(30, 300);
}

function getSelection() {
	var res = {};
	res.provider = $("form input[name=radioProvider]:checked").val();
	res.target = $("form input[name=radioTarget]:checked").val();
	return res;
}

$(document).ready(function() {
	UWAP.data.get("https://tjenester.uninett.no/feide/api/get_published_sp_list", null, function(data) {
		feidelisting(ui, data)
	});

	var ui = storeUI($("#appListingContainer"));

	$("#radioProvider").buttonset();
	$("#radioTarget").buttonset();

	$("form").on("change", "input", function(event) {
		// console.log("Change value : ");
		// console.log($(this));
		// console.log($(this).find("select").value());
		var term = $("#searchfield").val();
		ui.filter(term, getSelection());

	});
	
	

	UWAP.applisting.list(function(data) {
		applisting(ui, data);
	});

	$("#searchfield").uwapsearch(ui.filter, getSelection);
	$("#searchfield").focus();
});

