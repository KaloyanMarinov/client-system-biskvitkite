<?php

/**
 * The file that defines the core plugin class
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 * 
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @since      1.0.0
 * @package    IGS_Client_System
 * @author     igamingsolutions.com <support@igamingsolutions.com>
 * 
 */
class IGS_Client_System {

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version  The current version of the plugin.
	 */
	protected $version = '1.0.0';

	/**
	 * The single instance of the class.
	 *
	 * @var IGS_Client_System
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
	}

	/**
	 * IGS_Client_System Instance.
	 *
	 * Ensures only one instance of KF is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return IGS_Client_System - instance.
	 */
  public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
    return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden', 'igs-client-system' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Deserialization of instances of this class is forbidden!', 'igs-client-system' ), '1.0.0' );
	}

	/**
	 * Define WC Constants.
	 */
	private function define_constants() {
		$this->define( 'IGS_CS_ABSPATH', dirname( IGS_CS_PLUGIN_FILE ) );
		$this->define( 'IGS_CS_PLUGIN_BASENAME', plugin_basename( IGS_CS_PLUGIN_FILE ) );
		$this->define( 'IGS_CS_VERSION', $this->version );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Load the dependencies for this plugin.
	 * 
   * @since 1.0.0
	 * @access private
	 * 
	 * @return void
	 * 
	 */
	private function includes() {

		require_once IGS_CS_ABSPATH . '/includes/funcitons/igs-template-functions.php';
		require_once IGS_CS_ABSPATH . '/includes/funcitons/igs-core-functions.php';
		require_once IGS_CS_ABSPATH . '/includes/core/igs-constants.php';
		require_once IGS_CS_ABSPATH . '/includes/abstracts/igs-loader.php';
		require_once IGS_CS_ABSPATH . '/includes/igs-i18n.php';
		require_once IGS_CS_ABSPATH . '/admin/igs-admin.php';
		require_once IGS_CS_ABSPATH . '/public/igs-public.php';

	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax or frontend.
	 * @return bool
	 */
	public function is_request( $type ) {

    switch ( $type ) {
      case 'admin':
        return is_admin();
      case 'ajax':
        return wp_doing_ajax();
      case 'frontend':
        return ! is_admin() || ! wp_doing_ajax() || ! wp_doing_cron();
      case 'cron':
        return wp_doing_cron();
    }

	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', WC_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( WC_PLUGIN_FILE ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		/**
		 * Filter to adjust the base templates path.
		 */
		return apply_filters( 'igs_cs_template_path', 'templates/' );
	}

}
