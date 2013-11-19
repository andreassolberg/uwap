//Requires bootstrap typeahead
(function( $ ){

	$.fn.kontakt = function( options ) {
		var settings = $.extend( {
			'letter'         : 4,
			'callback' : function(d){ console.log(d);}
		}, options);
		
		return this.each(function() { 
			var letter = settings.letter;
			var updatedSource = false; 
			var memberList = {
					"Olav Morken": {
						'userid': 'olavmo@uninett.no',
						'mail': 'olav.morken@uninett.no',
						'name': 'Olav Morken'
					},
					"Andreas 흆re Solberg": {
						"userid": "andreas@uninett.no",
						"name": "Andreas 흆re Solberg"
					},
					"Terje Navjord": {
						"userid": "navjord@uninett.no",
						"mail": "terje.navjord@uninett.no",
						"name": "Terje Navjord"
					},
					"Anders Lund": {
						"userid": "anders@uninett.no",
						"mail": "anders@uninett.no",
						"name": "Anders Lund"
					},
					"Simon": {
						"userid": "simon@uninett.no",
						"mail": "simon@uninett.no",
						"name": "Simon"
					},
					"Armaz": {
						"userid": "armaz@uninett.no",
						"mail": "armaz@uninett.no",
						"name": "Armaz Mellati"
					}
			};
			var $this = $(this);
			$this.keyup(function(){
				if($this.val().length==letter){
					UWAP.data.get('http://www.katalog.uninett.no/ldap/finn/?navn='+$this.val()+'&org=uninett&sok=s%F8k&valg1=cn&valg2=ou&valg3=telephonenumber&valg4=mail', null, 
							function(d){
						console.log(d);
					});
					UWAP.data.get('http://www.katalog.uninett.no/ldap/finn/', {data: {navn: $this.val(), org: 'uninett', sok: 's%F8k', valg1: 'cn', valg2: 'ou', valg3: 'telephonenumber', valg4: 'mail'}}, 
							function(d){
						console.log(d);
					});
					UWAP.data.get('http://www.uio.no/personer/?person-query='+$this.val()+'&vrtx=person-search&areacode=900000&lang=no&scope=%2Fpersoner', null, 
							function(d){
						var testRE = $(d);
						var res = testRE.find("div.vrtx-person-search-hits");
						res = res.find('tbody');
						res = res.find('tr');
						var tempNames = ['Terje Navjord', 'Andreas 흆re Solberg', 'Olav Morken', 'Anders Lund', 'Simon', 'Armaz'];
						if(res.length < 1){
							letter = $this.val().length+1;
						}
						else{
							letter = settings.letter;
							$.each(res, function(i, tr){
								var tempMember = {};
								if($(tr).find('td.vrtx-person-listing-email').find('a')[0]){
									tempMember.name = $(tr).find('td.vrtx-person-listing-name').find('a')[1].innerHTML;
									tempMember.mail = $(tr).find('td.vrtx-person-listing-email').find('a')[0].innerHTML;
									tempMember.userid = tempMember.mail;
									tempNames.push(tempMember.name);
									memberList[tempMember.name] = tempMember;
								}
							});
							var autocomplete = $this.typeahead();
							autocomplete.data('typeahead').source = tempNames;
							updatedSource = true;
						}
					});
				}
				else if(updatedSource == true && $this.val().length<letter){
					updatedSource = false;
					var autocomplete = $this.typeahead();
					autocomplete.data('typeahead').source = ['Terje Navjord', 'Andreas 흆re Solberg', 'Olav Morken', 'Anders Lund', 'Simon', 'Armaz'];
				}
			});
			$this.typeahead({source: ['Terje Navjord', 'Andreas 흆re Solberg', 'Olav Morken', 'Anders Lund', 'Simon', 'Armaz']});
			$this.typeahead().change(function(){
				if(memberList[$this.val()]){
					settings.callback(memberList[$this.val()]);
					$this.val('');
				}
			});
		});
    
  };
})( jQuery );