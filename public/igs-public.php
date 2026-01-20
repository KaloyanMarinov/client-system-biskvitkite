<?php

/**
 * The public-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 * 
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_CS/Public
 * @author     igamingsolutions.com <support@igamingsolutions.com>
 * 
 */
class IGS_CS_Public extends IGS_CS_Loader {

  /**
   * The single instance of the Public class.
   *
   * @since 1.0.0
   * @access private
   * @static
   * @var IGS_CS_Public|null $_instance Singleton instance.
   */
  private static $_instance = null;

  /**
   * Reference to the Public Hooks instance.
   *
   * @since 1.0.0
   * @access private
   * @var IGS_CS_Public_Hooks $hooks Public hooks handler.
   */
  private $hooks;

  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct( ) {

    $this->includes();

  }

  /**
   * Loads necessary public-specific dependencies.
   *
   * @since 1.0.0
   */
  private function includes() {
    require_once IGS_CS_ABSPATH . '/public/igs-public-hooks.php';
  }

}

IGS_CS_Public::instance();