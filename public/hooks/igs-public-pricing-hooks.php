<?php

/**
 * Public Pricing Hooks.
 *
 * Overrides product prices for customers whose account is assigned a
 * non-standard price list.  Covers simple products, product variations
 * and WooCommerce's variation price cache.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Public/Hooks
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Public_Pricing_Hooks extends IGS_CS_Loader {

  /**
   * @var IGS_CS_Public_Pricing_Hooks|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Public_Pricing_Hooks
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
    $this->add_filter( 'woocommerce_product_get_price',                $this, 'get_price', 10, 2 );
    $this->add_filter( 'woocommerce_product_variation_get_price',      $this, 'get_price', 10, 2 );
    $this->add_filter( 'woocommerce_variation_prices_price',           $this, 'get_price', 10, 2 );
    $this->add_filter( 'woocommerce_variation_prices_regular_price',   $this, 'get_price', 10, 2 );
  }

  /**
   * Return the price-list specific price when the current user has one.
   *
   * @since 1.0.0
   * @param string|float    $price
   * @param WC_Product      $product
   * @return string|float
   */
  public function get_price( $price, $product ) {

    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
      return $price;
    }

    global $user;

    if ( ! $user instanceof IGS_CS_User ) {
      return $price;
    }

    $list_id = $user->igs_get_price_list();

    if ( ! $list_id ) {
      return $price;
    }

    if ( in_array( $product->get_type(), array( 'simple', 'variation' ), true ) ) {
      $custom_price = $product->get_meta( '_list_price_' . $list_id );

      if ( $custom_price !== '' && $custom_price !== false ) {
        return wc_format_decimal( $custom_price );
      }
    }

    return $price;

  }

}

IGS_CS_Public_Pricing_Hooks::instance();
