<?php
/**
 * Plugin Name: Subscriber Stats For Mailpoet
 * Description: Display statistics of individual subscriber engagement through Mailpoet.
 * Version: 1.0
 * Author: Sparkjoy Studios
 * Author URI: http://sparkjoy.com
 * License: GPL2
 *
 * @package mailpoet_subscriber_overview
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/*activation hook*/
register_activation_hook( __FILE__, array( 'Subscriber_Stats_For_Mailpoet', 'activate' ) );
/*deactivation hook*/
register_deactivation_hook( __FILE__, array( 'Subscriber_Stats_For_Mailpoet', 'deactivate' ) );

/*initialition*/
add_action( 'admin_init', array( 'Subscriber_Stats_For_Mailpoet', 'admin_init' ) );

/* add pages  */
add_action( 'admin_menu', array( 'Subscriber_Stats_For_Mailpoet', 'add_pages' ) );
add_action( 'admin_menu', array( 'Subscriber_Stats_For_Mailpoet', 'remove_pages' ) );

/* ob_start */
add_action( 'init', array( 'Subscriber_Stats_For_Mailpoet', 'output_start' ) );

// Hook into mailpoet.
add_action( 'mailpoet_pages_subscriber', array( 'Subscriber_Stats_For_Mailpoet', 'sjmailsub_test' ) );
/*include php files*/
require_once plugin_dir_path( __FILE__ ) . '/pages/info-page.php';
require_once plugin_dir_path( __FILE__ ) . '/util/general-util.php';
require_once plugin_dir_path( __FILE__ ) . '/class-subscriber-stats-for-mailpoet-core.php';



