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
class IGS_CS_Admin {

  /**
   * The single instance of the Admin class.
   *
   * @since 1.0.0
   * @access private
   * @static
   * @var IGS_CS_Admin|null $_instance Singleton instance.
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

    require_once IGS_CS_ABSPATH . '/admin/igs-admin-menus.php';
    require_once IGS_CS_ABSPATH . '/admin/igs-admin-hooks.php';

    require_once IGS_CS_ABSPATH . '/admin/igs-admin-order.php';
    require_once IGS_CS_ABSPATH . '/admin/igs-admin-subscriptions.php';
    require_once IGS_CS_ABSPATH . '/admin/igs-admin-product.php';

  }

  /**
	 * Get Subscriptions Class.
	 *
	 * @return IGS_CS_Admin_Order
	 */
  public function order() {
    return IGS_CS_Admin_Order::instance();
  }

  /**
	 * Get Admin Menu Class.
	 *
	 * @return IGS_CS_Admin_Menus
	 */
  public function menus() {
    return IGS_CS_Admin_Menus::instance();
  }

  /**
	 * Get Subscriptions Class.
	 *
	 * @return IGS_CS_Admin_Subscriptions
	 */
  public function subscriptions() {
    return IGS_CS_Admin_Subscriptions::instance();
  }

  /**
   * Get Product Class.
   *
   * @return IGS_CS_Admin_Product
   */
  public function product() {
    return IGS_CS_Admin_Product::instance();
  }

}
