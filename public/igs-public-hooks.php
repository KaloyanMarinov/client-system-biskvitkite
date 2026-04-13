<?php

/**
 * The Public Hooks functionality of the plugin.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS
 * @subpackage IGS/Public/Hooks
 * @author     igamingsolutions.com <support@igamingsolutions.com>
 *
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Public_Hooks extends IGS_CS_Loader {

	/**
	 * The single instance of the class.
	 *
	 * @var IGS_CS_Public_Hooks
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Main IGS_CS_Public_Hooks Instance.
	 *
	 * Ensures only one instance of IGS_CS_Public_Hooks is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return IGS_CS_Public_Hooks - Main instance.
	 */
  public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
    return self::$_instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( ) {

    $this->hooks();
		$this->run();

	}


  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function hooks() {
    $this->add_filter('woocommerce_product_data_store_cpt_get_products_query', $this, 'product_data_products_query', 10, 1);


    $this->add_action( 'init', 'IGS_CS_User', 'igs_setup_user_data', 10 );
    $this->add_action( 'wp_enqueue_scripts', $this, 'enqueue_public_scripts' );
    $this->add_action('template_redirect', $this, 'disable_product_page', 10);
    $this->add_action('woocommerce_product_query', $this, 'product_query', 10, 1);
    $this->add_filter( 'woocommerce_product_get_price', $this, 'woocommerce_get_price', 10, 2);
    $this->add_filter( 'woocommerce_product_variation_get_price', $this, 'woocommerce_get_price', 10, 2);
    $this->add_filter( 'woocommerce_variation_prices_price', $this, 'woocommerce_get_price', 10, 2);
    $this->add_filter( 'woocommerce_variation_prices_regular_price', $this, 'woocommerce_get_price', 10, 2);
  }

  /**
   * Enqueue admin-specific JavaScript.
   *
   * @since 1.0.0
   *
   * @return void
   */
  public function enqueue_public_scripts() { }

  public function disable_product_page() {

    if (!is_product()) {
      return;
    }

    $user = wp_get_current_user();

    if ($user->exists() && !in_array('customer', (array) $user->roles)) {
      return;
    }

    $product_id = get_the_ID();
    $allowed_roles = get_post_meta($product_id, '_visibility_roles', true);

    if ( empty($allowed_roles) || $allowed_roles === '') {
      return;
    }

    if (!is_array($allowed_roles)) {
      $allowed_roles = array($allowed_roles);
    }

    $user_id = get_current_user_id();
    $user_price_list = '0';

    if ($user_id) {
      $meta_value = get_user_meta($user_id, 'price_list', true);

      if (!empty($meta_value)) {
        $user_price_list = $meta_value;
      }
    }

    if (!in_array($user_price_list, $allowed_roles)) {
      global $wp_query;

      $wp_query->set_404();
      status_header(404);
      nocache_headers();
    }
  }

  public function product_query( $q ) {

    if (is_admin()) {
      return $q;
    }

    $user = wp_get_current_user();

    if ($user->exists() && !in_array('customer', (array) $user->roles)) {
      return $q;
    }

    $user_id = $user->ID;
    $price_list = '0';

    if ($user_id) {
      $meta_value = get_user_meta($user_id, 'price_list', true);

      if ( ! empty($meta_value) ) {
        $price_list = $meta_value;
      }
    }

    $meta_query = (array) $q->get('meta_query');

    $meta_query[] = array(
      'relation' => 'OR',
      array(
        'key'     => '_visibility_roles',
        'value'   => '',
        'compare' => '='
      ),
      array(
        'key'     => '_visibility_roles',
        'compare' => 'NOT EXISTS'
      ),
      array(
        'key'     => '_visibility_roles',
        'value'   => '"' . $price_list . '"',
        'compare' => 'LIKE'
      )
    );

    $q->set('meta_query', $meta_query);
  }

  public function product_data_products_query( $query_vars ) {

    if (is_admin()) {
      return $query_vars;
    }

    $user = wp_get_current_user();

    if ($user->exists() && !in_array('customer', (array) $user->roles)) {
      return $query_vars;
    }

    $user_id         = $user->ID;
    $user_price_list = '0';

    if ($user_id) {
      $meta_value = get_user_meta($user_id, 'price_list', true);
      if (!empty($meta_value)) {
        $user_price_list = $meta_value;
      }
    }

    $visibility_meta_query = array(
      'relation' => 'OR',
      array(
        'key'     => '_visibility_roles',
        'value'   => '',
        'compare' => '='
      ),
      array(
        'key'     => '_visibility_roles',
        'compare' => 'NOT EXISTS'
      ),
      array(
        'key'     => '_visibility_roles',
        'value'   => '"' . $user_price_list . '"',
        'compare' => 'LIKE'
      )
    );

    if (!isset($query_vars['meta_query'])) {
      $query_vars['meta_query'] = array();
    }

    $query_vars['meta_query'][] = $visibility_meta_query;

    return $query_vars;
  }

  public function woocommerce_get_price($price, $product) {

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
      return $price;

    global $user;

    if ( ! $user ) {
      return $price;
    }

    if ( ! $list_id = $user->igs_get_price_list() )
      return $price;

    $type = $product->get_type();

    if ( in_array($type, array('simple', 'variation')) ) {
      $custom_price = $product->get_meta('_list_price_' . $list_id);
      if ($custom_price !== '' && $custom_price !== false) {
        return wc_format_decimal($custom_price);
      }
    }

    return $price;

  }

}

IGS_CS_Public_Hooks::instance();
