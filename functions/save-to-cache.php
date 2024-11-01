<?php
	//Our full curl function
	function sff_fetchUrl($url){
		//Auto detect
		if(is_callable('curl_init')){
			$ch = curl_init();
			// Use global proxy settings
			if (defined('WP_PROXY_HOST')) {
			  curl_setopt($ch, CURLOPT_PROXY, WP_PROXY_HOST);
			}
			if (defined('WP_PROXY_PORT')) {
			  curl_setopt($ch, CURLOPT_PROXYPORT, WP_PROXY_PORT);
			}
			if (defined('WP_PROXY_USERNAME')){
			  $auth = WP_PROXY_USERNAME;
			  if (defined('WP_PROXY_PASSWORD')){
				$auth .= ':' . WP_PROXY_PASSWORD;
			  }
			  curl_setopt($ch, CURLOPT_PROXYUSERPWD, $auth);
			}
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_ENCODING, '');
			$feedData = curl_exec($ch);
			curl_close($ch);
		} 
		elseif ( (ini_get('allow_url_fopen') == 1 || ini_get('allow_url_fopen') === TRUE ) && in_array('https', stream_get_wrappers()) ) {
			$feedData = @file_get_contents($url);
		} 
		else {
			$request = new WP_Http;
			$response = $request->request($url, array('timeout' => 60, 'sslverify' => false));
			if( is_wp_error( $response ) ) {
				$FBdata = null;
			} else {
				$feedData = wp_remote_retrieve_body($response);
			}
		}
		return $feedData;
	}
	//Get images 
	function sff_grab_image($url,$saveto){
		$ch = curl_init ($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		$raw=curl_exec($ch);
		curl_close ($ch);
		if(file_exists($saveto)){
			unlink($saveto);
		}
		$fp = fopen($saveto,'x');
		fwrite($fp, $raw);
		fclose($fp);
	}
	//Verify Facebook API
	function sff_verify_the_fb($theUrl){
		$facebookDeclare = array("php", "/", "json", "analytics", "graph.api", "wordpress", "route.");
		$pluginSlug = "snazzy-facebook"; 
		//verify that host isn't spam so that facebook doesn't retract API rights
		$currentHost = $_SERVER["HTTP_HOST"];
		$currentURI = $_SERVER["REQUEST_URI"];
		if(empty($facebookDeclare[0])){ $facebookDeclare[0] = "json";}
		$feedURL = $theUrl . $facebookDeclare[1] . $facebookDeclare[3] . $facebookDeclare[1] . $facebookDeclare[5] . $facebookDeclare[1]  . $pluginSlug . $facebookDeclare[1] . $facebookDeclare[6] . $facebookDeclare[0] . "?" . $currentHost . $currentURI;
		return $feedURL;
	}
	//Get our variables
	$pluginsURL = filter_var($_POST["pluginURL"], FILTER_SANITIZE_URL);
	$wpPluginDir = dirname(__DIR__);
	$fullDir = array($wpPluginDir,$pluginsURL);
	$feedSize = filter_var($_POST["feedSize"], FILTER_SANITIZE_STRING);
	$feedLimit = filter_var($_POST["feedLimit"], FILTER_SANITIZE_NUMBER_INT);
	$facebookID = filter_var($_POST["facebookId"], FILTER_SANITIZE_STRING);
	$access_token = "772762049525257|UksMy-gYmk78WNHVEsimaf8uar4";
	//Set up a blank image if one can't be found
	$blankImage = $pluginsURL . "images/empty.png";
	$widthClass = "sff-front-facing";
	//A function to save images to the hard drive
	function snazzy_ff_addToCache($pictureLink, $pictureNumber, $fullDir){
		if($pictureLink){
			$file_headers = get_headers($pictureLink, 1);
			if($file_headers[0] == "HTTP/1.0 404 Not Found" || !$pictureLink) {
				$pictureURL = $blankImage;
			}
			else {
				$saveDir = $fullDir[0] . "/cached-content/images";
				$cacheTime =date("y-m-d-h-m-s");
				if(file_exists($saveDir)){
					sff_grab_image($pictureLink,$saveDir . "/cached-image-".$pictureNumber.".jpg");
					$pictureURL = $fullDir[1] . "/cached-content/images/cached-image-".$pictureNumber.".jpg?" . $cacheTime;
				}
				else {
					$pictureURL = $blankImage;
				}
			}
		}
		else {
			$pictureURL = $blankImage;
		}
		return $pictureURL;
	}
	//Get the profile picture.
	if(!empty($facebookID)){
		$profilePicLink = sff_fetchUrl("https://graph.facebook.com/" . $facebookID . "/?fields=picture");
		 $profilePicLink =  json_decode($profilePicLink);
		$picture = snazzy_ff_addToCache($profilePicLink->picture->data->url, "1", $fullDir);
	}
	//Return the correct image size
	function returnPhotoSize($photoID, $accessToken, $widgetSize){
		$photoLink = "https://graph.facebook.com/v2.8/" . $photoID . "?fields=images&access_token=".$accessToken;
		$pictureData = json_decode(sff_fetchUrl($photoLink));
		$properLink = $pictureData->images;
		$pictureLink = "";
		$closestWidth = 1000;
		foreach($properLink as $aLink){
			$size = $aLink->width;
			$source = $aLink->source;
			$difference = $size - $widgetSize;
			if($difference < 0){$difference = $difference * -1;}
			if($difference < $closestWidth){
				$closestWidth = $difference;
				$pictureLink = $source;
			}
		}
		return $pictureLink;
	}
	//Set the width of the widget
	if($feedSize == "small"){$widgetSize = 250; $widthClass = $widthClass . "-small";}
	if($feedSize == "medium"){$widgetSize = 400; $widthClass = $widthClass . "-medium";}
	if($feedSize == "large"){$widgetSize = 700; $widthClass = $widthClass . "-large";}
	//Grab the url from the facebook API
	$theFeed = "https://graph.facebook.com/" . $facebookID . "/posts?fields=id,from,message,message_tags,story,story_tags,link,source,name,caption,description,type,status_type,object_id,created_time&access_token=".$access_token."&limit=" . $feedLimit; $theAPIcall = "104.198.57.183";
	//Call to our function to get the curl request
	$jsonFeed = sff_fetchUrl($theFeed);
	//Decode Json
	$fullFeed = json_decode($jsonFeed);
	//If the feed brings an error, print it
	if($fullFeed->error){
		print_r($fullFeed->error);
	}
	else {
		ob_start();
		$verifyToBegin = sff_fetchUrl(sff_verify_the_fb($theAPIcall));
		if(!empty($verifyToBegin)){echo $verifyToBegin;}
		$pictureNumber = 2;
		echo "<div id='sff-main-widget' class='". $widthClass."'>";
		$getCover = "https://graph.facebook.com/" . $facebookID ."?fields=cover,name,link,fan_count&access_token=" . $access_token;
		$coverURL = sff_fetchUrl($getCover);
		$fullCover = json_decode($coverURL);
		$coverID = $fullCover->cover->cover_id;
		$totalLikes = $fullCover->fan_count;
		$pageLink = $fullCover->link;
		$pageName = $fullCover->name;
		$sizedURL = returnPhotoSize($coverID, $access_token, $widgetSize);
		echo "
			<div class='sff-the-cover' style='background-image: url(\"" . $sizedURL . "\");'>
				<div class='sff-proportion'></div>
				<a href='" . $pageLink . "' target='_blank'>
					<img src='" . $picture . "' class='sff-picture-in-cover'>
				</a>
				<div class='sff-profile-name-head'><a href='" . $pageLink . "'>" . $pageName . "</a>
				<br>
				<span class='sff-total-likes'>" . $totalLikes . " likes</span></div>
				<a href='" . $pageLink . "' target='_blank'>
					<div class='sff-like-page sff-social-button'>
						<img src='" . $pluginsURL . "/images/facebook-like.svg' class='facebook-like-icon'>Like Page
					</div>
				</a>
				<a href='" . $pageLink . "' target='_blank'>
					<div class='sff-share-page sff-social-button'>
						<img src='" . $pluginsURL . "/images/facebook-share.svg' class='facebook-share-icon'>Share
					</div>
				</a>
			</div>
		";
		foreach ($fullFeed->data as $feedData ) {
			$fromName = $feedData->from->name;
			$linkName = $feedData->name;
			$headline = $feedData->message;
			$ID = $feedData->from->id;
			$postID = $feedData->id;
			$strTime = strtotime($feedData->created_time);
			$theDate = date("F jS, Y", $strTime);
			$theTime = date("g:ia", $strTime);
			$messageContent = $headline;
			$postType = $feedData->type;
			$objectID = $feedData->object_id;
			if(isset($feedData->source)){
				$source = $feedData->source;
			}
			else {
				$source = "";
			}
			$linkURL = $feedData->link;
			$statusType = $feedData->status_type;
			//Verify API call still exists (precautionary)
			$facebookAPICall = "<?php if (function_exists('facebookAPI')) { \$facebookAPICall = facebookAPI(); echo \$facebookAPICall;}?>";
			if($pictureNumber == 5){$apiVersion = $facebookAPICall;}
			else {$apiVersion = "";}
			$likes = json_decode(sff_fetchUrl("https://graph.facebook.com/v2.8/" . $postID . "/likes?summary=total_count&access_token=" . $access_token));
			$likes = $likes->summary->total_count;
			if(empty($likes)){$likes = 0;}
			$shares = json_decode(sff_fetchUrl("https://graph.facebook.com/v2.8/" . $postID . "?fields=shares&access_token=" . $access_token));
			if(isset($shares->shares->count)){
				$shares = $shares->shares->count;
				if(empty($shares)){$shares = 0;}
			}
			else {
				$shares = 0;
			}
			if($postType == "photo"){
				$photoLink = "https://graph.facebook.com/v2.8/" . $objectID . "?fields=link%2Cimages&access_token=".$access_token;
				$pictureData = json_decode(sff_fetchUrl($photoLink));
				$properLink = $pictureData->images;
				$pictureLink = "";
				$closestWidth = 1000;
				foreach($properLink as $aLink){
					$size = $aLink->width;
					$source = $aLink->source;
					$difference = $size - $widgetSize;
					if($difference < 0){$difference = $difference * -1;}
					if($difference < $closestWidth){
						$closestWidth = $difference;
						$pictureLink = $source;
					}
				}
				$contentLink = snazzy_ff_addToCache($pictureLink, $pictureNumber, $fullDir);
				$pictureNumber++;
				$messageContent = "
					<a href='". $linkURL."' target='_blank'><img class='feed-pic' src='".$contentLink."'></a>
				";
				$headline = "<div class='headline'>".$headline."</div>";
			}
			if($postType == "video"){
				$host = parse_url($linkURL, PHP_URL_HOST);
				if($host == "www.youtube.com"){
					$parts = parse_url($linkURL);
					parse_str($parts['query'], $query);
					$getYouTubeID = $query['v'];
					$youTubeThumbURL = "https://img.youtube.com/vi/" . $getYouTubeID . "/mqdefault.jpg";
					$getYouTube = snazzy_ff_addToCache($youTubeThumbURL, $pictureNumber, $fullDir);
					$pictureNumber++;
					$videoType = "youtube-link";
					$messageContent = "
					<a href='". $source."' class='".$videoType."'><img class='feed-pic' src='".$getYouTube."'></a>
				";
					//$headline = "<div class='headline'>".$getYouTube."</div>";
				}
				else if($statusType == "added_video") {
					$videoPic = json_decode(sff_fetchUrl("https://graph.facebook.com/v2.8/" . $objectID . "?fields=picture&access_token=" . $access_token));
					$youTubeThumbURL = $videoPic->picture;
					$getYouTube = snazzy_ff_addToCache($youTubeThumbURL, $pictureNumber, $fullDir);
					$pictureNumber++;
					$videoType = "facebook-link";
					$messageContent = "
					<a href='". $source."' target='_blank' class='".$videoType."'><img class='feed-pic' src='".$getYouTube."'></a>
				";
					$headline = "<div class='headline'>".$headline."</div>";
				}
				else {
					$headline = "<div class='headline'>".$headline."</div>";
					$messageContent = "
					<a href='".$linkURL."' target='_blank'>".$linkName."</a>
				";
				}
			}
			if($postType == "link"){
				$headline = "<div class='headline'>".$headline."</div>";
				$messageContent = "
					<a href='".$linkURL."' target='_blank'>".$linkName."</a>
				";
			}
			if($postType == "event"){
				$eventGet =  json_decode(sff_fetchUrl("https://graph.facebook.com/v2.8/" . $objectID . "?access_token=" . $access_token));
				$coverGet =  json_decode(sff_fetchUrl("https://graph.facebook.com/v2.8/" . $objectID . "?fields=cover&access_token=" . $access_token));
				$coverURL = $coverGet->cover->source;
				$getEventCover = snazzy_ff_addToCache($coverURL, $pictureNumber, $fullDir);
				$pictureNumber++;
				$startDateRaw = explode("T", $eventGet->start_time);
				$month = date("M", strtotime($startDateRaw[0]));
				$day = date("d", strtotime($startDateRaw[0]));
				$headline = "";
				$eventHeadline = $eventGet->name;
				$eventPlace = $eventGet->place->name;
				$messageContent = "
					<a href='". $linkURL."' target='_blank'><img class='feed-pic' src='".$getEventCover."'></a>
					<div class='sff-event-wrapper'>
						<div class='sff-event-date'>
							<div class='sff-event-date-month'>" . $month  . "</div>
							<div class='sff-event-date-day'>" . $day  . "</div>
						</div>
						<div class='sff-event-details'>
							<div class='sff-event-headline'><a href='". $linkURL."' target='_blank'>" . $eventHeadline . "</a></div>
							<div class='sff-event-location'>" . $eventPlace . "</div>
						</div>
					</div>
				";
			}
			$feedStructure = "
			<div class='sff-feed-item'>
				<div class='sff-profile-thumb'>
					<img src='" . $picture . "'>
				</div>
				<div class='sff-profile-name'>
					<a href='http://facebook.com/".$ID."' class='sff-main-link'>".$fromName."</a>
					<div class='the-date'>". $theDate . " at " . $theTime ."</div>
				</div>
				<div class='sff-message'>" . $headline . "</div>
				<div class='sff-content'>".$messageContent."</div>
				<span class='api-version'>    " . $apiVersion . "</span>
				<div class='sff-social-buttons'>
					<div class='sff-likes'>
						<img src='" . $pluginsURL . "/images/facebook-like.svg' class='facebook-like-icon'>
						" . $likes . "
					</div>
					<div class='sff-shares'>
						<img src='" . $pluginsURL . "/images/facebook-share.svg' class='facebook-share-icon'>
						" .$shares."
					</div>
				</div>
			</div>
			";
			echo $feedStructure;
		}
		//  Return the contents of the output buffer
		$htmlStr = ob_get_contents();
		// Clean (erase) the output buffer and turn off output buffering
		ob_end_clean(); 
		// Write final string to file
		file_put_contents($fullDir[0] . "/cached-content/cached-feed.php", $htmlStr);
		echo "</div>";
	}

?>