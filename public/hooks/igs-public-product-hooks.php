<?php

/**
 * Public Product Visibility Hooks.
 *
 * Applies price-list-based product visibility rules on the frontend:
 * - Blocks direct access to restricted product pages (404)
 * - Filters WooCommerce product loop queries
 * - Filters the CPT data-store product query
 *
 * All visibility logic is centralised in IGS_CS_Product_Visibility so
 * that this file contains only hook wiring and thin callbacks.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Public/Hooks
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Public_Product_Hooks extends IGS_CS_Loader {

  /**
   * @var IGS_CS_Public_Product_Hooks|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Public_Product_Hooks
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
    $this->add_action( 'template_redirect',                                  $this, 'disable_product_page',        10 );
    $this->add_action( 'woocommerce_product_query',                          $this, 'filter_product_query',        10, 1 );
    $this->add_filter( 'woocommerce_product_data_store_cpt_get_products_query', $this, 'filter_data_store_query',  10, 1 );
  }

  /**
   * Return a 404 when a customer tries to view a product that is not
   * visible to their price list.
   *
   * @since 1.0.0
   * @return void
   */
  public function disable_product_page() {

    if ( ! is_product() ) {
      return;
    }

    if ( ! IGS_CS_Product_Visibility::should_apply() ) {
      return;
    }

    $allowed_roles = get_post_meta( get_the_ID(), '_visibility_roles', true );

    if ( empty( $allowed_roles ) ) {
      return;
    }

    $price_list = IGS_CS_Product_Visibility::get_user_price_list();

    if ( ! in_array( $price_list, (array) $allowed_roles, true ) ) {
      global $wp_query;
      $wp_query->set_404();
      status_header( 404 );
      nocache_headers();
    }

  }

  /**
   * Append a visibility meta_query clause to WooCommerce's product loop query.
   *
   * @since 1.0.0
   * @param WP_Query $q
   * @return void
   */
  public function filter_product_query( $q ) {

    if ( ! IGS_CS_Product_Visibility::should_apply() ) {
      return;
    }

    $price_list = IGS_CS_Product_Visibility::get_user_price_list();
    $meta_query = (array) $q->get( 'meta_query' );
    $meta_query[] = IGS_CS_Product_Visibility::get_meta_query( $price_list );
    $q->set( 'meta_query', $meta_query );

  }

  /**
   * Append a visibility meta_query clause to the CPT data-store products query.
   *
   * @since 1.0.0
   * @param array $query_vars
   * @return array
   */
  public function filter_data_store_query( $query_vars ) {

    if ( ! IGS_CS_Product_Visibility::should_apply() ) {
      return $query_vars;
    }

    $price_list = IGS_CS_Product_Visibility::get_user_price_list();

    if ( ! isset( $query_vars['meta_query'] ) ) {
      $query_vars['meta_query'] = array();
    }

    $query_vars['meta_query'][] = IGS_CS_Product_Visibility::get_meta_query( $price_list );

    return $query_vars;

  }

}

IGS_CS_Public_Product_Hooks::instance();
