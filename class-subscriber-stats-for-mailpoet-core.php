<?php
/**
 * Core file for the Mailpoet Subscribers Plugin.
 *
 * @package mailpoet_subscriber_overview
 */
/**
 * Core class; contains everything needed to run the plugin.
 * Class Subscriber_Stats_For_Mailpoet
 */
class Subscriber_Stats_For_Mailpoet {
	const PHPUNIT_RUNNING = 0;
	/**
	 * Activation hook callback.
	 */
	public static function activate() {
		/* set activation timestamp */
		$timestamp = time();
		update_option( 'sjmailsub_activate', date( 'r', $timestamp ) );
	}

	/**
	 * Deactivation hook callback.
	 */
	public static function deactivate() {
		$timestamp = time();
		update_option( 'sjmailsub_deactivate', date( 'r', $timestamp ) );
	}

	/**
	 * Admin init callback
	 */
	public static function admin_init() {
		self::register_scripts();
		self::register_style();
		self::enqueue_style();
	}

	/**
	 * Enqueue scripts callback
	 */
	public static function frontend_enqueue_scripts() {

		self::register_scripts();
		self::register_style();
		self::enqueue_scripts();
		self::enqueue_style();
	}

	/**
	 * Add pages callback
	 */
	public static function add_pages() {
		$page = add_menu_page( 'Mailpoet Subscriber Statistics', 'MP Subscriber Statistics', 'publish_pages', 'wjmailsub-stats', 'sjmailsub_info_page', 'dashicons-location-alt' );
		add_action( 'admin_print_styles-' . $page, array( 'Subscriber_Stats_For_Mailpoet', 'enqueue_style' ) );
		add_action( 'admin_print_scripts-' . $page, array( 'Subscriber_Stats_For_Mailpoet', 'enqueue_scripts' ) );
	}
	/**
	 * Remove pages callback.
	 */
	public static function remove_pages() {
		/**
		 * Target non super admins.
		 *
		 * @todo remove created pages
		 */
	}

	/**
	 * Register javascript.
	 */
	public static function register_scripts() {
		$deps = array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-datepicker',
			'jquery-ui-widget',
			'jquery-ui-button',
			'jquery-ui-tabs',
			'jquery-ui-sortable',
			'jquery-ui-draggable',
			'jquery-ui-droppable',
			'jquery-ui-selectable',
			'jquery-ui-position',
			'jquery-ui-resizable',
			'jquery-ui-dialog',
			'jquery-ui-slider',
			'jquery-ui-mouse',
			'jquery-ui-autocomplete',
			'jquery-ui-progressbar',
		);

		wp_register_script( 'frontend_helper_script', plugins_url( 'js/frontend_script.js', __FILE__ ), $deps, '0.1', false );
		wp_register_script( 'chart_js', plugins_url( 'js/chart.min.js', __FILE__ ), $deps, '2.7.2', false );
		wp_register_script( 'select_2', plugins_url( 'js/select2.min.js', __FILE__ ), $deps, '4.0.6-rc.0', false );

	}

	/**
	 * Register css.
	 */
	private static function register_style() {
		wp_register_style( 'sjmailsub_style', plugins_url( 'assets/css/style.css', __FILE__ ), array(), '0.1' );
		wp_register_style( 'font_awesome', plugins_url( 'assets/css/font-awesome.min.css', __FILE__ ), array(), '0.1' );
		wp_register_style( 'select_2_style', plugins_url( 'assets/css/select2.min.css', __FILE__ ), array(), '0.1' );

	}

	/**
	 * Enqueue css.
	 */
	public static function enqueue_style() {
		wp_enqueue_style( 'sjmailsub_style' );
		wp_enqueue_style( 'font_awesome' );
		wp_enqueue_style( 'select_2_style' );
	}

	/**
	 * Enqueue javascript
	 * runs localize script to set ajax url and nonce
	 */
	public static function enqueue_scripts() {

		wp_enqueue_script( 'frontend_helper_script' );
		wp_enqueue_script( 'chart_js' );
		wp_enqueue_script( 'select_2' );

		$nonce_key = sjmailsub_nonce();
		$ajax_obj  = array(
			'nonce'       => $nonce_key,
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'plugins_url' => plugins_url(),
		);
		wp_localize_script( 'core_helper_script', 'ajax_obj', $ajax_obj );
		wp_localize_script( 'frontend_helper_script', 'ajax_obj', $ajax_obj );

	}

	/**
	 * Output buffer start for csv export
	 */
	public static function output_start() {
		ob_start();
	}

}
