<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<title>Hidden UWAP Messenger</title>

	<script type="text/javascript">

		function run() {
			var msg = <?php echo json_encode($message)  ?>;
			// console.log("Sending message ", msg);

			if (window.parent && window.parent.UWAP && window.parent.UWAP.messenger) {

				
				window.parent.UWAP.messenger.send(msg);


			} else {
				// console.error("ERROR: Could not send to parent frame ");
				// alert("Could not send message to parent frame.");
			}

		}


	</script>
</head>

<body onload="run();">

</body>
</html>