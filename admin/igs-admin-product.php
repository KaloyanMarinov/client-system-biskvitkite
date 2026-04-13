<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_CS/Admin
 * @author     igamingsolutions.com <support@igamingsolutions.com>
 *
 */
class IGS_CS_Admin_Product {

  /**
   * The single instance of the Admin class.
   *
   * @since 1.0.0
   * @access private
   * @static
   * @var IGS_CS_Admin_Product|null $_instance Singleton instance.
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

  }

  /**
   * Loads necessary admin-specific dependencies.
   *
   * @since 1.0.0
   */
  private function includes() {

    require_once IGS_CS_ABSPATH . '/admin/components/igs-admin-product-data.php';

  }

  /**
   * Get Product Data Component.
   *
   * @since  1.0.0
   * @return IGS_CS_Admin_Product
   */
  // public function get_product_data() {
  //   return IGS_CS_Admin_Product_Data::instance();
  // }

}

IGS_CS_Admin_Product::instance();
