define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core')
    	;
	
	require("uwap-core/js/uwap-people");

    require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap/js/bootstrap');	
	require('uwap-core/bootstrap/js/bootstrap-collapse');	
	require('uwap-core/bootstrap/js/bootstrap-dropdown');

	/**
	 * Welcome to UWAP. Replace this section with your own main script.
	 */

	UWAP.auth.require(function(data) {
		$("div#out").append("<h2>You are logged in (passive check) as - <i>" + data.name + "</i></h2>");

		$("#peoplesearch").focus().peopleSearch({
			callback: function(item) {
				$("#pl").append('<li><strong>' + item.name + '</strong> from ' + item.o + '</li>');
			}
		});


		// $("#peoplesearch2").on('keyup', function() {
		// 	var q = $("#peoplesearch").val();
		// 	console.log('search: ', q);


		// 	if (q.length < 2) return;

		// 	UWAP.people.query(q, function(data) {
		// 		$("#peoplelist").empty();
		// 		$.each(data, function(i, item) {
		// 			if(item.userid){
						// var e = $('<li style="clear: both"><a href=""></a></li>');
						// var el = e.find('a');

						// if (item.jpegphoto) {
						// 	el.append('<img class="img-polaroid" style="margin: 5px; float: left; max-width: 64px; max-height:'
						// 			+'64px; border: 1px solid #ccc" src="data:image/jpeg;base64,' 
						// 			+ item.jpegphoto
						// 			+ '" />');
						// } else {
						// 	el.append('<img class="img-polaroid" style="margin: 5px; float: left; max-width: 64px; max-height:'
						// 			+'64px; border: 1px solid #ccc" src="/img/placeholder.png" />');
						// 	// Got from here: http://www.veodin.com/wp-content/uploads/2012/01/placeholder.png
						// }
						// var iName = $('<h4 style="margin: 0px;">' + item.name + ' </h4>').appendTo(el);

						// // $('<button type="button" class="btn btn-success btn-mini">Add</button>').appendTo(iName).click(function(){
						// // 	gr.addMember(item);
						// // 	e.remove();
						// // });

						// $("#peoplelist").append(e);

						// var e2 = '<p style="margin: 0px"><span style="margin-right: 15px;"><i class="icon-briefcase"></i> ' +
						// item.o + '</span></p>';
						// e2 += '<p style="margin: 0px"><span style="margin-right: 15px;"><i class="icon-user"></i> ' +
						// item.userid + '</span></p>';
						// e2 += '<p style="margin: 0px"><span style="margin-right: 15px;"><i class="icon-envelope"></i> ' +
						// item.mail + '</span></p>';
						// // e2 += '<span>' + JSON.stringify(item) + '</span>';
						// el.append(e2);

		// 			}

		// 		});

		// 	});

		// });

	});

});