# Getting started developing webapps at UNINETT WebApp Park



## The UWAP API



### Authentication API


To check if the user is authenticated.

	UWAP.auth.check(loggedin, notloggedin);

To ensure the user is authenticated. The user will be redirected to perform login, if the user is not logged in already.

	UWAP.auth.require(loggedin);

The `loggedin(user)` callback, sends a user object as a parameter.

The `notloggedin()` callback is used when the user is not loggedin, does not send any parameters.


### Data API

The data API can be used to retrieve HTTP REST data from a remote source

	UWAP.data.get(url, options, callback, errorcallback);

The options object MUST contain a `handler`:

	{handler: "plain"}

The options object MAY contain a property `xml`, if set to `true`, expects the endpoint to return XML data, and this will be converted to JSON before return.

The `handler` point to a configured data handler in the App configuration. A data handler can be a configured OAuth client, a set of HTTP Baisc Auth credentials or similar.

When data is successfully retrieved the `callback` is called.

When error occurs the `errorcallback` is called.


### Storage API





