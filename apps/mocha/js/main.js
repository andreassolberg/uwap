define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		hogan = require('uwap-core/js/hogan'),
//		mocha = require('lib/mocha'),
		UWAP = require('uwap-core/js/core');
	require('lib/mocha');
	require('lib/expect');
	require("lib/director");
	
	require("uwap-core/js/uwap-people");
	require('uwap-core/bootstrap/js/bootstrap');	
//	require('uwap-core/bootstrap/js/bootstrap-collapse');
//	require('uwap-core/bootstrap/js/bootstrap-modal');
//	require('uwap-core/bootstrap/js/bootstrap-typeahead');
//	require('uwap-core/bootstrap/js/bootstrap-button');
//	require('uwap-core/bootstrap/js/bootstrap-tooltip');
//	require('uwap-core/bootstrap/js/bootstrap-tab');
	
	var tmpl = {
//		    "detCont": require('uwap-core/js/text!templates/detailsContainer.html')
	};
	
	var templates = {
//			"appdashboard": hogan.compile(tmpl.appdashboard),
	};
	
	$(document).ready(function() {
		var userObj = null;
		var createdGroup = null;
		var createdFeedItem = null;
		var createdApp = null;
		var createdAuthzHandler = null;
		
		$('#'+window.location.search.replace('?grep=', '').toLowerCase()).parent().addClass('active');
		$('#standard').click(function(){
			window.location.search = "?grep=Standard";
		});
		$('#optional').click(function(){
			window.location.search = "?grep=Optional";
		});
		
		 function assert(expr, msg) {
		        if (!expr) throw new Error(msg || 'failed');
	      }
		 console.log(window.location.search);
		 if(window.location.search == null || window.location.search == ""){
			window.location.search = "?grep=Standard"; 
			 
		 }
		 
		 mocha.setup('bdd'); 
		 
		 describe("Standard Logged-In Tests", function(){
			 
		 describe("UWAP.auth", function(){
				
			 describe("#require()", function(){
				 it("should eventually return user", function(done){
					UWAP.auth.require(function(user){
						console.log(user);
						userObj = user;
						expect(user.userid).not.to.be(null);
						done();
					}); 
				 });
				
			 	});
			 
			 describe("#check()", function(){
				 it("should return user logged in", function(done){
					UWAP.auth.check(function(user){
						console.log(user);
						expect(user.userid).not.to.be(null);
						done();
					}, function(){}); 
				 });
				
			 	});
			 
			 describe("#checkPassive()", function(){
				 it("should return user logged in", function(done){
					UWAP.auth.check(function(user){
						console.log(user);
						expect(user.userid).not.to.be(null);
						done();
					}, function(){}); 
				 });
				
			 	});
			 
			}); 
		 
		 describe("UWAP.data", function(){
			
			 describe("#get()", function(){
				 it("should get VGNett without error or null", function(done){
					UWAP.data.get('http://www.vg.no', {}, function(d){
						expect(d).not.to.be(null);
						done();
					}, function(err){ console.log(err); throw err;}); 
				 });
				 
				 it("should send and receive using Custom Header", function(done){
					 UWAP.data.get('http://app.solweb.no/misc/reflect.php', {handler: 'custom1'}, function(d){
						 console.log(d);
						 expect(d['Special-Key1']).to.be('Special-Value1');
						 done();
					 }, function(err){throw err;}); 
				 });
				 
				 it("should send and receive using Custom Header (with user)", function(done){
					 UWAP.data.get('http://app.solweb.no/misc/reflect.php', {handler: 'custom2'}, function(d){
						 console.log(d);

						 expect(d['Special-Key2']).to.be('Special-Value2');
						 if(userObj != null){
							 expect(d['UWAP-UserID']).to.be(userObj.userid);
						 }
						 else{
							 expect(d['UWAP-UserID']).to.be.a('string');
						 }
						 expect(d['UWAP-Groups']).to.be.a('string');
						 done();
					 }, function(err){throw err;}); 
				 });
				 
				 it("should send and receive using Basic Authentication", function(done){
					 UWAP.data.get('http://app.solweb.no/misc/reflect.php', {handler: 'basic1'}, function(d){
						 console.log(d);
//						 if(d["Authorization"] == undefined){
//							 d = JSON.parse(d);
//						 }
						 expect(d["Authorization"]).to.be.a('string');
						 done();
					 }, function(err){throw err;}); 
				 });
				 
				 it("should send and receive using Basic Authentication (with user)", function(done){
					 UWAP.data.get('http://app.solweb.no/misc/reflect.php', {handler: 'basic2'}, function(d){
						 console.log(d);
//						 if(d["Authorization"] == undefined){
//							 d = JSON.parse(d);
//						 }
						 expect(d["Authorization"]).to.be.a('string');
						 if(userObj != null){
							 expect(d['UWAP-UserID']).to.be(userObj.userid);
						 }
						 else{
							 expect(d['UWAP-UserID']).to.be.a('string');
						 }
						 expect(d['UWAP-Groups']).to.be.a('string');
						 done();
					 }, function(err){throw err;}); 
				 });
				 
			 	});
			 
			 
			 	
			}); 
		 
		 	
		 
			 describe("UWAP.store", function(){
				describe("#save()", function(){
					it("should run the example from docs.uwap.org (with an added comma)", function(done){
						UWAP.store.save(
								{
									"test": "value",
									"size": Math.floor(Math.random()*1000),
									"bool": true,
									"speed": 1.2333,
									"geolocation": {
										"city": "Trondheim",
										"code": "7040"
									}
								}, function() {
									console.log("Successfully stored object.")
									done();
								}, function(err) {
									console.log("Error storing object: " + err.message)
								}
							);
					});
				});
				
				describe("#queryOne()", function(){
					it("should run the example from docs.uwap.org", function(done){
						UWAP.store.queryOne(
								{"bool": true},
								function(res) {
									console.log("Query one returned result:");
									console.log(res);
									res.text = "Modified2";
									console.log("Is about to save entry with id: ", res["_id"]);
									UWAP.store.save(res, function() {
										console.log("Successfully stored modified attribute");
										done();
									});
								}
							);
					});
				});
				
				describe("#queryList()", function(){
					it("should run the example from docs.uwap.org", function(done){
						UWAP.store.queryList(
								{"bool": true},
								function(res) {
									console.log(res);
									done();
								}, function(err) {
									console.log(err);
									throw err;
								}
							);
					});
				});
				
				describe("#remove()", function(){
					it("should run the example from docs.uwap.org", function(done){
						UWAP.store.remove(
								{"bool": true},
								function() {
									console.log("Successfully deleted");
									done();
								}, function(err) {
									console.log("Error:" + err);
									throw err;
								}
							);
					});
				});
			 });
			 
			 
			 describe("UWAP.groups", function(){
					describe("#listMyGroups()", function(){
						it("should return a list of groups", function(done){
							UWAP.groups.listMyGroups(
									function(d){
										console.log(d);
										done();
									}, function(err){console.log(err); throw err;}
								);
						});
					});
					describe("#get()", function(){
						it("should return your first group", function(done){
							UWAP.groups.listMyGroups(
									function(d){
										UWAP.groups.get( d[0].id,
												function(d){
													console.log(d);
													done();
												}, function(err){console.log(err); throw err;}
										);
									}, function(err){console.log(err); throw err;}
								);
							
						});
					});
					
					describe("#addGroup()", function(){
						it("should run the example from docs.uwap.org (with added 'listable'-attribute, and without setting the id) and return group", function(done){
							UWAP.groups.addGroup({title: 'A new group...', description: 'description of the new group...', listable: false},
									function(groupinfo) {
										// groupinfo now contains the stored group object as if it was retrieved using get().
										createdGroup = groupinfo;
										expect(groupinfo).not.to.be.a('string');
										console.log("Successfully added new group", groupinfo);
										done();
									}, function(err) {
										console.log("Error:" + err);
									}
								);
							
						});
					});
					
					describe("#updateGroup()", function(){
						it("should run the example from docs.uwap.org", function(done){
							UWAP.groups.updateGroup(createdGroup.id, {'title': 'A new title...'},
									function() {
										console.log("Successfully updated");
										done();
									}, function(err) {
										throw err;
										console.log("Error:" + err);
									}
								);
							
						});
					});
					
					describe("#addMember()", function(){
						it("should run the example from docs.uwap.org", function(done){
							UWAP.groups.addMember(createdGroup.id, 
									{
										userid: "andreas@uninett.no",
										name: "Andreas Åkre Solberg",
										admin: true
									},
									function() {
										console.log("Andreas is no longer member of the group...");
										done();
									}, function(err) {
										console.log("Error:" + err);
										throw err;
									}
								);
							
						});
					});
					
					describe("#updateMember()", function(){
						it("should run the example from docs.uwap.org (with an added comma)", function(done){
							UWAP.groups.updateMember(createdGroup.id, 'andreas@uninett.no', {'admin': false},
							function() {
								console.log("Andreas is no longer admin...");
								done();
							}, function(err) {
								console.log("Error:" + err);
								throw err;
							}
						);
						});
					});
					
					describe("#removeMember()", function(){
						it("should run the example from docs.uwap.org (with an added comma)", function(done){
							UWAP.groups.removeMember(createdGroup.id, 'andreas@uninett.no',
									function() {
										console.log("Andreas is no longer member of the group...");
										done();
									}, function(err) {
										console.log("Error:" + err);
										throw err;
									}
								);
						});
					});
					
					describe("#removeGroup()", function(){
						it("should remove the just created group", function(done){
							UWAP.groups.removeGroup(createdGroup.id, 
									function() {
										console.log("Successfully removed");
										done();
									}, function(err) {
										console.log("Error:" + err);
										throw err;
									}
								);
						});
					});
					
					describe("#listPublic()", function(){
						it("should list public groups", function(done){
							UWAP.groups.listPublic(
									function(d) {
										console.log(d);
										expect(d).not.to.be(null);
										done();
									}, function(err) {
										console.log("Error:" + err);
										throw err;
									}
								);
						});
					});
					
					describe("#subscribe()", function(){
						it("should make you subscribe to Terena TF-EMC2", function(done){
							UWAP.groups.subscribe("f7954059-690a-4904-9a5e-04f45b0fae41", function(d){
								console.log(d);
								expect(d).not.to.be(null);
								done();
							}, function(err){throw err;});
						}
						);
							
					});
					
					describe("#unsubscribe()", function(){
						it("should make you unsubscribe to Terena TF-EMC2", function(done){
							UWAP.groups.unsubscribe("f7954059-690a-4904-9a5e-04f45b0fae41o", function(d){
								console.log(d);
//								expect(d).not.to.be(null);
								done();
							}, function(err){throw err;});
						}
						);
							
					});
					
			 });
			 
			 describe("UWAP.people", function(){
					describe("#listRealms()", function(){
						it("should return realms", function(done){
							UWAP.people.listRealms( 
									function(d){
										console.log(d);
										expect(d).not.to.be(null);
										done();
									}, function(err){console.log(err); throw err;}
								);
						});
					});
					
					describe("#query()", function(){
						it("should return people", function(done){
							UWAP.people.listRealms( 
									function(d){
										console.log(d);
										UWAP.people.query( d[0].realm, 'Andreas',
											function(d2){
											console.log(d2);
											expect(d2).not.to.be(null);
											done();
											}, function(err){console.log(err); throw err;}
										);
										
									}, function(err){console.log(err); throw err;}
								);
							
						});
					});
					
			 });
			 
			 describe("UWAP.feed", function(){
					describe("#upcoming()", function(){
						it("should return without error", function(done){
							UWAP.feed.upcoming( {},
									function(d){
										console.log(d);
										done();
									}, function(err){console.log(err); throw err;}
								);
						});
					});
					describe("#notifications()", function(){
						it("should return without error", function(done){
							UWAP.feed.notifications( {},
									function(d){
										console.log(d);
										done();
									}, function(err){console.log(err); throw err;}
								);
						});
					});
					
					describe("#readItem()", function(){
						it("should return without error, but just if you have notifications", function(done){
							UWAP.feed.notifications( {},
									function(d){
										console.log(d);
										if(d.items != "null"){
											UWAP.feed.readItem( d.items[0].id,
												function(d){
													console.log(d);
													done();
												}, function(err){console.log(err); throw err;}
											);
										}
								done();
							}, function(err){console.log(err); throw err;}
							);
							
						});
					});
					
					describe.skip("#post()", function(){
						it("should return without error", function(done){
							UWAP.feed.post( {title: "test", message: "test"},
									function(d){
//										console.log(d);
										
										done();
							}, function(err){console.log(err); throw err;}
							);
							
						});
					});
					describe.skip("#respond()", function(){
						it("should return without error", function(done){
							UWAP.feed.post( {title: "testresponse", inresponseto: "5114ec386209a94832000000", message: "testresponse"},
									function(d){
//										console.log(d);
										
										done();
							}, function(err){console.log(err); throw err;}
							);
							
						});
					});
					describe.skip("#read()", function(){
						it("should return without error", function(done){
							UWAP.feed.read( {title: "testresponse"},
									function(d){
//										console.log(d);
										
										done();
							}, function(err){console.log(err); throw err;}
							);
							
						});
					});
					describe.skip("#delete()", function(){
						it("should return without error", function(done){
							UWAP.feed['delete']( createdFeedItem.id,
									function(d){
//										console.log(d);
										
										done();
							}, function(err){console.log(err); throw err;}
							);
							
						});
					});
			 });
			 
			 describe("UWAP.appconfig", function(){
					describe("#list()", function(){
						it.skip("should return a list of applications", function(done){
							UWAP.appconfig.list( 
									function(d){
										console.log(d);
										expect(d).not.to.be(null);
										done();
									}, function(err){console.log(err); throw err;}
								);
						});
					});
					
					describe("#store()", function(){
						it.skip("should return without error", function(done){
							UWAP.appconfig.store({ title: "mochatest"}, 
									function(d){
										console.log(d);
										createdApp = d;
										done();
										
									}, function(err){console.log(err); throw err;}
								);
							
						});
					});
					
					describe("#updateStatus()", function(){
						it.skip("should return without error", function(done){
							UWAP.appconfig.updateStatus( createdApp.id, {title: "mochatest2"}, 
									function(d){
										console.log(d);
										done();
										
									}, function(err){console.log(err); throw err;}
								);
							
						});
					});
					
					describe("#bootstrap()", function(){
						it.skip("should return without error", function(done){
							UWAP.appconfig.bootstrap(createdApp.id, "Bootstrap", 
									function(d){
//										console.log(d);
										done();
										
									}, function(err){console.log(err); throw err;}
								);
							
						});
					});
					
					describe("#updateAuthzHandler()", function(){
						it.skip("should return without error", function(done){
							UWAP.appconfig.updateAuthzHandler(createdApp.id, {title: "mochatest", handler: "oauth2"}, 
									function(d){
//										console.log(d);
										createdAuthzHandler = d;
										done();
										
									}, function(err){console.log(err); throw err;}
								);
							
						});
					});
					
					describe("#deleteAuthzHandler()", function(){
						it.skip("should return without error", function(done){
							UWAP.appconfig.deleteAuthzHandler(createdApp.id, createdAuthzHandler.id, 
									function(d){
//										console.log(d);
										done();
										
									}, function(err){console.log(err); throw err;}
								);
							
						});
					});
					
					describe("#check()", function(){
						it.skip("should return without error", function(done){
							UWAP.appconfig.check(createdApp.id, 
									function(d){
//										console.log(d);
										done();
										
									}, function(err){console.log(err); throw err;}
								);
							
						});
					});
					
					describe("#get()", function(){
						it.skip("should return without error", function(done){
							UWAP.appconfig.get(createdApp.id, 
									function(d){
//										console.log(d);
										done();
										
									}, function(err){console.log(err); throw err;}
								);
							
						});
					});
					
			 });
			 
			 describe('UWAP.applisting', function(){
				  describe("#list()", function(){
					it.skip("should return a list of apps", function(done){
						UWAP.appconfig.list(
								function(d){
//									console.log(d);
									expect(d).not.to.be(null);
									done();
									
								}, function(err){console.log(err); throw err;}
							);
						
					});
				});
			 });
			
			 
			 
		 });
		 
		 describe("Standard Logged-Out Tests", function(){
			 
			 
			 
			 describe("UWAP.auth", function(){
				 
				 describe("#logout()", function(){
					 it("should be logged out", function(done){
						UWAP.auth.logout();
						
						UWAP.auth.check(function(user){
							console.log(user);
							expect(user.userid).to.be(null);
							
						}, function(){done();});
					 });
					
				 	});
//					
////				 describe("#require()", function(){
////					 it("should eventually return user", function(done){
////						UWAP.auth.require(function(user){
////							console.log(user);
////							expect(user.userid).not.to.be(null);
////							done();
////						}); 
////					 });
////					
////				 	});
////				 
				 describe("#check()", function(){
					 it("should be logged out", function(done){
						UWAP.auth.check(function(user){
							console.log(user);
							expect(user.userid).to.be(null);
							
						}, function(){done();}); 
					 });
					
				 	});
 		 
			 describe("UWAP.data", function(){
				 
				 describe("#get()", function(){
					 it("should get VGNett without error or null", function(done){
						UWAP.data.get('http://www.vg.no', {}, function(d){
							expect(d).not.to.be(null);
							done();
						}, function(err){ console.log(err); throw err;}); 
					 });
				 	});
				 
				 
				 	
				}); 
 
				 describe("UWAP.store", function(){
					describe("#save()", function(){
						it.skip("should run the example from docs.uwap.org (with an added comma)", function(done){
							UWAP.store.save(
									{
										"test": "value",
										"size": Math.floor(Math.random()*1000),
										"bool": true,
										"speed": 1.2333,
										"geolocation": {
											"city": "Trondheim",
											"code": "7040"
										}
									}, function() {
										console.log("Successfully stored object.")
										done();
									}, function(err) {
										console.log("Error storing object: " + err.message)
									}
								);
						});
					});
					
					describe("#queryOne()", function(){
						it.skip("should run the example from docs.uwap.org", function(done){
							UWAP.store.queryOne(
									{"bool": true},
									function(res) {
										console.log("Query one returned result:");
										console.log(res);
										res.text = "Modified2";
										console.log("Is about to save entry with id: ", res["_id"]);
										UWAP.store.save(res, function() {
											console.log("Successfully stored modified attribute");
											done();
										});
									}
								);
						});
					});
					
					describe("#queryList()", function(){
						it.skip("should run the example from docs.uwap.org", function(done){
							UWAP.store.queryList(
									{"bool": true},
									function(res) {
										console.log(res);
										done();
									}, function(err) {
										console.log(err);
										throw err;
									}
								);
						});
					});
					
					describe("#remove()", function(){
						it.skip("should run the example from docs.uwap.org", function(done){
							UWAP.store.remove(
									{"bool": true},
									function() {
										console.log("Successfully deleted");
										done();
									}, function(err) {
										console.log("Error:" + err);
										throw err;
									}
								);
						});
					});
				 });
				 
				 
				 describe("UWAP.groups", function(){
						describe("#listMyGroups()", function(){
							it.skip("should not return a list of groups", function(done){
								UWAP.groups.listMyGroups(
										function(d){
											console.log(d);
											expect(d).to.be(null);
										}, function(err){done();}
									);
							});
						});
						describe("#get()", function(){
							it.skip("should not return anything", function(done){
								UWAP.groups.listMyGroups(
										function(d){
											UWAP.groups.get( d[0].id,
													function(d){
														console.log(d);
														
													}, function(err){done();}
											);
										}, function(err){console.log(err); throw err;}
									);
								
							});
						});
						
				 });
				 
				 describe("#checkPassive()", function(){
					 it("should return user logged in again", function(done){
						UWAP.auth.checkPassive(function(user){
							expect(user).not.to.be(null);
							done();
						}, function(){throw err; }); 
					 });
					
				 	});
//				 
				});
//				 describe("UWAP.feed", function(){
//						describe("#upcoming()", function(){
//							it("should return without error", function(done){
//								UWAP.feed.upcoming( {},
//										function(d){
//											console.log(d);
//											done();
//										}, function(err){console.log(err); throw err;}
//									);
//							});
//						});
//				 });
			 });
			
		 describe("Optional Tests", function() {
			 
		 
		 	describe("uwap.data.get()", function(){
			 
				it("should return calendar list with OAuth 2.0 and Google Calendar", function(done){
					
					UWAP.data.get('https://www.googleapis.com/calendar/v3/users/me/calendarList', {handler: 'gcal'}, function(d){
						console.log(d);
						expect(d.kind).to.be("calendar#calendarList");
						done();
					}, function(err){throw err;});
				 	});
			 	});
			 var runner = mocha.run();
		 });
		 
		 
		 
		 
	});
	
	
});