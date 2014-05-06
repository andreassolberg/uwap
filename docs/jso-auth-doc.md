# JSO Authentication



`UWAP.auth.require` - force authenticaiton.

1. performs a GET /api/userinfo



`UWAP.auth.check` - check but do not force authentication.

1. first UWAP.utils.hasToken() checks if token exists, if not return false.
Then performs a GET /api/userinfo when it knows the token.
On this request a special option `"jso_allowia": false` is sent along with the request to make sure that it will not redirect for authentication.

`UWAP.auth.logout` wipes the tokens.

`UWAP.auth.checkPassive` first 

