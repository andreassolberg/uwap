# Autoconfiguration widget


In the application, setup an iframe like this:


	<p>Register your application in the configuration widget below in order to continue.</p>
	<iframe id="uwap_autoconnect_widget" src="http://dev.app.bridge.uninett.no/autoconnect.html" 
		style="border-radius: 10px; border: 1px solid #aaa; width: 100%; height: 680px"></iframe>


Setup a window listener for retrieving messages from the widget:

		window.addEventListener("message", function(event) {

			/**
			 * When receving a message that the widget is ready, then send a message back with metadata.
			 */
			if (event.data.msg === 'ready') {

				onReady(event.data.data);

			} else if (event.data.msg === 'appconfig') {

				onRegisterCompleted(event.data.data);

			}


		}, false);


Implement onReady to send initial configuration data to the widget:

		var onReady = function(message) {

			var environment = 'http://dev.app.bridge.uninett.no';
			var metadata = {
				"client_id": "c74e2395-3712-4c53-b488-e0108af48952",
				"redirect_uri": "http://localhost:3000/callback/FeideConnect",
			};
				
			console.log("Received a message from widget to container", event.data);
				// Perform a request for registering client to widget.
				var widget = document.getElementById("uwap_autoconnect_widget").contentWindow;
				widget.postMessage({
					"msg": "metdata", 
					"metadata": metadata
				}, environment);
			});

		} 

Then the user register the app using the widget, and when submitted, your app retrieves the full configuration:

	var onRegisterCompleted = function(message) {

		$("#uwap_autoconnect_widget").hide();

		var meta = {
			'authorization': '<?php echo $uwap["oauth"]["authorization"]; ?>',
			'token':  '<?php echo $uwap["oauth"]["token"]; ?>',
			'userinfo':  '<?php echo $uwap["oauth"]["userinfo"]; ?>',
			'client_id':  message['client_id'],
			'client_secret':  message['client_secret'],
			'redirect_uri':  message['redirect_uri'][0],
		};

		$("#updmetadata").attr('value', JSON.stringify(meta));

		$.ajax({
			type: "POST",
			processData: false,
			dataType: "json",
			mimeType: "text/json",
			url: '/_autoconfigure-api/register',
			data: JSON.stringify({"metadata": meta}),
			success: function(msg) {

				location.reload(true);	
			}
		});

	}

The example above posts the retrieved metadata to a local API for storage.


