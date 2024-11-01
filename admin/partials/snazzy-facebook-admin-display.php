<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       lucashitch.com
 * @since      1.0.0
 *
 * @package    Snazzy_Cacheable_Facebook_Feed
 * @subpackage Snazzy_Cacheable_Facebook_Feed/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->



<?php
/**
*
* admin/partials/wp-cbf-admin-display.php - Don't add this comment
*
**/

//Get JSON object of feed data
function snazzy_ff_fetchUrl($url){

    //Style options
    $options = get_option('cff_style_settings');
    isset( $options['cff_request_method'] ) ? $cff_request_method = $options['cff_request_method'] : $cff_request_method = 'auto';

    if($cff_request_method == '1'){
        //Use cURL
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
    } else if($cff_request_method == '2') {
        //Use file_get_contents
        if ( (ini_get('allow_url_fopen') == 1 || ini_get('allow_url_fopen') === TRUE ) && in_array('https', stream_get_wrappers()) ){
            $feedData = @file_get_contents($url);
        }
    } else if($cff_request_method == '3'){
        //Use the WP HTTP API
        $request = new WP_Http;
        $response = $request->request($url, array('timeout' => 60, 'sslverify' => false));
        if( is_wp_error( $response ) ) {
            //Don't display an error, just use the Server config Error Reference message
            $FBdata = null;
        } else {
            $feedData = wp_remote_retrieve_body($response);
        }
    } else {
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
        } elseif ( (ini_get('allow_url_fopen') == 1 || ini_get('allow_url_fopen') === TRUE ) && in_array('https', stream_get_wrappers()) ) {
            $feedData = @file_get_contents($url);
        } else {
            $request = new WP_Http;
            $response = $request->request($url, array('timeout' => 60, 'sslverify' => false));
            if( is_wp_error( $response ) ) {
                $FBdata = null;
            } else {
                $feedData = wp_remote_retrieve_body($response);
            }
        }
    }
    
    return $feedData;
}

$access_token_array = array(
	'772762049525257|UksMy-gYmk78WNHVEsimaf8uar4',
	'1611234219197161|PenH1iYmf3CShpuWiLMrP6_0mro',
	'842457575860455|MA2WQAK6MO22mYlD1vAfQmY-jNQ',
	'1598576770461963|t3KRNHf1490G8qEopdGoUiMPJ7I',
	'1774415812787078|3yGpMpgbH-Nte9YHCfVIQ59RIt8',
	'762305090538008|KmVsImjHmaJIPTpII9HyOif3yD0',
	'1741187749472232|b1ZfgQ2OSQZzsQN1lqLn4vjrQV4',
	'1748310315388907|AMSWRHgAoChtXepfsWU0OxKfVbQ',
	'1721409114785415|4dIAXp4_utfqkAJS-9X4OXB6GR4',
	'1609030662756868|nCKsZPN4cI-GsIJsi0DESGGtSgw',
	'1590773627916704|EbgBWG45AVQZdNrwsAnTl_-CW_A',
	'227652200958404|AzHtmm3B080elswwLKJrRCKYpGg',
	'1176842909001332|YIQehZhGPWxqkvmiFn4Klt1PA4U',
	'217933725249790|h4YSEYX71EO_2el93hiT47uyf5g',
	'823681761071502|0oAyJYz-MO-jgr8rI3ftrEcBRiQ'
);

$access_token = $access_token_array[rand(0, 14)];

?>

