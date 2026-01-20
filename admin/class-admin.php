<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/includes
 * @author     igamingsolutions.com <support@igamingsolutions.com>
 */
class IGS_CS_Admin extends IGS_CS_Loader {

  /**
	 * Dependencies service.
	 *
	 * @var IGS_CS_Dependencies
	 */
	private $dependencies;

  /**
	 * The single instance of the class.
	 *
	 * @var IGS_CS_Admin
	 * @since 1.0.0
	 */
  private static $_instance = null;

  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct( ) {
    $this->includes();
    $this->hooks();
    $this->run();
  }

  /**
   * Loads necessary admin-specific dependencies.
   *
   * @since 1.0.0
   */
  private function includes() {
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function hooks() {
    $this->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin_scripts' );
  }

  /**
   * Enqueue admin-specific JavaScript.
   *
   * @since 1.0.0
   */
  public function enqueue_admin_scripts() { }
}

IGS_CS_Admin::instance();