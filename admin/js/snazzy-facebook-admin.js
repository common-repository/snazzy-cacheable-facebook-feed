jQuery(document).ready(function($){
		var pluginURI= $("#pluginDir").text();
		var fullURI= pluginURI + "/functions/save-to-cache.php";
		var feedSize = $("#feedSize").text();
		var feedLimit = $("#feedLimit").text();
		var facebookId = $("#facebookId").text();
		jQuery.ajax({
			method: "POST",
			beforeSend: console.log("sending"),
			url: fullURI,
			data: { feedSize: feedSize, feedLimit:feedLimit, facebookId:facebookId,  pluginURL: pluginURI}
		})
		.done(function( msg ) {
			console.log( "Data Saved: " + msg );
		});
	
});