# Format of authorization handlers



## OAuth 2.0


	{
		type	"oauth1"
		request	"https://foodl.org/simplesaml/module.php/oauth/requestToken.php"
		authorize	"https://foodl.org/simplesaml/module.php/oauth/authorize.php"
		access	"https://foodl.org/simplesaml/module.php/oauth/accessToken.php"
		key	"_94d9ff555f02478652f15d1d3916cb07b0c827334f"
		secret	"_9d1f022c154fc4eed55b490e855c01864866f12cde"
	}


	{
		type	"oauth2"
		authorization	"https://www.facebook.com/dialog/oauth"
		token	"https://graph.facebook.com/oauth/access_token"
		tokentransport	"query"
		client_credentials	
		{
			client_id	"254785877865195"
			client_secret	"e506356c1f2e2004cadc3c6d0f686abf"
		}
	}


	{
		type	"token"
		token_hdr	"X-norrs-busbuddy-apikey"
		token_val	"5E7QG6FhDqPRcOFB"
		followRedirects	false
		user	false
		curl	true
	}


	{
		type	"basic"
		client_user	"andreas"
		client_password	"password"
		user	true
	}

	{
		type	"plain"
	}