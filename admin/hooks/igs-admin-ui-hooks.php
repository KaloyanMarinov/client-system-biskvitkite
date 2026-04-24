<?php

/**
 * Admin UI Hooks.
 *
 * Responsible for everything visual on the admin side:
 * - enqueueing assets
 * - injecting the plugin header template
 * - filtering out unneeded WordPress styles on plugin pages
 * - customer search and contact-method tweaks
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Admin/Hooks
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Admin_UI_Hooks extends IGS_CS_Loader {

  /**
   * @var IGS_CS_Admin_UI_Hooks|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Admin_UI_Hooks
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
   * Styles that must survive the dequeue pass on plugin pages.
   *
   * @since 1.0.0
   * @var string[]
   */
  private $allowed_styles = array(
    'dashicons',
    'select2_style',
    'wc-components',
    'woocommerce_admin_styles',
    'econt_style',
    'speedy_style',
  );

  /**
   * @since 1.0.0
   * @return void
   */
  private function hooks() {
    // Enqueue plugin assets (priority 10)
    $this->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin_scripts', 10, 1 );

    // Dequeue ALL other WP/third-party admin styles AFTER everything has been
    // enqueued (priority 99999).  This removes them from WP's queue before
    // wp-admin/load-styles.php is built, so no bundled stylesheet request is made.
    $this->add_action( 'admin_enqueue_scripts', $this, 'dequeue_wp_admin_styles', 99999, 1 );

    $this->add_action( 'in_admin_header',        $this, 'render_header_template', 10 );
    $this->add_action( 'igs_cs_before_content',  $this, 'render_page_title', 10 );

    $this->add_filter( 'user_contactmethods',                   $this, 'clear_contact_methods',   11, 1 );
    $this->add_filter( 'woocommerce_customer_search_customers', $this, 'filter_search_customers', 10, 2 );
  }

  /**
   * Enqueue plugin-specific CSS and JS on plugin admin pages.
   *
   * On the subscription edit page, also loads WooCommerce order meta-box
   * scripts and localises them with the correct post ID and nonces.
   *
   * @since 1.0.0
   * @param string $hook Current admin page hook.
   * @return void
   */
  public function enqueue_admin_scripts( $hook ) {

    if ( ! $hook || false === strpos( $hook, 'igs-' ) ) {
      return;
    }

    wp_enqueue_style( 'dashicons' );

    wp_enqueue_style(
      'igs-cs-styles',
      IGS_CS()->plugin_url() . '/resources/css/igs-styles.css',
      array( 'select2_style' ),
      null
    );

    wp_enqueue_script(
      'igs-cs-scripts',
      IGS_CS()->plugin_url() . '/resources/js/igs-script.js',
      array( 'wc-admin-meta-boxes' ),
      null,
      array( 'strategy' => 'async', 'in_footer' => true )
    );

    $is_subscription_edit = (
      isset( $_GET['action'], $_GET['page'] ) &&
      'edit' === $_GET['action'] &&
      'igs-subscriptions' === $_GET['page']
    );

    if ( $is_subscription_edit ) {

      wp_enqueue_script(
        'wc-admin-order-meta-boxes',
        WC()->plugin_url() . '/assets/js/admin/meta-boxes-order.min.js',
        array(),
        null
      );

      wp_enqueue_script(
        'wcs-admin-meta-boxes-order',
        WC_Subscriptions_Core_Plugin::instance()->get_subscriptions_core_directory_url( 'assets/js/admin/wcs-meta-boxes-order.js' ),
        array(),
        null,
        false
      );

      wp_localize_script( 'wc-admin-meta-boxes', 'woocommerce_admin_meta_boxes', array(
        'add_order_note_nonce'    => wp_create_nonce( 'add-order-note' ),
        'delete_order_note_nonce' => wp_create_nonce( 'delete-order-note' ),
        'search_products_nonce'   => wp_create_nonce( 'search-products' ),
        'ajax_url'                => admin_url( 'admin-ajax.php' ),
        'post_id'                 => absint( $_GET['id'] ),
      ) );
    }

  }

  /**
   * Output the shared plugin header on plugin admin pages.
   *
   * @since 1.0.0
   * @return void
   */
  public function render_header_template() {

    $screen = get_current_screen();

    if ( ! $screen || false === strpos( $screen->id, 'page_igs-' ) ) {
      return;
    }

    igs_cs_get_template( 'admin/header' );

  }

  /**
   * Output the page title partial via the igs_cs_before_content action.
   *
   * @since 1.0.0
   * @return void
   */
  public function render_page_title() {
    igs_cs_get_template( 'admin/part/page-title' );
  }

  /**
   * Dequeue every WordPress and third-party admin style that is not in the
   * $allowed_styles list on plugin-owned pages.
   *
   * Running at priority 99999 ensures all other plugins and WP core have
   * already finished enqueuing.  Removing handles from the queue at this
   * point prevents them from being included in the wp-admin/load-styles.php
   * bundle request that WordPress builds just before printing the <head>.
   *
   * @since 1.0.0
   * @param string $hook Current admin page hook.
   * @return void
   */
  public function dequeue_wp_admin_styles( $hook ) {

    if ( ! $hook || false === strpos( $hook, 'igs-' ) ) {
      return;
    }

    $wp_styles = wp_styles();

    foreach ( $wp_styles->queue as $handle ) {
      if ( in_array( $handle, $this->allowed_styles, true ) ) {
        continue;
      }

      if ( false !== strpos( $handle, 'igs-cs-' ) ) {
        continue;
      }

      wp_dequeue_style( $handle );
    }

  }

  /**
   * Remove all default contact methods from WP user profiles.
   *
   * @since 1.0.0
   * @return array
   */
  public function clear_contact_methods() {
    return array();
  }

  /**
   * Restrict WooCommerce customer search to the 'customer' role and
   * also allow searching by billing phone number.
   *
   * @since 1.0.0
   * @param array  $args WP_User_Query args.
   * @param string $term The search term.
   * @return array
   */
  public function filter_search_customers( $args, $term ) {

    $args['role'] = 'customer';

    if ( isset( $args['search_columns'] ) ) {
      $args['search_columns'] = array( 'ID', 'user_email' );
    }

    if ( isset( $args['meta_query'] ) ) {
      $args['meta_query'][] = array(
        'key'     => 'billing_phone',
        'value'   => $term,
        'compare' => 'LIKE',
      );
    }

    return $args;

  }

}

IGS_CS_Admin_UI_Hooks::instance();
