<?php
/**
 * Plugin Name: Elementor Must-have Addons
 * Description: Custom premium widgets for Elementor, including 3D Video Scroll and Simple Submission Form.
 * Version:     1.0.0
 * Author:      Akash Mali <maliakash6198@gmail.com>
 * Author URI:  https://github.com/Akashmali6198
 * Text Domain: elementor-must-have-addons
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class Elementor_Must_Have_Addons {

	const VERSION = '1.0.0';
	const MINIMUM_ELEMENTOR_VERSION = '3.0.0';
	const MINIMUM_PHP_VERSION = '7.4';

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'i18n' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ] );

		// Activation hook to setup DB table
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
	}

	public function i18n() {
		load_plugin_textdomain( 'elementor-must-have-addons' );
	}

	public function init() {
		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return;
		}

		// Check Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		// Check PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}

		// Load Include Files
		require_once( __DIR__ . '/includes/admin-settings.php' );

		// Register Widget Categories
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );

		// Register Widgets
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

		// Register Scripts
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_frontend_scripts' ] );
		add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_frontend_styles' ] );
	}

	public function activate() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'emha_submissions';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			form_id varchar(100) NOT NULL,
			form_name varchar(255) NOT NULL,
			fields text NOT NULL,
			user_ip varchar(100) DEFAULT '' NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public function admin_notice_missing_main_plugin() {
		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'elementor-must-have-addons' ),
			'<strong>' . esc_html__( 'Elementor Must-have Addons', 'elementor-must-have-addons' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'elementor-must-have-addons' ) . '</strong>'
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	public function admin_notice_minimum_elementor_version() {
		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-must-have-addons' ),
			'<strong>' . esc_html__( 'Elementor Must-have Addons', 'elementor-must-have-addons' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'elementor-must-have-addons' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	public function admin_notice_minimum_php_version() {
		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-must-have-addons' ),
			'<strong>' . esc_html__( 'Elementor Must-have Addons', 'elementor-must-have-addons' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'elementor-must-have-addons' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'emha-category',
			[
				'title' => esc_html__( 'Must-have Addons', 'elementor-must-have-addons' ),
				'icon'  => 'fa fa-plug',
			]
		);
	}

	public function register_widgets( $widgets_manager ) {
		$active_widgets = get_option( 'emha_active_widgets', [ 'video-scroll' => '1', 'simple-form' => '1' ] );

		if ( ! empty( $active_widgets['video-scroll'] ) ) {
			require_once( __DIR__ . '/widgets/video-scroll/video-scroll-widget.php' );
			$widgets_manager->register( new \Elementor_Video_Scroll_Widget() );
		}

		if ( ! empty( $active_widgets['simple-form'] ) ) {
			require_once( __DIR__ . '/widgets/simple-form/simple-form-widget.php' );
			$widgets_manager->register( new \Elementor_Simple_Form_Widget() );
		}
	}

	public function register_frontend_scripts() {
		wp_register_script(
			'emha-video-scroll-script',
			plugins_url( '/widgets/video-scroll/assets/js/video-scroll.js', __FILE__ ),
			[ 'jquery' ],
			self::VERSION,
			true
		);
		wp_register_script(
			'emha-simple-form-script',
			plugins_url( '/widgets/simple-form/assets/js/simple-form.js', __FILE__ ),
			[ 'jquery' ],
			self::VERSION,
			true
		);
		$ajax_url = admin_url( 'admin-ajax.php' );
		if ( is_ssl() ) {
			$ajax_url = str_replace( 'http://', 'https://', $ajax_url );
		} else {
			$ajax_url = str_replace( 'https://', 'http://', $ajax_url );
		}

		wp_localize_script( 'emha-simple-form-script', 'emha_ajax', [
			'ajax_url' => $ajax_url,
			'nonce'    => wp_create_nonce( 'emha_form_nonce' )
		] );
	}

	public function register_frontend_styles() {
		wp_register_style(
			'emha-video-scroll-style',
			plugins_url( '/widgets/video-scroll/assets/css/video-scroll.css', __FILE__ ),
			[],
			self::VERSION
		);
		wp_register_style(
			'emha-simple-form-style',
			plugins_url( '/widgets/simple-form/assets/css/simple-form.css', __FILE__ ),
			[],
			self::VERSION
		);
	}
}

Elementor_Must_Have_Addons::instance();
