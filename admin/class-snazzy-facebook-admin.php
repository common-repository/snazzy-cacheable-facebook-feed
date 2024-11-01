<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       lucashitch.com
 * @since      1.0.0
 *
 * @package    Snazzy_Facebook
 * @subpackage Snazzy_Facebook/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Snazzy_Facebook
 * @subpackage Snazzy_Facebook/admin
 * @author     Lucas Hitch <lucas@meridio.media>
 */
class Snazzy_Facebook_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Snazzy_Facebook_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Snazzy_Facebook_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		global $pw_settings_page;
		if( $hook != $pw_settings_page ) 
		return;
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/snazzy-facebook-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Snazzy_Facebook_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Snazzy_Facebook_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		global $pw_settings_page;
		if( $hook != $pw_settings_page ) 
		return;
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/snazzy-facebook-admin.js', array( 'jquery' ), $this->version, true );

	}
	
	function pw_create_settings_page() {
			global $pw_settings_page;
			$pw_settings_page = add_options_page( 'Setup your Facebook Feed', 'Snazzy Facebook', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page'));
		}
	public function add_plugin_admin_menu() {
		
		
	}

 /**
 * Add settings action link to the plugins page.
 *
 * @since    1.0.0
 */
 
	public function add_action_links( $links ) {
		$settings_link = array(
			'<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
			);
		return array_merge(  $settings_link, $links );
	}

/**
 * Render the settings page for this plugin.
 *
 * @since    1.0.0
 */
 
public function display_plugin_setup_page() {
    include_once( 'partials/snazzy-facebook-admin-display.php' );
}


 public function options_update() {
    register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
 }

public function validate($input) {
    // All checkboxes inputs        
    $valid = array();
	$facebookID = $input['sfacebook-id'];
	$postCount = $input['sfeed-limit'];
	$feedSize = $input['sfeed-size'];
	$cacheExpiration= $input['sfeed-cache-expiration'];
	
    //Cleanup
    $valid['sfacebook-id'] = $facebookID;
    $valid['sfeed-limit'] = $postCount;
    $valid['sfeed-size']=$feedSize;
    $valid['sfeed-cache-expiration']=$cacheExpiration;
    
     return $valid;
 }

}
include( 'widget-facebook-feed.php' );

function sff_facebook_widget( $atts ){
	ob_start();
	echo the_widget( 'Snazzy_Facebook_Widget' );
	$output = ob_get_contents(); // end output buffering
	ob_end_clean(); // grab the buffer contents and empty the buffer
	return $output;
}
add_shortcode( 'sff_facebook_widget', 'sff_facebook_widget' );
