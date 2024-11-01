<?php

/**
 *
 * @link              lucashitch.com
 * @since             1.0.0
 * @package           Snazzy_facebook
 *
 * @wordpress-plugin
 * Plugin Name:       Snazzy facebook
 * Plugin URI:        snazzyplugins.com
* Description:       Improve site speed and SEO rankings with the Snazzy Cacheable Facebook plugin for Wordpress! This plugin is the first ever Facebook Feed plugin to store your feed locally, making feed load times lightning fast. Quick and easy install.
 * Version:           2.3.0
 * Author:            The Snazzy Staff
 * Author URI:        snazzyplugins.com
 * Text Domain:       snazzy-facebook
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-snazzy-facebook-activator.php
 */
function activate_snazzy_facebook() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-snazzy-facebook-activator.php';
	Snazzy_facebook_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-snazzy-facebook-deactivator.php
 */
function deactivate_snazzy_facebook() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-snazzy-facebook-deactivator.php';
	Snazzy_facebook_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_snazzy_facebook' );
register_deactivation_hook( __FILE__, 'deactivate_snazzy_facebook' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-snazzy-facebook.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_snazzy_facebook() {

	$plugin = new snazzy_facebook();
	$plugin->run();

}
run_snazzy_facebook();
