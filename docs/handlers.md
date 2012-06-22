# Format of authorization handlers



## OAuth 2.0



OAuth 2.0

	{
		type	"oauth2"
		authorization	"https://www.facebook.com/dialog/oauth"
		token	"https://graph.facebook.com/oauth/access_token"
		tokentransport	"query"
		client_id	"254785877865195"
		client_secret	"e506356c1f2e2004cadc3c6d0f686abf"
	}

## OAuth 1.0

	{
		type	"oauth1"
		request	"https://foodl.org/simplesaml/module.php/oauth/requestToken.php"
		authorize	"https://foodl.org/simplesaml/module.php/oauth/authorize.php"
		access	"https://foodl.org/simplesaml/module.php/oauth/accessToken.php"
		client_id	"_94d9ff555f02478652f15d1d3916cb07b0c827334f"
		client_secret	"_9d1f022c154fc4eed55b490e855c01864866f12cde"
	}

TODO key and secret is updated as of June 2012.



## Custom header

Custom header authentication, for client authentication.

	{
		type	"token"
		token_hdr	"X-norrs-busbuddy-apikey"
		token_val	"5E7QG6FhDqPRcOFB"
		followRedirects	false
		user	false
		curl	true
	}


## Basic HTTP Authentication

Basic HTTP authentication. Authenticating client only.

	{
		type	"basic"
		client_user	"andreas"
		client_password	"password"
		user	true
	}

