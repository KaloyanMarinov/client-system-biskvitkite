<?php
/**
 * The Admin List Subscription Component.
 *
 * @package    IGS
 * @subpackage IGS/Admin/Components
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Admin_Product_Data {

  /**
   * Singleton instance.
   */
  private static $_instance = null;

  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct() { }


  public static function igs_product_data_tabs( $tabs ) {

    $tabs['igs_visibility'] = array(
      'label'    => __( 'Visibility', 'igs-client-system' ),
      'target'   => 'igs_visibility_product_data',
      'class'    => array(),
      'priority' => 11,
    );

    return $tabs;
  }

  public static function igs_visibility_product_data() {

    global $post;

    $product = wc_get_product( $post->ID );

    igs_cs_get_template( 'admin/part/visibility-product-data', array(
      'product' => $product,
    ));

  }

  public static function igs_get_visibility_options() {

    $prices   = get_terms(array(
      'taxonomy'   => 'product_prices_list',
      'hide_empty' => false,
      'fields'     => 'id=>name'
    ));

    $options = array('0' => __('Customer', 'igs-client-system' )) + $prices;

    return $options;
  }

  public static function save_product_visibility_roles( $post_id ) {

    $product = wc_get_product($post_id);

    if (!$product) {
      return;
    }

    $visibility_roles = isset($_POST['_visibility_roles']) && is_array($_POST['_visibility_roles'])
      ? array_map('sanitize_text_field', $_POST['_visibility_roles'])
      : '';

    $product->update_meta_data('_visibility_roles', $visibility_roles);
    $product->save();

  }

  public static function product_prices_list_pricing() {
    global $post;

    $product = wc_get_product($post->ID);

    if ( ! $terms = get_terms( [ 'taxonomy' =>  'product_prices_list', 'hide_empty' => false, 'fields' => 'id=>name' ] ) )
        return;

    foreach ($terms as $id => $name) {
      woocommerce_wp_text_input(array(
        'id'            => '_list_price_'. $id,
        'wrapper_class' => 'form-field hide_if_subscription',
        'label'         =>  $name . ' ' . __('price', 'igs-client-system') . ' (' . get_woocommerce_currency_symbol() . ')',
        'value'         =>  $product->get_meta('_list_price_' . $id),
        'data_type'     => 'price',
      ));
    }
  }

  public static function save_product_prices_list_pricing( $post_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

  $product = wc_get_product( $post_id );
  $type    = $product->get_type();

		if ( ! $product ) {
			return;
		}

    if ( ! $terms = get_terms( [ 'taxonomy' =>  'product_prices_list', 'hide_empty' => false, 'fields' => 'ids' ] ) )
      return;

    foreach ($terms as $id) {
      $list_price = $_POST['_list_price_' . $id] ?? '';


      if ( $type !== 'simple' || ! empty( $list_price) ) {
        $product->update_meta_data( '_list_price_' . $id, sanitize_text_field($list_price) );
      } else {
        $product->delete_meta_data( '_list_price_' . $id );
      }
    }

    $product->save();
  }

  public static function variation_product_prices_list_pricing($loop, $variation_data, $variation) {

    if ( ! $terms = get_terms( [ 'taxonomy' =>  'product_prices_list', 'hide_empty' => false, 'fields' => 'id=>name' ] ) )
        return;

    $i = 0;
    foreach ($terms as $id => $name) {

      $wrapper_class = 'form-field variable_regular_price_'. $loop .'_field form-row hide_if_variable-subscription';

      if ( $i % 2 === 0) {
        $wrapper_class .= ' form-row-first';
      } else {
        $wrapper_class .= ' form-row-last';
      }

      woocommerce_wp_text_input(array(
        'id'            => '_list_price_'. $id .'_[' . $loop . ']',
        'wrapper_class' => $wrapper_class,
        'label'         => $name . ' ' . __('price', 'igs-client-system') .  ' (' . get_woocommerce_currency_symbol() . ')',
        'value'         => get_post_meta($variation->ID, '_list_price_' . $id, true),
        'data_type'     => 'price',
      ));

      $i++;
    }

  }

  public static function save_variations_prices_list_pricing($variation_id, $i) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $variation_id ) ) {
			return;
		}

    $variation = wc_get_product( $variation_id );
    $type      = $variation->get_type();


		if ( ! $variation ) {
			return;
		}

    if ( ! $terms = get_terms( [ 'taxonomy' =>  'product_prices_list', 'hide_empty' => false, 'fields' => 'ids' ] ) )
      return;

    foreach ($terms as $term_id) {
      $list_price = $_POST['_list_price_' . $term_id . '_'][$i] ?? '';

      if ( $type != 'variation' || empty( $list_price ) ) {
        $variation->delete_meta_data( '_list_price_' . $term_id );
      } else {
        $variation->update_meta_data( '_list_price_' . $term_id, sanitize_text_field($list_price) );
      }
    }

    $variation->save();
  }
}

IGS_CS_Admin_Product_Data::instance();
