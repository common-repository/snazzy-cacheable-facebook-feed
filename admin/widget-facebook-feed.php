<?php

/**
 * Class Featured_Events
 */
class Snazzy_Facebook_Widget extends WP_Widget {

	/**
	 * Initializing the widget
	 */
	public function __construct() {
		$widget_ops = array(
			'class'			=>	'snazzy_fb_widget',
			'description'	=>	__( 'Add a facebook feed to your site!', 'fb_widget' )
		);

		parent::__construct(
			'awmi_featured_events',			//base id
			__( 'Snazzy Facebook Feed', 'fb_widget' ),	//title
			$widget_ops
		);
	}


	/**
	 * Displaying the widget on the back-end
	 * @param  array $instance An instance of the widget
	 */
	public function form( $instance ) {
		$widget_defaults = array(
			'title'			=>	'Facebook Feed',
			'theme' => "classic"
		);

		$instance  = wp_parse_args( (array) $instance, $widget_defaults );
		?>
		
		<!-- Rendering the widget form in the admin -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title', 'fb_widget' ); ?>
			</label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" class="widefat" value="<?php echo esc_attr( $instance['title'] ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>">
				<?php _e( 'Theme', 'fb_widget' ); ?>
			</label>
			<?php $custom_terms = get_terms('eventing-list');?>
		</p>

		<?php
	}


	/**
	 * Making the widget updateable
	 * @param  array $new_instance New instance of the widget
	 * @param  array $old_instance Old instance of the widget
	 * @return array An updated instance of the widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = $new_instance['title'];

		return $instance;
	}


	/**
	 * Displaying the widget on the front-end
	 * @param  array $args     Widget options
	 * @param  array $instance An instance of the widget
	 */
	public function widget( $args, $instance ) {
	if ( ! is_admin() ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
	
		//Grab all options
		$options = get_option("snazzy-facebook");

		// Cleanup
		$cleanup = $options['cleanup'];
		$facebookID = $options['sfacebook-id'];
		$feedLimit = $options['sfeed-limit'];
		$feedSize = $options['sfeed-size'];
		$cacheLimit = $options['sfeed-cache-expiration'];

		date_default_timezone_set('America/Denver');
		
		//our cached feed file
		$cacheFeed = plugin_dir_path( dirname(__FILE__ )) . "cached-content/cached-feed.php";
		
		//If the file doesn't exist for some reason, create a date in the past so that a new file gets generated
		if(!file_exists($cacheFeed)){
			$cachedFile = "nothing-yet";
			$compareCache = strtotime(date("2014-01-01"));
		}
		//Otherwise, retrieve the last time that the file was modified, effectively lettings us compare it for caching purposes
		else {
			$compareCache = filemtime($cacheFeed);
		}
		echo "<h4 class='widgettitle'>" . esc_attr( $instance['title'] ) . "</h4>";
		//Self explanitory. This figures out if it's expired based on our user's preferences
		if($cacheLimit == "hourly"){
			if ($compareCache <= strtotime('-1 hour')) {
				$expired = "true";
			} 
			else {
				$expired = "false";
			}
		}
		if($cacheLimit == "4-hours"){
			if ($compareCache <= strtotime('-4 hours')) {
				$expired = "true";
			} else {
				$expired = "false";
			}
		}
		if($cacheLimit == "daily"){
			if ($compareCache <= strtotime('-24 hours')) {
				$expired = "true";
			} else {
				$expired = "false";
			}
		}
		if($cacheLimit == "3-days"){
			if ($compareCache <= strtotime('-3 days')) {
				$expired = "true";
			} else {
				$expired = "false";
			}
		}
		if($cacheLimit == "weekly"){
			if ($compareCache <= strtotime('-1 week')) {
				$expired = "true";
			} else {
				$expired = "false";
			}
		}
		if($cacheLimit == "monthly"){
			if ($compareCache <= strtotime('-1 month')) {
				$expired = "true";
			} else {
				$expired = "false";
			}
		}
		if($expired == "true"){
			$thePluginURL = plugin_dir_url( dirname(__FILE__));
			//This will give the users' the expired cache file and then send an ajax request to recache the file. This way, the loadtime remains optimal and the javascript will process the recaching and the subsequent loads will all contain the new cache file
			echo "
			<div class='update-the-cache'>
			<script type='text/javascript'>
				(function( $ ) {
					var pluginURI= '" . $thePluginURL . "';
					$.ajax({
						method: 'POST',
						beforeSend: console.log('sending'),
						url: '" . $thePluginURL . "/functions/save-to-cache.php',
						data: { feedSize: '" . $feedSize . "', feedLimit:'". $feedLimit ."', facebookId:'" . $facebookID . "',  pluginURL: pluginURI}
					})
					.done(function( msg ) {
					console.log( 'Data Saved: ' + msg );
					});
				})( jQuery );
			</script>
			</div>
			";
			if(file_exists($cacheFeed)){
				include($cacheFeed);
			}
		}
			else {
				if(file_exists($cacheFeed)){
					include($cacheFeed);
				}
			}
			echo "</div>";
	}
}
}
function snazzyfb_register_widget() {
	register_widget( 'Snazzy_Facebook_Widget' );
}
add_action( 'widgets_init', 'snazzyfb_register_widget' );
