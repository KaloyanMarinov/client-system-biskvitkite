<?php

/**
 * Admin Shipping Hooks.
 *
 * Listens for the igs_renew_subscription action (fired after a manual
 * renewal is created) and delegates to IGS_CS_Shipping_Integration to
 * recalculate Speedy / Econt costs.
 *
 * Kept as its own file so that the shipping integration can be enabled
 * or disabled, swapped out, or tested without touching any other module.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Admin/Hooks
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Admin_Shipping_Hooks extends IGS_CS_Loader {

  /**
   * @var IGS_CS_Admin_Shipping_Hooks|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Admin_Shipping_Hooks
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct() {
    parent::__construct();
    $this->hooks();
    $this->run();
  }

  /**
   * @since 1.0.0
   * @return void
   */
  private function hooks() {
    $this->add_action( 'igs_renew_subscription', $this, 'on_subscription_renewed', 10, 2 );
  }

  /**
   * Recalculate shipping cost for the renewal order.
   *
   * @since 1.0.0
   * @param int      $order_id
   * @param WC_Order $order
   * @return void
   */
  public function on_subscription_renewed( $order_id, $order ) {
    IGS_CS_Shipping_Integration::instance()->update_delivery_price( $order_id, $order );
  }

}

IGS_CS_Admin_Shipping_Hooks::instance();
