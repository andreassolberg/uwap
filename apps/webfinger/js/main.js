require(["webfinger"], function(webfinger) {
    //This function is called when scripts/helper/util.js is loaded.
    //If util.js calls define(), then this function is not fired until
    //util's dependencies have loaded, and the util argument will hold
    //the module value for "helper/util".
    
    
    console.log("Loading complete");
    console.log(webfinger);



$("document").ready(function() {

	$("#search").focus().bind('change', function() {

		console.log("Change");
		var userid = $("#search").attr("value");

	    webfinger.finger(userid, function(res) {
    		$("div#out").empty();
    		$("div#out").append("<p>Results for " + userid + "</p>");
    		var ul = $('<ul></<ul>');
	    	$.each(res, function(i, item) {

				ul.append('<li><a href="' + item.href + '">' + item.rel + '</a></li>');

	    	});
			$("div#out").append(ul);	    	
	    });

	});


	// UWAP.data.get('http://www.vegvesen.no/trafikk/xml/savedsearch.xml?id=600', 
	// 	{'xml': 1},
	// 	vegmelding);
	
	// UWAP.data.get('https://foodl.org/api/activity', {handler: "foodle"}, activity);
	// UWAP.data.get('http://bridge.uninett.no/rest.php', {handler: "plain"}, generic);

});


});



