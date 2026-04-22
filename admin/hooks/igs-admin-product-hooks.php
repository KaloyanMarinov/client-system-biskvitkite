<?php

/**
 * Admin Product Hooks.
 *
 * Responsible for everything product-related in the WP/WooCommerce admin:
 * - Registering the custom 'product_prices_list' taxonomy
 * - WooCommerce product-data tabs and panels for visibility
 * - Per-price-list pricing fields (simple products and variations)
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Admin/Hooks
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Admin_Product_Hooks extends IGS_CS_Loader {

  /**
   * @var IGS_CS_Admin_Product_Hooks|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Admin_Product_Hooks
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

    // Taxonomy
    $this->add_filter( 'woocommerce_after_register_taxonomy', $this, 'register_prices_list_taxonomy', 10 );

    // Product data tabs & panels
    $this->add_filter( 'woocommerce_product_data_tabs',   'IGS_CS_Admin_Product_Data', 'igs_product_data_tabs',        10, 1 );
    $this->add_action( 'woocommerce_product_data_panels', 'IGS_CS_Admin_Product_Data', 'igs_visibility_product_data',  10 );

    // Simple product pricing
    $this->add_action( 'woocommerce_product_options_pricing', 'IGS_CS_Admin_Product_Data', 'product_prices_list_pricing',      10 );
    $this->add_action( 'woocommerce_process_product_meta',    'IGS_CS_Admin_Product_Data', 'save_product_visibility_roles',    10, 1 );
    $this->add_action( 'woocommerce_process_product_meta',    'IGS_CS_Admin_Product_Data', 'save_product_prices_list_pricing', 10, 1 );

    // Variation pricing
    $this->add_action( 'woocommerce_variation_options_pricing', 'IGS_CS_Admin_Product_Data', 'variation_product_prices_list_pricing', 10, 3 );
    $this->add_action( 'woocommerce_save_product_variation',    'IGS_CS_Admin_Product_Data', 'save_variations_prices_list_pricing',   10, 2 );

  }

  /**
   * Register the 'product_prices_list' taxonomy after WooCommerce
   * has finished registering its own taxonomies.
   *
   * @since 1.0.0
   * @return void
   */
  public function register_prices_list_taxonomy() {

    register_taxonomy(
      'product_prices_list',
      apply_filters( 'woocommerce_taxonomy_objects_prices_list', array( 'product' ) ),
      apply_filters(
        'woocommerce_taxonomy_args_prices_list',
        array(
          'hierarchical'          => true,
          'update_count_callback' => '_wc_term_recount',
          'label'                 => __( 'Prices List', 'igs-client-system' ),
          'labels'                => array(
            'name'                  => __( 'Prices List',          'igs-client-system' ),
            'singular_name'         => __( 'Price list',           'igs-client-system' ),
            'menu_name'             => _x( 'Prices List',          'Admin menu name', 'igs-client-system' ),
            'search_items'          => __( 'Search',               'igs-client-system' ),
            'all_items'             => __( 'All Prices List',      'igs-client-system' ),
            'parent_item'           => __( 'Parent list',          'igs-client-system' ),
            'parent_item_colon'     => __( 'Parent list:',         'igs-client-system' ),
            'edit_item'             => __( 'Edit list',            'igs-client-system' ),
            'update_item'           => __( 'Update list',          'igs-client-system' ),
            'add_new_item'          => __( 'Add new list',         'igs-client-system' ),
            'new_item_name'         => __( 'New list name',        'igs-client-system' ),
            'not_found'             => __( 'No prices list found', 'igs-client-system' ),
            'item_link'             => __( 'Price List Link',      'igs-client-system' ),
            'item_link_description' => __( 'A link to a price list.', 'igs-client-system' ),
            'template_name'         => _x( 'Products by Price List', 'Template name', 'igs-client-system' ),
          ),
          'public'            => false,
          'show_ui'           => true,
          'meta_box_cb'       => false,
          'show_in_nav_menus' => false,
          'query_var'         => true,
          'capabilities'      => array(
            'manage_terms' => 'manage_product_terms',
            'edit_terms'   => 'edit_product_terms',
            'delete_terms' => 'delete_product_terms',
            'assign_terms' => 'assign_product_terms',
          ),
          'rewrite' => false,
        )
      )
    );

  }

}

IGS_CS_Admin_Product_Hooks::instance();
