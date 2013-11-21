<?php


class Mailer {


	var $adr;
	var $body;

	protected $user;

	function __construct($adr, $body = null, User $user) {
		$this->adr = $adr;
		$this->body = $body;
		$this->user = $user;
	}


	function getHTML() {
		$body = $this->getBody($this->body);
		return $body;
	}

	function send() {

		$from = 'notifications@uwap.org';
		$subject = 'UNINETT Activitystream Updates';
		// $headers = 'From: notifications@uwap.org' . "\r\n" .
		// 	'Reply-To: andreas.solberg@uninett.no' . "\r\n" .
		// 	'X-Mailer: PHP/' . phpversion();

		// mail($this->adr, $subject, $this->getBody($this->body), $headers);


		$body = $this->getBody($this->body);
		$bodyText = strip_tags(html_entity_decode($this->body));

		$message = Swift_Message::newInstance();
		$message->setSubject($subject);
		$message->setFrom($from);
		$message->setTo($this->adr);
		$message->setBody(strip_tags(html_entity_decode($this->body)));
		$message->addPart( $body, 'text/html');
		
		// if (!empty($attach)) {
		// 	foreach($attach AS $a) {
		// 		$na = Swift_Attachment::newInstance($a['data'], $a['file'], $a['type']);
		// 		$message->attach($na);
		// 	}
		// }
		
		
		//$transport = Swift_MailTransport::newInstance();
		//Sendmail
		$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
		
		$mailer = Swift_Mailer::newInstance($transport);
		$mailer->send($message);







	}


