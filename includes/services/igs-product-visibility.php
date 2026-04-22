<?php

/**
 * Product Visibility Service.
 *
 * Centralises all logic for price-list-based product visibility so that
 * the three public hooks (disable_product_page, product_query,
 * product_data_products_query) share a single implementation.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Services
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Product_Visibility {

  /**
   * Return the price-list term ID for the given (or current) user.
   *
   * @since 1.0.0
   * @param int|null $user_id  Defaults to the logged-in user.
   * @return string            Term ID as a string, or '0' for standard.
   */
  public static function get_user_price_list( $user_id = null ) {

    if ( null === $user_id ) {
      $user_id = get_current_user_id();
    }

    if ( ! $user_id ) {
      return '0';
    }

    $value = get_user_meta( $user_id, 'price_list', true );

    return ! empty( $value ) ? (string) $value : '0';

  }

  /**
   * Build the meta_query clause that filters products by visibility.
   *
   * Matches products that are either unrestricted (empty / non-existent meta)
   * or that include the given price-list in their allowed roles.
   *
   * @since 1.0.0
   * @param string $price_list  Price-list term ID.
   * @return array
   */
  public static function get_meta_query( $price_list ) {

    return array(
      'relation' => 'OR',
      array(
        'key'     => '_visibility_roles',
        'value'   => '',
        'compare' => '=',
      ),
      array(
        'key'     => '_visibility_roles',
        'compare' => 'NOT EXISTS',
      ),
      array(
        'key'     => '_visibility_roles',
        'value'   => '"' . $price_list . '"',
        'compare' => 'LIKE',
      ),
    );

  }

  /**
   * Whether visibility rules should be applied to the current request.
   *
   * Rules apply only on the frontend and only for guest users or users with
   * the 'customer' role. Admins, editors, etc. see everything.
   *
   * @since 1.0.0
   * @return bool
   */
  public static function should_apply() {

    if ( is_admin() && ! wp_doing_ajax() ) {
      return false;
    }

    $user = wp_get_current_user();

    if ( ! $user->exists() ) {
      return true;
    }

    return in_array( 'customer', (array) $user->roles, true );

  }

}
