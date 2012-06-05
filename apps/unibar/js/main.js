require(["unibar"], function(unibar) {
    //This function is called when scripts/helper/util.js is loaded.
    //If util.js calls define(), then this function is not fired until
    //util's dependencies have loaded, and the util argument will hold
    //the module value for "helper/util".
    

    var menu = [
    	{
    		title: "UNINETT",
    		url: "http://www.uninett.no",
    		match: "www\.uninett\.no"
    	},
    	{
    		title: "Eureka/Intern", 
    		url: "http://eureka.uninett.no",
    		match: "eureka\.uninett\.no",
    		groups: ["@realm:uninett.no"],
    		children: [
    			{
		    		title: "Eureka",
		    		url: "http://eureka.uninett.no",
		    		match: "eureka\.uninett\.no",
		    		groups: ["@realm:uninett.no"]
    			},
    			{
		    		title: "Kalender",
		    		url: "http://calendar.uninett.no",
		    		match: "calendar\.uninett\.no"
    			},
    			{
		    		title: "Planweb",
		    		url: "https://eureka.uninett.no/planweb",
		    		match: "eureka.uninett.no.planweb"
    			},
    			{
		    		title: "Agresso",
		    		url: "http://krone.uninett.no",
		    		match: "krone.uninett.no"
    			},
    			{
		    		title: "Webmail",
		    		url: "https://webmail.uninett.no/src/login.php",
		    		match: "webmail.uninett.no"
    			}
    		]
    	},
    	{
			title: "Nett", 
			url: "https://www.uninett.no/nettleveranse",
			match: "www.uninett.no.nett",
			children: [
				{
					title: "Driftsportalen", 
					url: "https://drift.uninett.no",
					match: "drift.uninett.no"
				},
				{
					title: "Bigbrother", 
					url: "https://bigbrother.uninett.no",
					match: "bigbrother.uninett.no",
					groups: ["@realm:uninett.no"]
				},
				{
					title: "Stager", 
					url: "http://stager.uninett.no",
					match: "stager.uninett.no"
				}
			]
		},
		{
			title: "Samarbeid", 
			url: "http://agora.uninett.no",
			match: "agora\.uninett\.no",
			children: [
		    	{
		    		title: "Agora", 
		    		url: "http://agora.uninett.no",
		    		match: "agora\.uninett\.no"
		    	},
		    	{
		    		title: "Webmøter", 
		    		url: "http://connect.uninett.no",
		    		match: "connect.uninett.no"
		    	},
		    	{
		    		title: "Foodle", 
		    		url: "https://foodl.org",
		    		match: "foodl\.org"
		    	},
		    	{
		    		title: "OpenWiki", 
		    		url: "https://openwiki.uninett.no",
		    		match: "openwiki.uninett.no"
		    	},
		    	{
		    		title: "Redmine", 
		    		url: "https://redmine.uninett.no",
		    		match: "redmine.uninett.no"
		    	},
		    	{
		    		title: "Cloudstor", 
		    		url: "https://cloudstor.uninett.no/",
		    		match: "cloudstor.uninett.no"
		    		
		    	}
			]
		},
    	{
    		title: "Media", 
    		url: "http://ecampus.no",
    		match: "ecampus.no",
    		// groups: ["@orgunit:uninett.no:1e7a0caefeefb920ac3b3501b71f9d2f7943e013"]
			children: [
		    	{
		    		title: "ecampus.no", 
		    		url: "http://ecampus.no",
		    		match: "ecampus.no"
		    	},
		    	{
		    		title: "Camtasia Relay", 
		    		url: "https://relay.ecampus.no/Relay/",
		    		match: "relay.ecampus.no"
		    		// groups: ["@orgunit:uninett.no:0a02f6092fa92d03b3b29a792eeafeac7b7f2b11"]
		    	},
		    	{
		    		title: "IP TV", 
		    		url: "http://forskningsnett.uninett.no/tv/kanaler.html",
		    		match: "forskningsnett.uninett.no.tv.kanaler.html"
		    	}
			]
    	},
		{
			title: "Feide", 
			url: "http://feide.no/",
			match: "feide.no",
			children: [
		    	{
		    		title: "feide.no", 
		    		url: "http://feide.no",
		    		match: "www.feide.no"
		    	},
		    	{
		    		title: "FeideTools", 
		    		url: "https://tools.feide.no",
		    		match: "tools\.feide\.no",
		    		groups: ["@orgunit:uninett.no:0a02f6092fa92d03b3b29a792eeafeac7b7f2b11"]
		    	},
		    	{
		    		title: "Kundeportal", 
		    		url: "https://kunde.feide.no",
		    		match: "tjenester\.uninett\.no",
		    		groups: ["@orgunit:uninett.no:0a02f6092fa92d03b3b29a792eeafeac7b7f2b11"]
		    	},
		    	{
		    		title: "Innsyn", 
		    		url: "https://innsyn.feide.no",
		    		match: "idp.feide.no.simplesaml.module.php.attribViewer"
		    	},
		    	{
		    		title: "DiscoJuice", 
		    		url: "http://discojuice.org/",
		    		match: "discojuice\.org"
		    	},
		    	{
		    		title: "SimpleSAMLphp", 
		    		url: "http://simplesamlphp.org/",
		    		match: "simplesamlphp.org"
		    	},
		    	{
		    		title: "saml2int", 
		    		url: "http://saml2int.org/",
		    		match: "saml2int.org"
		    	}
			]
		},
	   	{
    		title: "Tungregning og lagring", 
    		url: "https://www.uninett.no/regne-og-lagringsressurser",
    		match: "www.uninett.no.regne-og-lagringsressurser",
    		// groups: ["@orgunit:uninett.no:1e7a0caefeefb920ac3b3501b71f9d2f7943e013"]
			children: [
		    	{
		    		title: "Sigma", 
		    		url: "https://www.uninett.no/sigma",
		    		match: "www.uninett.no.sigma"
		    	},
		    	{
		    		title: "Notur", 
		    		url: "http://www.notur.no/",
		    		match: "notur.no"
		    	},
		    	{
		    		title: "Norgrid", 
		    		url: "http://www.norgrid.no",
		    		match: "norgrid.no"
		    	},
		    	{
		    		title: "Norstore", 
		    		url: "http://www.norstore.no/",
		    		match: "norstore.no"
		    	}
			]
    	},
	   	{
    		title: "Administrativt", 
    		url: "https://fasportalen.uninett.no/portal/",
    		match: "fasportalen.uninett.no",
			children: [
		    	{
		    		title: "Fasportalen", 
		    		url: "https://fasportalen.uninett.no/portal/",
		    		match: "fasportalen.uninett.no"
		    	},
		    	{
		    		title: "Basware", 
		    		url: "https://uninett.basware-saas.com/invoice/Global_Login.asp",
		    		match: "uninett.basware-saas.com"
		    	},
		    	{
		    		title: "Visma", 
		    		url: "https://www.eu-supply.com/login.asp?B=KGV&target=&timeout=",
		    		match: "www.eu-supply.com"
		    	},
		    	{
		    		title: "Contempus", 
		    		url: "http://contempus.no",
		    		match: "contempus.no"
		    	}
			]
    	},



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

	function isActive(item) {

		if (!item.match) return false;
		var r = new RegExp(item.match, "gi");
		var cu = decodeURIComponent(window.location.hash.substr(1));
		// console.log("comparing against", cu, item.match, (r.exec(window.location.hash)));
		return !!(r.exec(cu));
	}

	function include(includegroups, usergroups) {

		// console.log("Check if user member of these groups ", usergroups, " should access with this acl ", includegroups);

		var k, i;
		for(k in usergroups) {
			for(i = 0; i < includegroups.length; i++) {
				if (includegroups[i] === k) return true;
			}
		}
		return false;
	} 

	function menuTraverse(menu, user) {
		var
			active = false,
			activecount = 0,
			cmenu;

		cmenu = menu.reverse();
		$.each(cmenu, function(i, item) {

			if(item.groups && !user) return;
			if (item.groups && !include(item.groups, user.groups)) return;

			cmenu[i].show = true;

			if (isActive(item) && activecount++ < 1) {

				cmenu[i].active = true;
				active = true;
			}

			if (item.hasOwnProperty("children")) {
				if (menuTraverse(cmenu[i].children, user)) {
					active = true;
					cmenu[i].active = true;
					cmenu[i].show = true;
				}
			}

		});
		menu.reverse();

		// console.log("Processing row ", active, menu);

		return active;
	}

	function menuRowDisplay(menu, user) {
		var nextlevel = null;
		menurow = $('<div class="menurow"></div>');

		$.each(menu, function(i, item) {

			if(!item.show) return;

			var mi = $('<div class="menuitem"><a target="_parent" href="' + item.url + '">' + item.title + '</a></div>');

			if (item.active) {
				$(mi).addClass("menuitem_active");
				if (item.hasOwnProperty("children")) {
					nextlevel = item.children;
				}
			}
			menurow.append(mi);

		});
		$("div#menu").append(menurow);
		if (nextlevel) {
			// console.log("Preparing next level", nextlevel);
			menuRowDisplay(nextlevel, user);
		}
	}

	function menuSetup(menu, user, empty) {
	
		$("div#menu").empty();	

		menuTraverse(menu, user);
		console.log(menu); // return;

		menuRowDisplay(menu, user);
		
	}


	$("document").ready(function() {

		UWAP.auth.checkPassive(loggedin, notloggedin);

	});


});



