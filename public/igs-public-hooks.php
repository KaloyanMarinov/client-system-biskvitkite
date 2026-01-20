<?php

/**
 * The Public Hooks functionality of the plugin.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS
 * @subpackage IGS/Public/Hooks
 * @author     igamingsolutions.com <support@igamingsolutions.com>
 * 
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Public_Hooks extends IGS_CS_Loader {

	/**
	 * The single instance of the class.
	 *
	 * @var IGS_CS_Public_Hooks
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Main IGS_CS_Public_Hooks Instance.
	 *
	 * Ensures only one instance of IGS_CS_Public_Hooks is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return IGS_CS_Public_Hooks - Main instance.
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
    $this->add_action( 'wp_enqueue_scripts', $this, 'enqueue_public_scripts' );
  }

  /**
   * Enqueue admin-specific JavaScript.
   *
   * @since 1.0.0
   * 
   * @return void
   */
  public function enqueue_public_scripts() { }

}

IGS_CS_Public_Hooks::instance();
