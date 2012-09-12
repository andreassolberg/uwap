<!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<title>Request for access</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Le styles -->
	<link href="/bootstrap/css/bootstrap.css" rel="stylesheet">
	<style>
	  body {
		padding-top: 100px;
	  }
	  div.container {
		max-width: 550px;
	  }

	</style>
	<link href="/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

  </head>

  <body>

	

	<div class="container">

		<form id="login" method="post" action="<?php echo htmlspecialchars($posturl); ?>">

		<?php
			foreach($postdata AS $name => $value) {
				echo '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />';
			}
		?>


		<h1>Request for access</h1>

		<p>The application <strong><?php echo htmlspecialchars($data["client_name"]); ?></strong> is asking for permissions to the following:</p>

		<hr />

		<ul>

			<li>Access to basic personal information<br />
				<dl style="margin: 0px; color: #999">
					<dt>Your full name</dt>
					<dd><?php echo htmlspecialchars($userdata["name"]); ?></dd>
					<dt>UserID</dt>
					<dd><?php echo htmlspecialchars($userdata["userid"]); ?></dd>
				</dl>
			</li>

			<?php
									
			foreach($permissions AS $perm) {
				echo '<li>' . htmlspecialchars($perm) . '</li>';
			}

			?>
		</ul>



		<p style="text-align: right">Owner of the application <!-- <?php echo htmlspecialchars($data["client_name"]); ?> -->is: <br />
		   <!-- <a href="mailto:<?php echo htmlspecialchars($data["owner"]["email"]); ?>">-->
				<span class="label ">
					<i class="icon icon-user icon-white"></i>
					<?php echo htmlspecialchars($data["owner"]["displayName"]); ?>
				</span>  <br />
				<a tabindex="4" style="margin-top: 3px" class="btn btn-mini" href="mailto:<?php echo htmlspecialchars($data["owner"]["email"]); ?>">Send e-mail</a>
			<!-- </a> -->
		</p>

		<fieldset id="actions">
			<input tabindex="1" type="submit" id="submit" class="btn btn-large btn-primary" value="OK, I accept">
			<a tabindex="2" class="btn" href="#">Deny access</a>
			<a tabindex="3" target="_blank" class="btn" href="#">Read more about this app</a>
		</fieldset>

		</form>

	</div> <!-- /container -->

	<!-- Le javascript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster 

	<script src="/bootstrap/js/bootstrap-transition.js"></script>
	<script src="/bootstrap/js/bootstrap-alert.js"></script>
	<script src="/bootstrap/js/bootstrap-modal.js"></script>
	<script src="/bootstrap/js/bootstrap-dropdown.js"></script>
	<script src="/bootstrap/js/bootstrap-scrollspy.js"></script>
	<script src="/bootstrap/js/bootstrap-tab.js"></script>
	<script src="/bootstrap/js/bootstrap-tooltip.js"></script>
	<script src="/bootstrap/js/bootstrap-popover.js"></script>
	<script src="/bootstrap/js/bootstrap-button.js"></script>
	<script src="/bootstrap/js/bootstrap-collapse.js"></script>
	<script src="/bootstrap/js/bootstrap-carousel.js"></script>
	<script src="/bootstrap/js/bootstrap-typeahead.js"></script>
-->
		<!-- JQuery hosted by Google -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
  </body>
</html>


