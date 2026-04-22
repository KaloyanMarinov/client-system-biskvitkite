<?php

/**
 * Admin Hooks Orchestrator.
 *
 * Loads every focused hook class that lives under admin/hooks/ and the
 * shipping integration.  This file used to contain all ~35 hook
 * registrations in a single class; each responsibility now lives in its
 * own file:
 *
 *  - IGS_CS_Admin_UI_Hooks           → assets, header template, style filtering
 *  - IGS_CS_Admin_Form_Hooks         → form saves, cache-busting, user profile fields
 *  - IGS_CS_Admin_Export_Hooks       → subscription and order XLS exports
 *  - IGS_CS_Admin_Product_Hooks      → product taxonomy, data tabs and pricing
 *  - IGS_CS_Admin_Shipping_Hooks     → delivery price recalculation after renewal
 *  - IGS_CS_Admin_Subscription_Hooks → WCS menus, statuses, filters, manual renewal
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Admin
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Admin_Hooks {

  /**
   * @var IGS_CS_Admin_Hooks|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Admin_Hooks
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct() {
    $this->includes();
  }

  /**
   * Load the shipping integration and all focused hook classes.
   * Each hook class self-registers its WordPress hooks on instantiation.
   *
   * @since 1.0.0
   * @return void
   */
  private function includes() {

    require_once IGS_CS_ABSPATH . '/admin/integrations/igs-shipping-integration.php';

    require_once IGS_CS_ABSPATH . '/admin/hooks/igs-admin-ui-hooks.php';
    require_once IGS_CS_ABSPATH . '/admin/hooks/igs-admin-form-hooks.php';
    require_once IGS_CS_ABSPATH . '/admin/hooks/igs-admin-export-hooks.php';
    require_once IGS_CS_ABSPATH . '/admin/hooks/igs-admin-product-hooks.php';
    require_once IGS_CS_ABSPATH . '/admin/hooks/igs-admin-shipping-hooks.php';
    require_once IGS_CS_ABSPATH . '/admin/hooks/igs-admin-subscription-hooks.php';

  }

}

IGS_CS_Admin_Hooks::instance();