<div class="wrap">

   <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <form method="post" name="cleanup_options" action="options.php" class="sfacebook-form">

    <?php
        //Grab all options
        $options = get_option($this->plugin_name);

        // Cleanup
        $cleanup = $options['cleanup'];
        $facebookID = $options['sfacebook-id'];
         if(!$facebookID){$facebookID = "facebook";}
        $feedLimit = $options['sfeed-limit'];
        if(!$feedLimit){$feedLimit = "7";}
        $feedSize = $options['sfeed-size'];
        $cacheLimit = $options['sfeed-cache-expiration'];
        $pluginName = $this->plugin_name;
        ?>
        <div style="display:none;">
		<div id="pluginDir"><?php echo plugin_dir_url( dirname(dirname(__FILE__))); ?></div>
		<div id="fullDir"> <?php echo WP_PLUGIN_DIR; ?></div>
		<div id="facebookId"><?php echo $facebookID; ?></div>
		<div id="feedLimit"><?php echo $feedLimit; ?></div>
		<div id="feedSize"><?php echo $feedSize; ?></div>
	</div>
  <?php
	settings_fields($this->plugin_name);
	do_settings_sections($this->plugin_name);
	echo "
	<div class='sff-left-side'>
		<div class='sff-admin-options'>
			<fieldset>
				<label for '". $pluginName . "-facebook-id'>Facebook ID</label>
				<input type='text' id='". $pluginName . "-facebook-id' name='". $pluginName . "[sfacebook-id]' value='". $facebookID . "'/>
			</fieldset>
			<fieldset>
				<label for '". $pluginName . "-facebook-count'>Post Limit</label>
				<input type='text' id='". $pluginName . "-facebook-count' name='". $pluginName . "[sfeed-limit]' value='". $feedLimit . "'/>
			</fieldset>
			<fieldset>
				<label for '". $pluginName . "-facebook-size'>Widget Size</label>
				<select id='". $pluginName . "-facebook-size' name='". $pluginName . "[sfeed-size]'>
					<option value='small' ";
					if($feedSize == "small"){echo "selected";}
					echo ">Small (Smallest file size)</option>
					<option value='medium' ";
					if($feedSize == "medium"){echo "selected";}
					echo ">Medium</option>
					<option value='large' ";
					if($feedSize == "large"){echo "selected";}
					echo ">Large</option>
					";
				echo "</select>
			</fieldset>";
			function sff_cacheFind($duration){
				$selectedHourly = "";
				$selected4Hours = "";
				$selectedDaily = "";
				$selected3Day = "";
				$selectedWeekly = "";
				$selectedMonthly = "";
				if($duration == "hourly"){$selectedHourly = " selected";}
				if($duration == "4-hours"){$selected4Hours = " selected";}
				if($duration == "daily"){$selectedDaily = " selected";}
				if($duration == "3-days"){$selected3Day = " selected";}
				if($duration == "weekly"){$selectedWeekly = " selected";}
				if($duration == "monthly"){$selectedMonthly = " selected";}
				return "
					<option value='hourly'" . $selectedHourly . ">Hourly</option>
					<option value='4-hours'" . $selected4Hours . ">Every 4 Hours</option>
					<option value='daily'" . $selectedDaily . ">Daily</option>
					<option value='3-days'" . $selected3Day . ">Every 3 Days</option>
					<option value='weekly'" . $selectedWeekly . ">Weekly</option>
					<option value='monthly'" . $selectedMonthly . ">Monthly</option>
				";
			}
			echo "<fieldset>
				<label for '" . $pluginName . "-facebook-expiration'>Cache Expiration</label>
				<select id='" . $pluginName . "-facebook-expiration' name='". $pluginName . "[sfeed-cache-expiration]'>" . sff_cacheFind($cacheLimit) . "</select>
			</fieldset>
		</div>
		<div class='sff-instructions'>
			<h3>Instructions</h3>
			<p>
			<h4>Facebook ID</h4>
				To find your facebook ID, go to your Facebook Page and note the URL. The ID can be found by extracting the following:
			</p>
			Example URL: https://www.facebook.com/<b>THIS-IS-YOUR-ID</b>/1234567
			<p>
				<h4>Post Limit</h4>
				Post Limit is the amount of Facebook posts that you would like to list. For performances, we recommend setting this to under 20 posts, with 8 being optimal.
			</p>
			<p>
				<h4>Widget Size</h4>
				This not only sets the width of the Facebook Widget, but it also sets the size of images that will be retrieved from Facebook. Use \"Small\" for optimal performance, unless you find that the pictures are blurry or pixelated, and then go up from there to suit your needs.
			</p>
			<p>
				<h4>Cache Expiration</h4>
				This sets how often the feed will refresh. Obviously, the longer the time, the better the performance. However, this plugin implements a Javascript AJAX refresh, so your users will most likely not even notice when the cache refills, since it will be refilling in the background after the page has loaded and only if the cache is expired. One last thing to note, your cache will refresh every time you refresh this admin page or click \"Save all Changes\".
			</p>
			<p>
				<h4>To Display The Feed on Your Site</h4>
				A \"Snazzy Facebook Feed\" Widget has been added to the list of widgets in the \"Appearance/Widgets\" section of the Wordpress backend. Simply drag the widget to the content area in which you would like it to be displayed, set your title, and click \"Save\". Alternatively, you can display the feed in any post or page by inserting the shortcode \"[sff_facebook_widget]\", or insert it in your template php files by copying and pasting:<br> \"<code>&lt;?php echo do_shortcode( '[sff_facebook_widget]' ); ?&gt;</code>\".
			</p>
		</div>
	";
	submit_button('Save all changes', 'primary','submit', TRUE);
	echo "</div>
	<div class='sff-right-side'><h3>A Preview of Your Feed</h3>
	";
		if(!empty($facebookID)){
			$profilePicLink = "https://graph.facebook.com/" . $facebookID . "/picture?type=square";
			$picture = $profilePicLink;
		}
		$today = date("Y-m-d-h-m-s");
		$widthClass="back-facing";
		if($feedSize == "small"){$widgetSize = 250; $widthClass = $widthClass . "-small";}
		if($feedSize == "medium"){$widgetSize = 400; $widthClass = $widthClass . "-medium";}
		if($feedSize == "large"){$widgetSize = 700; $widthClass = $widthClass . "-large";}
		
		function returnPhotoSize($photoID, $accessToken, $widgetSize){
			$photoLink = "https://graph.facebook.com/v2.8/" . $photoID . "?fields=images&access_token=".$accessToken;
			$pictureData = json_decode(snazzy_ff_fetchUrl($photoLink));
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
		
		
		// Turn on output buffering
		$theFeed = "https://graph.facebook.com/" . $facebookID . "/posts?fields=id,from,message,message_tags,story,story_tags,link,source,name,caption,description,type,status_type,object_id,created_time&access_token=".$access_token."&limit=" . $feedLimit;
		$jsonFeed = snazzy_ff_fetchUrl($theFeed);
		$fullFeed = json_decode($jsonFeed);
		if($fullFeed->error){
			echo "<div class='ssf-error-message'>Invalid Facebook ID</div>";
		}
		else {
			$pictureNumber = 2;
			echo "<div id='sff-main-widget' class='". $widthClass."'>";
			$getCover = "https://graph.facebook.com/" . $facebookID ."?fields=cover,name,link,fan_count&access_token=" . $access_token;
			$coverURL = snazzy_ff_fetchUrl($getCover);
			$fullCover = json_decode($coverURL);
			$coverID = $fullCover->cover->cover_id;
			$totalLikes = $fullCover->fan_count;
			$pageLink = $fullCover->link;
			$pageName = $fullCover->name;
			$sizedURL = returnPhotoSize($coverID, $access_token, $widgetSize);
			echo "
				<div class='sff-the-cover' style='background-image: url(\"" . $sizedURL . "\");'>
					<div class='sff-proportion'></div>
					<img src='" . $profilePicLink . "' class='sff-picture-in-cover'>
					<div class='sff-profile-name-head'><a href='" . $pageLink . "'>" . $pageName . "</a>
					<br>
					<span class='sff-total-likes'>" . $totalLikes . " likes</span></div>
					<a href='" . $pageLink . "' target='_blank'>
						<div class='sff-like-page sff-social-button'>
							<img src='" . plugins_url( '../images/facebook-like.svg', dirname(__FILE__)) . "' class='facebook-like-icon'>Like Page
						</div>
					</a>
					<a href='" . $pageLink . "' target='_blank'>
						<div class='sff-share-page sff-social-button'>
							<img src='" . plugins_url( '../images/facebook-share.svg', dirname(__FILE__)) . "' class='facebook-share-icon'>Share
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
				$source = $feedData->source;
				$linkURL = $feedData->link;
				$pluginDir = plugins_url( 'images/wordpress.png', __FILE__ );
				$statusType = $feedData->status_type;
				$likes = json_decode(snazzy_ff_fetchUrl("https://graph.facebook.com/v2.8/" . $objectID . "/likes?summary=total_count&access_token=" . $access_token));
				$likes = $likes->summary->total_count;
				if(empty($likes)){$likes = 0;}
				$shares = json_decode(snazzy_ff_fetchUrl("https://graph.facebook.com/v2.8/" . $postID . "?fields=shares&access_token=" . $access_token));
				$shares = $shares->shares->count;
				if(empty($shares)){$shares = 0;}
				if($postType == "photo"){
					$photoLink = "https://graph.facebook.com/v2.8/" . $objectID . "?fields=link%2Cimages&access_token=".$access_token;
					$pictureData = json_decode(snazzy_ff_fetchUrl($photoLink));
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
					$contentLink =$pictureLink;
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
						$getYouTube = $youTubeThumbURL;
						$videoType = "youtube-link";
						$messageContent = "
						<a href='". $source."' class='".$videoType."'><img class='feed-pic' src='".$getYouTube."'></a>
					";
						//$headline = "<div class='headline'>".$getYouTube."</div>";
					}
					else if($statusType == "added_video") {
						$videoPic = json_decode(snazzy_ff_fetchUrl("https://graph.facebook.com/v2.8/" . $objectID . "?fields=picture&access_token=" . $access_token));
						$youTubeThumbURL = $videoPic->picture;
						$getYouTube = $youTubeThumbURL;
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
					$eventGet =  json_decode(snazzy_ff_fetchUrl("https://graph.facebook.com/v2.8/" . $objectID . "?access_token=" . $access_token));
					$coverGet =  json_decode(snazzy_ff_fetchUrl("https://graph.facebook.com/v2.8/" . $objectID . "?fields=cover&access_token=" . $access_token));
					$coverURL = $coverGet->cover->source;
					$getEventCover = $coverURL;
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
					<div class='sff-likes'>
						<img src='" . plugins_url( '../images/facebook-like.svg', dirname(__FILE__)) . "' class='facebook-like-icon'>
						" . $likes . "
					</div>
					<div class='sff-shares'>
						<img src='" . plugins_url( '../images/facebook-share.svg', dirname(__FILE__)) . "' class='facebook-share-icon'>
						" .$shares."
					</div>
				</div>
				";
				echo $feedStructure;
			}
			
	
			echo "</div>";
		}
  ?>
 </div>