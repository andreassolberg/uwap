/**
 * An implementation of WebFinger in javascript.
 * This implementation is based upon the work from unhosted.org, but made independently.
 */

 define(
 	['utils/urltool'],
	function (urltool) {

		var webfinger = {};

		webfinger.finger = function(userid, callback) {

		    var candidates = webfinger.userAddress2hostMetas('andreassolberg@gmail.com'); 
		    var acct = webfinger.acctURL(userid);

			console.log('userAddress2hostMetas() ', candidates);
			console.log("Looking up candidate ", candidates[0]);

			webfinger.lookupHostMeta(candidates[0], acct, callback);


		};

		webfinger.acctURL = function(userid) {
			if (userid.substr(0, 5) === 'acct:') {
				return userid;
			}
			return 'acct:' + userid;
		}

		webfinger.userAddress2hostMetas = function (userAddress) {
			var candidates = [];
			var parts = userAddress.toLowerCase().split('@');

			if(parts.length < 2) {
				throw new Error('That is not a user address. There is no @-sign in it');

			} else if(parts.length > 2) {
				throw new Error('That is not a user address. There is more than one @-sign in it');

			} else {
				if(!(/^[\.0-9a-z\-\_]+$/.test(parts[0]))) {
					throw new Error('That is not a user address. There are non-dotalphanumeric symbols before the @-sign: "'+parts[0]+'"');

				} else if(!(/^[\.0-9a-z\-]+$/.test(parts[1]))) {
					throw new Error('That is not a user address. There are non-dotalphanumeric symbols after the @-sign: "'+parts[1]+'"');

				} else {
					// candidates.push('https://' + parts[1] + '/.well-known/host-meta.json');
					candidates.push('https://' + parts[1] + '/.well-known/host-meta');
					// candidates.push('http://' + parts[1] + '/.well-known/host-meta.json');
					candidates.push('http://' + parts[1] + '/.well-known/host-meta');
				}
			}
			return candidates;
		}


		webfinger.parseLRDDresponse = function(data, callback) {
			var res = [];
			$(data).find("Link").each(function(i, item) {
				var no = {};
				no.type = $(item).attr("type");
				no.rel = $(item).attr("rel");
				no.href = $(item).attr("href");
				res.push(no);
			});
			console.log(res);
			callback(res);
		}

		webfinger.findLRDD = function(data) {
		
			var res = $(data).find('Link[rel="lrdd"]').attr('template');
			console.log("result from lrdd parse: ", res);
			return res;
		}

		webfinger.lookupLRDD = function(template, acct, callback) {
			var url = template.split('{uri}').join(acct);
			url = urltool.addQueryParam(url, 'format', 'json');

			UWAP.data.get(url, {handler: "plain", "envelope": true}, function(resp) {
				webfinger.parseLRDDresponse(resp, callback)
			});
		}

		webfinger.lookupHostMeta = function(url, target, callback) {
			var furl = urltool.addQueryParam(url, 'rel', 'lrdd');
			furl = urltool.addQueryParam(furl, 'format', 'json');
			furl = urltool.addQueryParam(furl, 'resource', target);
			UWAP.data.get(furl, {handler: "plain", "envelope": true}, function(data) {
				var template = webfinger.findLRDD(data);
				console.log("Response from Hostmeta lookup: ", template);
				webfinger.lookupLRDD(template, target, callback)
			});
		};

		return webfinger;

	}
);


