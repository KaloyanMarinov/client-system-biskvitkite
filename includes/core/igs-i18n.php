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
	 * Initialize the class and set its properties.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function __construct( ) {
		parent::__construct();

    $this->hooks();
		$this->run();

	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * 
	 * @return void
	 * 
	 */
	private function hooks() {
		$this->add_action( 'init', $this, 'load_plugin_textdomain' );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 * @access private
	 * 
	 * @return void
	 * 
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain( 'igs-client-system', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages' );

	}

}