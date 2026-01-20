<?php

/**
 * The Admin Hooks functionality of the plugin.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS
 * @subpackage IGS/Admin/Hooks
 * @author     igamingsolutions.com <support@igamingsolutions.com>
 * 
 */

 defined( 'ABSPATH' ) || exit;

class IGS_CS_Admin_Hooks extends IGS_CS_Loader {

	/**
	 * The single instance of the class.
	 *
	 * @var IGS_CS_Admin_Hooks
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Main IGS_CS_Admin_Hooks Instance.
	 *
	 * Ensures only one instance of IGS_CS_Admin_Hooks is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return IGS_CS_Admin_Hooks - Main instance.
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
	 * @since 1.0.0
   * 
	 */
	public function __construct( ) {

		$this->actions = array();
		$this->filters = array();

    $this->hooks();
		$this->run();

	}

/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
   * 
   * @return void
	 */
	private function hooks() {
    $this->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin_scripts' );
	}

  /**
   * Enqueue admin-specific Styles and Scripts.
   *
   * @since 1.0.0
   */
  public function enqueue_admin_scripts() { }
}

IGS_CS_Admin_Hooks::instance();