// 		function fetchXrd(addresses, timeout, cb) {
// 			var firstAddress = addresses.shift();
// 			if(firstAddress) {
// 				platform.ajax({
// 					url: firstAddress,
// 					success: function(data) {
// 						parseAsJrd(data, function(err, obj){
// 							if(err) {
// 								parseAsXrd(data, function(err, obj){
// 									if(err) {
// 										fetchXrd(addresses, timeout, cb);
// 									} else {
// 										cb(null, obj);
// 									}
// 								});
// 							} else {
// 								cb(null, obj);
// 							}
// 						});
// 					},
// 					error: function(data) {
// 						fetchXrd(addresses, timeout, cb);
// 					},
// 					timeout: timeout
// 				});
// 			} else {
// 				cb('could not fetch xrd');
// 			}
// 		}
// 		function parseAsXrd(str, cb) {
// 			platform.parseXml(str, function(err, obj) {
// 				if(err) {
// 					cb(err);
// 				} else {
// 					if(obj && obj.Link) {
// 						var links = {};
// 						if(obj.Link && obj.Link['@']) {//obj.Link is one element
// 							if(obj.Link['@'].rel) {
// 								links[obj.Link['@'].rel]=obj.Link['@'];
// 							}
// 						} else {//obj.Link is an array
// 							for(var i=0; i<obj.Link.length; i++) {
// 								if(obj.Link[i]['@'] && obj.Link[i]['@'].rel) {
// 									links[obj.Link[i]['@'].rel]=obj.Link[i]['@'];
// 								}
// 							}
// 						}
// 						cb(null, links);
// 					} else {
// 						cb('found valid xml but with no Link elements in there');
// 					}
// 				}
// 			});
// 		}
// 		function parseAsJrd(str, cb) {
// 			var obj;
// 			try {
// 				obj = JSON.parse(str);
// 			} catch(e) {
// 				cb('not valid JSON');
// 				return;
// 			}
// 			var links = {};
// 			for(var rel in obj.links) {
// 				//just take the first one of each rel:
// 				if(obj.links[rel].length >= 1) {
// 					links[rel]=obj.links[rel][0];
// 				}
// 			}
// 			cb(null, links);
// 		}
// 		function getStorageInfo(userAddress, options, cb) {
// 			userAddress2hostMetas(userAddress, function(err1, hostMetaAddresses) {
// 				if(err1) {
// 					cb(err);
// 				} else {
// 					fetchXrd(hostMetaAddresses, options.timeout, function(err2, hostMetaLinks) {
// 						if(err2) {
// 							cb('could not fetch host-meta for '+userAddress);
// 						} else {
// 							if(hostMetaLinks['lrdd'] && hostMetaLinks['lrdd'].template) {
// 								var parts = hostMetaLinks['lrdd'].template.split('{uri}');
// 								var lrddAddresses=[parts.join('acct:'+userAddress), parts.join(userAddress)];
// 								fetchXrd(lrddAddresses, options.timeout, function(err4, lrddLinks) {
// 									if(err4) {
// 										cb('could not fetch lrdd for '+userAddress);
// 									} else {
// 										 //FROM:
// 										//{
// 										//  api: 'WebDAV',
// 										//  template: 'http://host/foo/{category}/bar',
// 										//  auth: 'http://host/auth'
// 										//}
// 										//TO:
// 										//{
// 										//  type: 'pds-remotestorage-00#webdav',
// 										//  href: 'http://host/foo/',
// 										//  legacySuffix: '/bar'
// 										//  auth: {
// 										//    type: 'pds-oauth2-00',
// 										//    href: 'http://host/auth'
// 										//  }
// 										//}
// 										if(lrddLinks['remoteStorage'] && lrddLinks['remoteStorage']['auth'] && lrddLinks['remoteStorage']['api'] && lrddLinks['remoteStorage']['template']) {
// 											var storageInfo = {};
// 											if(lrddLinks['remoteStorage']['api'] == 'simple') {
// 												storageInfo['type'] = 'pds-remotestorage-00#simple';
// 											} else if(lrddLinks['remoteStorage']['api'] == 'WebDAV') {
// 												storageInfo['type'] = 'pds-remotestorage-00#webdav';
// 											} else if(lrddLinks['remoteStorage']['api'] == 'CouchDB') {
// 												storageInfo['type'] = 'pds-remotestorage-00#couchdb';
// 											} else {
// 												cb('api not recognized');
// 												return;
// 											}

// 											var templateParts = lrddLinks['remoteStorage']['template'].split('{category}');
// 											if(templateParts[0].substring(templateParts[0].length-1)=='/') {
// 												storageInfo['href'] = templateParts[0].substring(0, templateParts[0].length-1);
// 											} else {
// 												storageInfo['href'] = templateParts[0];
// 											}
// 											if(templateParts.length == 2 && templateParts[1] != '/') {
// 												storageInfo['legacySuffix'] = templateParts[1];
// 											}
// 											storageInfo['auth'] = {
// 												type: 'pds-oauth2-00',
// 												href: lrddLinks['remoteStorage']['auth']
// 											};
// 											cb(null, storageInfo);
// 										} else if(lrddLinks['remotestorage']
// 											&& lrddLinks['remotestorage']['href']
// 											&& lrddLinks['remotestorage']['type']
// 											&& lrddLinks['remotestorage']['links']
// 											&& lrddLinks['remotestorage']['links']['auth']
// 												&& lrddLinks['remotestorage']['links']['auth'][0]//although parseAsJrd takes out the first link of each rel, it leaves nested links in a list
// 												&& lrddLinks['remotestorage']['links']['auth'][0]['href']
// 												&& lrddLinks['remotestorage']['links']['auth'][0]['type']
// 												&& lrddLinks['remotestorage']['links']['auth'][0]['type'] == 'oauth2-ig'
// 												) {
// 											lrddLinks['remotestorage']['auth']= lrddLinks['remotestorage']['links']['auth'][0];
// 											delete lrddLinks['remotestorage']['links'];
// 											cb(null, lrddLinks['remotestorage']);
// 										} else {
// 											cb('could not extract storageInfo from lrdd');
// 										}
// 									}
// 								}); 
// } else {
// 	cb('could not extract lrdd template from host-meta');
// }
// }
// });
// }
// });
// }
// return {
// 	getStorageInfo: getStorageInfo
// }
// });