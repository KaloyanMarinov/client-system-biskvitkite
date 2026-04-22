<?php

/**
 * Public-facing orchestrator.
 *
 * Loads every focused hook class that lives under public/hooks/.
 * Add new frontend modules there — one file per concern:
 *
 *  - IGS_CS_Public_Setup_Hooks   → global init (user context)
 *  - IGS_CS_Public_Product_Hooks → price-list product visibility
 *  - IGS_CS_Public_Pricing_Hooks → per-price-list product pricing
 *  - IGS_CS_Public_Enqueue_Hooks → frontend CSS / JS (extend as needed)
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Public
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Public {

  /**
   * @var IGS_CS_Public|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Public
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
   * Load all focused public hook classes.
   * Each class self-registers its WordPress hooks on instantiation.
   *
   * @since 1.0.0
   * @return void
   */
  private function includes() {

    require_once IGS_CS_ABSPATH . '/public/hooks/igs-public-setup-hooks.php';
    require_once IGS_CS_ABSPATH . '/public/hooks/igs-public-product-hooks.php';
    require_once IGS_CS_ABSPATH . '/public/hooks/igs-public-pricing-hooks.php';
    require_once IGS_CS_ABSPATH . '/public/hooks/igs-public-enqueue-hooks.php';

  }

}

IGS_CS_Public::instance();
