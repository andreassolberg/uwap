require(["unibar"], function(unibar) {
    //This function is called when scripts/helper/util.js is loaded.
    //If util.js calls define(), then this function is not fired until
    //util's dependencies have loaded, and the util argument will hold
    //the module value for "helper/util".
    

    var menu = [

    	{
    		title: "Eureka", 
    		url: "http://agora.uninett.no",
    		groups: ["@realm:uninett.no"]

    	},
    	{
    		title: "Agora", 
    		url: "http://agora.uninett.no"
    	},
    	{
    		title: "Foodle", 
    		url: "https://beta.foodl.org",
    		match: "foodl\.org"
    	},
    	{
    		title: "Kundeportal", 
    		url: "https://kunde.feide.no",
    		groups: ["@orgunit:uninett.no:0a02f6092fa92d03b3b29a792eeafeac7b7f2b11"]
    	},
    	{
    		title: "FeideTools", 
    		url: "https://tools.feide.no",
    		groups: ["@orgunit:uninett.no:0a02f6092fa92d03b3b29a792eeafeac7b7f2b11"]
    	},
    	{
    		title: "eCampus", 
    		url: "https://kunde.feide.no",
    		groups: ["@orgunit:uninett.no:1e7a0caefeefb920ac3b3501b71f9d2f7943e013"]
    	},
    	{
    		title: "DiscoJuice docs", 
    		url: "http://bridge.uninett.no/bar.html",
    		match: "bridge\.uninett\.no"
    	}
    ];
    
    console.log("Loading complete");
    console.log(unibar);


	function loggedin(user) {
		$("div#accountinfo").prepend('<p><a target="_blank" href="http://logout.feide.no"><span style="font-size: 80%">(logout)</span></a> ' + user.name + '</p>');
		menuSetup(menu, user);
	}
	function notloggedin() {
		$("div#accountinfo").prepend('<p>Login…</p>');
		$("div#accountinfo").bind('click', function() {
			// UWAP.auth.require(loggedin);
			// window.open('http://unibar.app.bridge.uninett.no/login.html');
			$("div#accountinfo").empty();
			var win = window.open('http://unibar.app.bridge.uninett.no/login.html', 'google','width=800,height=600,status=0,toolbar=0');   
			var timer = setInterval(function() {   
			    if(win.closed) {  
			        clearInterval(timer);  
			        UWAP.auth.checkPassive(loggedin, notloggedin);
			    }  
			}, 1000); 
		});
		menuSetup(menu);
	}

	function active(item) {

		if (!item.match) return false;
		var r = new RegExp(item.match, "gi");
		var cu = decodeURIComponent(window.location.hash.substr(1));
		console.log("comparing against", cu);
		return (r.exec(window.location.hash));
	}

	function include(includegroups, usergroups) {

		console.log("Check if user member of these groups ", usergroups, " should access with this acl ", includegroups);

		var k, i;
		for(k in usergroups) {
			for(i = 0; i < includegroups.length; i++) {
				if (includegroups[i] === k) return true;
			}
		}
		return false;
	} 

	function menuSetup(menu, user) {
		$("div#menu").empty();
		$.each(menu, function(i, item) {

			if(item.groups && !user) return;
			if (item.groups && !include(item.groups, user.groups)) return;

			console.log("entry", item);
			var mi = $('<div class="menuitem"><a target="_parent" href="' + item.url + '">' + item.title + '</a></div>');

			if (active(item)) {
				$(mi).addClass("menuitem_active");
			}

			$("div#menu").append(mi);
			console.log(mi);
			console.log($("div#menu"));
		});
	}


	$("document").ready(function() {

		UWAP.auth.checkPassive(loggedin, notloggedin);

	});


});



