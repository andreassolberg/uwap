var embed = '<iframe src="http://unibar.uwap.org/#' + encodeURIComponent(window.location.href) + '" ' +
	'style="overflow: none; border: none; padding: 0px; margin: 0px; width: 100%; height: 40px; border-bottom: 2px solid #222; z-index: 10" ></iframe>';
document.write(embed);
$(document).ready(function() {
	$("img#ulogo").hide();	
});
