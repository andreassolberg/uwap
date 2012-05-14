require(["unibar"], function(unibar) {
    //This function is called when scripts/helper/util.js is loaded.
    //If util.js calls define(), then this function is not fired until
    //util's dependencies have loaded, and the util argument will hold
    //the module value for "helper/util".
    

    var menu = [

    	{
    		title: "About UNINETT", 
    		url: "http://www.uninett.no"
    	},
    	{
    		title: "Foodle", 
    		url: "https://foodl.org"
    	},
    	{
    		title: "DiscoJuice docs", 
    		url: "http://localhost/~andreas/unibartest/"
    	}
    ];
    
    console.log("Loading complete");
    console.log(unibar);



	function loggedin(user) {
		$("div#accountinfo").prepend('<p>Logged in as <strong>' + user.name + '</strong> (<tt>' + user.userid + '</tt>)</p>');
		// $("input#smt").attr('disabled', 'disabled');



		// var gr = $('<dl></dl>')
		// if(user.groups) {
		// 	groups = user.groups;
		// 	for(var key in user.groups) {
		// 		gr.append('<dt>' + user.groups[key] + '</dt>');
		// 		gr.append('<dd><tt>' + key + '</tt></dd>');

		// 	}
		// }
		// $("div#out").append('<p>Groups:</p>').append(gr);


	}
	function notloggedin() {
		$("div#accountinfo").prepend('<p>Not logged in</p>');
		$("div#accountinfo").bind('click', function() {
			UWAP.auth.require(loggedin);
		});
	}


	

	$("document").ready(function() {

		UWAP.auth.check(loggedin, notloggedin);

		$.each(menu, function(i, item) {
			console.log("entry", item);
			var mi = $('<div class="menuitem"><a target="_parent" href="' + item.url + '">' + item.title + '</a></div>');
			$("div#menu").append(mi);
			console.log(mi);
			console.log($("div#menu"));
		});


	});


});



