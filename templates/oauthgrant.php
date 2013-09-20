<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Authorization Required - UWAP</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="description" content="UNINETT WebApp Park" />
		<meta name="author" content="Andreas Åkre Solberg" />

		<style type="text/css">

		</style>

		<script type="text/javascript" src="/_/js/require.js"></script>
		<script type="text/javascript" src="/oauthgrant/main.js"></script>
		
	</head>


<?php

		$scopes = array();
		if (!empty($data['scopes'])) {
			foreach($data['scopes'] AS $scope) {
				$scopes[$scope] = 1;
			}
		}

		// print_r($scopes);
?>



	<body>


		<div class="container" style="margin-top: 4em;" >
			<div class="row">


				<div class="col-md-6" style=" border-right: 3px solid #eee">

					<p><strong><?php echo htmlspecialchars($data["client_name"]); ?></strong> requests the following permissions:</p>


					<div class="panel panel-default">
						<div class="panel-heading">
							<span style="font-size: 150%" class="glyphicon glyphicon-user"></span> Access to information about you
						</div>

						<div class="panel-body" style="">
							<div class="media">
								<a class="pull-left" href="#">
									<img style="width: 64px; height: 64px; border: 1px solid #aaa; border-radius: 3px" 
										src="https://core.uwap.org/api/media/user/<?php echo htmlspecialchars($userdata["a"]); ?>">
								</a>
								<div class="media-body">
									<h4 class="media-heading">
										<!-- <span class="glyphicon glyphicon-user"></span> -->
										<?php echo htmlspecialchars($userdata["name"]); ?>
									</h4>

									<p style="margin:0px">
										<span style="" class="glyphicon glyphicon-user"></span> 
											<?php echo htmlspecialchars($userdata["userid"]); ?>
										<span style="margin-left: .8em" class="glyphicon glyphicon-envelope"></span> 
											<?php echo htmlspecialchars($userdata["mail"]); ?>
									</p>



<?php


/**
 * This section is within the Information about the current user...
 */

	if (!empty($userdata['groups'])) {


		echo '<p style="margin:0px">Member of ' . count($userdata['groups']) . ' groups - <a data-toggle="collapse" data-target="#grouplist" href="#">view list of groups</a></p>';



		echo '<ul id="grouplist" class="collapse" style="margin: 0px">';
		foreach($userdata['groups'] AS $k => $v) {
			echo '<li>' . $v . '</li>';
		}
		echo '</ul>';


	}

?>


								</div><!-- media-body -->
							</div><!-- media -->
						</div><!-- panel body -->
					</div><!-- panel -->






<?php
/*
 * Presenting Long term scope....
 */

if (isset($scopes['feedread']) || isset($scopes['feedwrite'])) {

?>
					<div class="panel panel-default">
						<div class="panel-body" style="">
							<p>
								<span style="font-size: 150%" class="glyphicon glyphicon-align-justify"></span> 

<?php
	if (isset($scopes['feedread']) || isset($scopes['feedwrite'])) {
		echo 'Access to both <span class="label label-success">read</span> and <span class="label label-danger">write</span> to your eduFeed.';
	}	 else if (isset($scopes['feedread'])) {
		echo 'Access to <span class="label label-success">read</span> your eduFeed.';
	} else if (isset($scopes['feedread'])) {	
		echo 'Write access to your eduFeed.';
	}

?>
								
							</p>
						</div>
					</div>

<?php
}
	unset($scopes['feedread']);
	unset($scopes['feedwrite']);

// echo '<pre>'; print_r($scopes); echo '</pre>';
?>







<?php
/*
 * Dealing with API Gatekeeper access
 */


