<?php

/**
 * Admin Subscription Hooks.
 *
 * All hooks that integrate with WooCommerce Subscriptions:
 * - Menu registration
 * - Manual renewal
 * - Related-orders meta-box customisation
 * - Status, period and reactivation filters
 * - Duplicate-site suppression
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Admin/Hooks
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Admin_Subscription_Hooks extends IGS_CS_Loader {

  /**
   * @var IGS_CS_Admin_Subscription_Hooks|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Admin_Subscription_Hooks
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

    // Manual renewal
    $this->add_action( 'admin_init', 'IGS_CS_Subscription', 'handle_manual_renewal' );

    // Admin menus
    $this->add_action( 'admin_menu',     'IGS_CS_Admin_Menus', 'add_admin_menus',     10 );
    $this->add_action( 'admin_bar_menu', 'IGS_CS_Admin_Menus', 'add_adminbar_menus', 100, 1 );

    // Related-orders meta-box
    $this->add_action( 'wcs_related_orders_meta_box_rows', 'IGS_CS_Subscription', 'related_orders_table_row', 5 );

    // Status filters — both WCS hooks fire at different points
    $this->add_action( 'wcs_subscription_statuses',                    'IGS_CS_Admin_Subscriptions', 'filter_subscription_statuses', 10, 1 );
    $this->add_action( 'woocommerce_subscriptions_registered_statuses', 'IGS_CS_Admin_Subscriptions', 'filter_subscription_statuses', 10, 1 );

    // Subscription model filters
    $this->add_filter( 'igs_cs_filter_statuses',                              'IGS_CS_Subscription', 'filter_subscription_statuses',               10, 1 );
    $this->add_filter( 'woocommerce_subscription_period_interval_strings',    'IGS_CS_Subscription', 'filter_subscription_period_interval_strings', 10, 1 );
    $this->add_filter( 'woocommerce_subscription_periods',                    'IGS_CS_Subscription', 'filter_subscription_periods',                 10, 1 );
    $this->add_filter( 'woocommerce_can_subscription_be_updated_to_active',   'IGS_CS_Subscription', 'filter_allow_reactivation',                   10, 2 );
    $this->add_filter( 'woocommerce_can_subscription_be_updated_to_on-hold',  'IGS_CS_Subscription', 'filter_allow_reactivation',                   10, 2 );

    // Related-orders table columns
    $this->add_filter( 'wcs_related_orders_table_header_columns', 'IGS_CS_Subscription', 'related_orders_table_header_columns', 10, 1 );
    $this->add_filter( 'wcs_related_orders_table_row_columns',    'IGS_CS_Subscription', 'related_orders_table_row_columns',    10, 1 );

    // Miscellaneous WooCommerce / WCS filters
    $this->add_filter( 'woocommerce_current_user_can_edit_customer_meta_fields', null, '__return_false' );
    $this->add_filter( 'woocommerce_subscriptions_is_duplicate_site',            null, '__return_false' );

  }

}

IGS_CS_Admin_Subscription_Hooks::instance();