	public function getBody() {

		$baseurl = GlobalConfig::getBaseURL();
		$feedurl = GlobalConfig::getBaseURL('feed');
		// echo "BASE URL " . $baseurl; exit;

		return '<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
		<head xml:lang="en">

			<meta charset="utf-8" />	
		<link rel="stylesheet" href="' . $baseurl . '_/bootstrap3/css/bootstrap.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="' . $feedurl . 'css/feed.css" rel="stylesheet" />

		<style type="text/css">

div.feedcontainer .basicItem .footer span {
	margin-right: 2em;
}	



div.feedcontainer .basicItem p { margin: 0px; padding: 0px; }
div.feedcontainer .basicItem p.articleParagraph {
	margin-top: .4em;
}

div.feedcontainer .basicItem {
	padding: 8px 10px 8px 70px;
	min-height: 64px;
	

	-webkit-box-shadow: rgba(255, 255, 255, 0.796875) 0px 1px 0px 0px inset;
	background-color: 
		rgb(242, 242, 242);
		background-image: -webkit-linear-gradient(top, 
		rgb(246, 246, 246), 
		rgb(238, 238, 238));
	background-repeat: repeat-x;
	border-bottom-color: 
		rgb(212, 212, 212);
	border-bottom-left-radius: 0px;
	border-bottom-right-radius: 0px;
	border-bottom-style: solid;
	border-bottom-width: 1px;
	border-top-left-radius: 5px;
	border-top-right-radius: 5px;
	box-shadow: rgba(255, 255, 255, 0.796875) 0px 1px 0px 0px inset;
	color: 
		rgb(51, 51, 51);

}
div.feedcontainer .basicItem.promoted {
	background: #fff !important;
}
div.feedcontainer .basicItem:last-child {
	border-bottom: none;
}
div.feedcontainer .basicItem .footer {
	color: #777;
	border-top: 1px solid rgba(255, 255, 255, 0.2);
}


div.feedcontainer .basicItem .profileimg {
	float: left;
	margin-left: -64px;

}
div.feedcontainer .item .profileimg {
	max-width: 48px;	
}

div.feedcontainer .basicItem img.thumb {
	max-width: 350px;
	
}

img.thumb {
/*	max-width: 200px;*/
	border: 1px solid #444;
}













		/* ------ Buttons  ----- */



		/* --- links --- */

		a, a:link, a:visited, a:active {
		    color: #633;
		    text-decoration: none;
		}
		a:hover{
		    text-decoration: underline;
		}
		a.lesmer {
		    float: right;
		    margin: 1em;
		}

		span.grey {
			color: #aaa;
		}


		html{
		    height: 100%;

		/*    font-family: Arial, Verdana, sans-serif;*/
		}
		body{
		    height: 100%;
		}
		p {
		    margin-top:     10px;
		    margin-bottom:  10px;
		}


		table {
			border-collapse:collapse;
			border-spacing:0;
			margin: .6em;
		}

		td {
			border: 1px solid #ccc;
		}
		td,th {
			border: 1px solid #aaa;
		/*	text-align: center; */
		}
		th {
			background: #dda;
			padding: .1em 1em .1em 1em;

		}

		dt {
			font-size: 105%;
			color: #600;
			font-weight: bold;
		}
		dd p {
			margin: 0px 1em .1em 0px;
		}


		/* --- General --- */


		body {
			margin: 0px;
			padding: 0px;
			font-family: Helvetica, Arial, sans-serif;
		}
		p {
		/*	margin: 0px;
			padding: 0px;*/
		}

		div#content {
			/* padding: 1em; */
		}
		div#content h1 {
			margin-top: 0px;
		}
		
		hr {
			height: 0px; color: #ccc; 
		}


		/*  --- Header ---  */


		#header {
		    z-index: 0;
		    background-color: #f00;
		}

		#header #logo {
			color: #fff;
			font-family: "Verdana", "sans-serif";
			font-weight: bold;
			letter-spacing: -0.12em;
			text-shadow: 0px 2px 0px #900;
			font-size: 30px;
		/*	position: absolute;
			top: 2px; left: 2px; */
			z-index: 10;
		}
		#header #version {
			font-weight: normal;
			letter-spacing: 0.1em;
			font-size: x-small;
			text-shadow: 0px 1px 0px #900;
		}
		#header #logo #news, #header #logo #mailinglist {
			font-weight: normal;
			letter-spacing: 0em;
		}


		/* --- headerbar --- */

		#headerbar {
		/*	position: absolute;
			top: 42px;
			width: 100%;

			*/
			background: #eee;
			border-top: 1px solid #ccc;
			border-bottom: 1px solid #ccc;
			margin: 3px 0px 0px 0px; 
			padding: 0px 0px 0px 0px;
			z-index: 3;
		}
		#headerbar #breadcrumb {
			float: left; 
			margin: 9px 1em;

		}




		/*  --- Footer ---  */

		#footer {
		/*	width: 100%;*/
			clear: both;
			border-top: 1px solid #ccc;
			text-align: center;
			margin-top: 1em; 
			padding: 0px 0px 0px 0px;
			z-index: 1;
			color: #888;
		}




		</style>

			<title>Feide Connect Activitystream</title> 

		</head>
		<body>





		<!-- Grey header bar below -->
		<div id="headerbar" style="clear: both">
		<p id="breadcrumb">Important updates on <a href="https://feed.uwap.org/">Feide Connect Activitystream</a> for ' . $this->user->get('name', 'noname') . '</p>
		<p style="height: 0px; clear: both"></p>
		</div><!-- /#headerbar -->



		<div id="content">

				' . $this->body . '

		</div><!-- /#content -->

		<div style="padding: 1px 3px" id="footer">
			You have been added to the <a href="https://groups.uwap.org/#!/group/uwap:grp-ah:7ea1c555-583c-4a1f-9ae2-1273b0c66ebc">Feide Connect early adopter team</a>. 
			Please contact Andreas Solberg for questions regarding this privilege.
		</div><!-- /#footer -->


		</body>
		</html>';
	}


	function setBody($body) {
		$this->body = $body;
	}



// 	function setNotifications($notifications) {

// 		$str = '';
// 		foreach($notifications AS $n) {
// 			$str .= '  - ' . $n['summary'] . "\n";
// 		}

// 		$this->body = 'Important updates waits for you on UNINETT Feeds.

// Here is a list of recent updates:

// ' . $str . '

// View these messages at: https://feed.uwap.org#!/


// To set your mail notifications preferences, please visit:
// 	https://feed.uwap.org/#!/usersettings

// UWAP is an experimental service. Contact andreas@uninett.no for questions or 
// comments regarding this service.

// Contact andreas@uninett.no if you want to be removed from the list of test users
// that receives status updats.';

// 	}


}