foreach($scopes AS $k => $v) {
	if (preg_match('/^rest_([^_]+($|_))/', $k, $matches)) {
		$apiid = $matches[1];

?>
		<div class="panel panel-default">
			<!-- <div class="panel-heading">Long term access</div> -->
			<div class="panel-body" style="">

				<p>
					<span style="font-size: 150%" class="glyphicon glyphicon-record"></span> 
					<b>API Accesss to <tt><?php echo $apiid; ?></tt></b>. The client may access this API on behalf of you.</p>

			</div>
		</div>


<?php

	}
}
?>









<?php
/*
 * Presenting UWAP Feed scope....
 */

if (isset($scopes['longterm'])) {
	unset($scopes['longterm']);
?>
					<div class="panel panel-default">
						<!-- <div class="panel-heading">Long term access</div> -->
						<div class="panel-body" style="">

							<p>
								<span style="font-size: 150%" class="glyphicon glyphicon-time"></span> 
								<b>Long term access</b>. The client may access your data until you explicitly revoke the access.</p>

						</div>
					</div>

<?php
}
 // echo '<pre>'; print_r($scopes); echo '</pre>';
?>


















				<div>
				<form id="login" method="post" action="<?php echo htmlspecialchars($posturl); ?>">
				<?php
					foreach($postdata AS $name => $value) {
						echo '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />';
					}
				?>
					<fieldset id="actions" style="text-align: right">
						<input tabindex="1" type="submit" id="submit" class="btn btn-lg btn-primary" value="Allow">
						<a tabindex="2" class="btn btn-default" href="#">Reject</a>
					</fieldset>
				</form>
				</div>






				</div><!-- column -->





				<div class="col-md-1">

					<svg xmlns="http://www.w3.org/2000/svg" xmlns:xl="http://www.w3.org/1999/xlink" 
						style="margin-left: -28px; margin-top: 0px" height="165pt" width="70pt" 
						version="1.1" viewBox="154 100 70 165" 
						>
						<!-- <g fill="none" fill-opacity="1" stroke="none" stroke-dasharray="none" stroke-opacity="1"> -->
							<path d="M 163 109 L 215 182.5 L 163 256 Z" fill="#eaeaea"/>
						<!-- </g> -->
					</svg>

				</div>


				<div class="col-md-5">

					<div class="well">

						<div class="media" style="margin: 2em; 0px">
							<a class="pull-left" href="#">
								<img class="media-object" src="https://core.uwap.org/api/media/logo/app/feed" alt="...">
							</a>
							<div class="media-body">

								<h2 class="media-heading"><?php echo htmlspecialchars($data["client_name"]); ?></h2>
								<?php
								if (!empty($data["description"])) {

									echo '<p>' . htmlspecialchars($data["description"]) . '</p>';

								}
								?>
								<p><a target="_blank" href="#">Read more about this application</a></p>

							</div><!-- media body -->
						</div><!-- media -->



					</div><!-- well -->



					<div class="panel panel-default">
						<div class="panel-heading">Application owner</div>
					  
					  <div class="panel-body" style="">
					    
							<div class="media">
								<a class="pull-left" href="#">
									<img style="width: 64px; height: 64px; border: 1px solid #aaa; border-radius: 3px" 
										src="https://core.uwap.org/api/media/user/<?php echo htmlspecialchars($owner["a"]); ?>">
								</a>
								<div class="media-body">
									<h4 class="media-heading">
										<!-- <span class="glyphicon glyphicon-user"></span> -->
										<?php echo htmlspecialchars($owner["name"]); ?>
									</h4>

									
									<p style="margin: 0px">
										<span class="glyphicon glyphicon-envelope"></span> 
										<?php echo htmlspecialchars($owner["mail"]); ?></p>
									<p style="margin: 0px">
										
										<a href="mailto:<?php echo htmlspecialchars($owner["mail"]); ?>">Send e-mail</a></p>


								</div><!-- media body -->
							</div><!--  media -->

					  </div><!-- panel body -->
					</div><!-- panel -->
















			</div><!-- row -->
		</div><!-- container -->





	</body>

</html>





	



