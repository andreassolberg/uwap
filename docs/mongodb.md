# mongodb


Setting up indexes to expire temporary objects. Requires MongoDB >= 2.6.X.


Wipe codes one hour after they are issued, and access tokens 24 hours after they are expired.

	db['oauth2-server-codes'].ensureIndex({"issued": 1}, { expireAfterSeconds: 3600 });
	db['oauth2-server-tokens'].ensureIndex({"validuntil": 1}, { expireAfterSeconds: 43200 });

