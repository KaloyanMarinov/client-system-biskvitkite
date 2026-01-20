<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/includes
 * @author     igamingsolutions.com <support@igamingsolutions.com>
 */
class IGS_CS_I18N extends IGS_CS_Loader {
	
	/**
	 * The single instance of the class.
	 *
	 * @var IGS_CS_I18N
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * IGS_CS_I18N Instance.
	 *
	 * Ensures only one instance of kf_Admin is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return IGS_CS_I18N - IGS_CS_I18N instance.
	 */
  public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
    return self::$_instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( ) {

    $this->hooks();
		$this->run();

	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function hooks() {
		$this->add_action( 'init', $this, 'load_plugin_textdomain' );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain( 'igs-client-system', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages' );

	}

}

IGS_CS_I18N::instance();