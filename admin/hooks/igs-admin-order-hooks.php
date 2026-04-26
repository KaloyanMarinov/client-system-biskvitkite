<?php

/**
 * Admin Order Hooks.
 *
 * Handles the "Preparation Date" (preparation date) field on the
 * standard WooCommerce order edit screen:
 * - Renders a date-picker field after the order-date field
 * - Saves the chosen date as order meta (_igs_preparation_date)
 * - Auto-sets the field to order_date + 2 days on every new order
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Admin/Hooks
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Admin_Order_Hooks extends IGS_CS_Loader {

  /**
   * @var IGS_CS_Admin_Order_Hooks|null
   */
  private static $_instance = null;

  /**
   * Meta key used to store the preparation date (Y-m-d).
   */
  const META_KEY = '_igs_preparation_date';

  /**
   * @since 1.0.0
   * @return IGS_CS_Admin_Order_Hooks
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

    // Render the field in the WC order edit screen (classic & HPOS).
    $this->add_action( 'woocommerce_admin_order_data_after_order_details', $this, 'render_preparation_date_field', 10, 1 );

    // Save – classic CPT-based orders.
    $this->add_action( 'woocommerce_process_shop_order_meta', $this, 'save_preparation_date', 10, 1 );

    // Save – HPOS (High Performance Order Storage).
    $this->add_action( 'woocommerce_before_order_object_save', $this, 'save_preparation_date_hpos', 10, 1 );

    // Orders list column – classic.
    $this->add_filter( 'manage_edit-shop_order_columns',        $this, 'add_preparation_date_column',      20, 1 );
    $this->add_action( 'manage_shop_order_posts_custom_column', $this, 'render_preparation_date_column',   10, 2 );
    $this->add_action( 'manage_shop_order_posts_custom_column', $this, 'render_returned_orders_column',    10, 2 );
    $this->add_action( 'manage_shop_order_posts_custom_column', $this, 'render_prepaid_column',            10, 2 );

    // Orders list column – HPOS.
    $this->add_filter( 'woocommerce_shop_order_list_table_columns',       $this, 'add_preparation_date_column',         20, 1 );
    $this->add_action( 'woocommerce_shop_order_list_table_custom_column', $this, 'render_preparation_date_column_hpos', 10, 2 );
    $this->add_action( 'woocommerce_shop_order_list_table_custom_column', $this, 'render_returned_orders_column_hpos',  10, 2 );
    $this->add_action( 'woocommerce_shop_order_list_table_custom_column', $this, 'render_prepaid_column_hpos',          10, 2 );

    // AJAX: search subscription products (used in subscription edit UI).
    $this->add_action( 'wp_ajax_igs_search_subscription_products', $this, 'ajax_search_subscription_products' );

  }

  /**
   * Render the preparation-date field after the order-date field.
   *
   * The field re-uses the existing `.js-datepicker` class so it picks up
   * the global flatpickr initialisation (date-only, d.m.Y format).
   *
   * @since 1.0.0
   * @param WC_Order $order
   * @return void
   */
  public function render_preparation_date_field( $order ) {

    $stored  = $order->get_meta( self::META_KEY );
    $display = $stored ? $stored : ''; // stored as Y-m-d, same format WC date-picker uses

    ?>
    <p class="form-field form-field-wide">
      <label for="igs_preparation_date"><?php _e( 'Preparation Date', 'igs-client-system' ); ?>:</label>
      <input
        type="text"
        id="igs_preparation_date"
        name="igs_preparation_date"
        class="date-picker"
        value="<?php echo esc_attr( $display ); ?>"
        placeholder="YYYY-MM-DD"
        autocomplete="off"
        maxlength="10"
        pattern="\d{4}-\d{2}-\d{2}"
      >
    </p>
    <?php if ( '1' === $order->get_meta( '_igs_prepaid' ) ) : ?>
      <div class="form-field form-field-wide">
        <h2 class="woocommerce-order-data__heading" style="color: red; text-transform: uppercase;"><?php _e( 'This order is prepaid', 'igs-client-system' ); ?></h2>
        <p class="form-field form-field-wide order_number">
          <?php _e( 'The customer has prepaid this order. When creating a waybill, select Sender as the payer if you want the shipping cost to be covered by you.', 'igs-client-system' ); ?>
        </p>
      </div>
    <?php endif; ?>
    <?php

  }

  /**
   * Save the preparation date for classic (CPT) orders.
   *
   * @since 1.0.0
   * @param int $order_id
   * @return void
   */
  public function save_preparation_date( $order_id ) {

    if ( ! isset( $_POST['igs_preparation_date'] ) ) {
      return;
    }

    $raw        = sanitize_text_field( wp_unslash( $_POST['igs_preparation_date'] ) );
    $new_status = isset( $_POST['order_status'] ) ? sanitize_text_field( wp_unslash( $_POST['order_status'] ) ) : '';

    if ( 'wc-cooking' === $new_status && '' === trim( $raw ) ) {
      WC_Admin_Meta_Boxes::add_error(
        __( 'Preparation date is required for orders with "Cooking" status.', 'igs-client-system' )
      );
      return;
    }

    $dt = DateTime::createFromFormat( 'Y-m-d', $raw );

    if ( $dt ) {
      update_post_meta( $order_id, self::META_KEY, $dt->format( 'Y-m-d' ) );
    }

  }

  /**
   * Save the preparation date for HPOS orders.
   *
   * Fires before the order object is persisted, so we update the meta
   * directly on the order object (no separate DB call needed).
   *
   * @since 1.0.0
   * @param WC_Order $order
   * @return void
   */
  public function save_preparation_date_hpos( $order ) {

    if ( ! isset( $_POST['igs_preparation_date'] ) ) {
      return;
    }

    $raw = sanitize_text_field( wp_unslash( $_POST['igs_preparation_date'] ) );

    // 'cooking' = status without 'wc-' prefix (how WC_Order::get_status() returns it).
    if ( 'cooking' === $order->get_status() && '' === trim( $raw ) ) {
      WC_Admin_Meta_Boxes::add_error(
        __( 'Preparation date is required for orders with "Cooking" status.', 'igs-client-system' )
      );
      return;
    }

    $dt = DateTime::createFromFormat( 'Y-m-d', $raw );

    if ( $dt ) {
      $order->update_meta_data( self::META_KEY, $dt->format( 'Y-m-d' ) );
    }

  }

  /**
   * Add the "Preparation Date" column to the WC orders list table.
   * Works for both classic (CPT) and HPOS list tables.
   *
   * @since 1.0.0
   * @param array $columns
   * @return array
   */
  public function add_preparation_date_column( $columns ) {

    $new = array();

    foreach ( $columns as $key => $label ) {
      $new[ $key ] = $label;
      // Insert after the order date column.
      if ( 'order_date' === $key ) {
        $new['igs_preparation_date'] = __( 'Preparation Date', 'igs-client-system' );
        $new['igs_returned_orders']  = __( 'Uncollected Orders', 'igs-client-system' );
        $new['igs_prepaid']          = __( 'Prepaid', 'igs-client-system' );
      }
    }

    return $new;

  }

  /**
   * Render the preparation date cell – classic (CPT) orders list.
   *
   * @since 1.0.0
   * @param string $column
   * @param int    $post_id
   * @return void
   */
  public function render_preparation_date_column( $column, $post_id ) {

    if ( 'igs_preparation_date' !== $column ) {
      return;
    }

    $this->output_preparation_date( wc_get_order( $post_id ) );

  }

  /**
   * Render the preparation date cell – HPOS orders list.
   *
   * @since 1.0.0
   * @param string   $column
   * @param WC_Order $order
   * @return void
   */
  public function render_preparation_date_column_hpos( $column, $order ) {

    if ( 'igs_preparation_date' !== $column ) {
      return;
    }

    $this->output_preparation_date( $order );

  }

  /**
   * Echo the formatted preparation date for an order, or a dash if not set.
   *
   * @since 1.0.0
   * @param WC_Order|false $order
   * @return void
   */
  private function output_preparation_date( $order ) {

    if ( ! $order ) {
      echo '&mdash;';
      return;
    }

    $stored = $order->get_meta( self::META_KEY );

    if ( ! $stored ) {
      echo '&mdash;';
      return;
    }

    $dt = DateTime::createFromFormat( 'Y-m-d', $stored );
    echo $dt ? esc_html( $dt->format( 'd.m.Y' ) ) : '&mdash;';

  }

  /**
   * Render the uncollected-orders cell – classic (CPT) orders list.
   *
   * @since 1.0.0
   * @param string $column
   * @param int    $post_id
   * @return void
   */
  public function render_returned_orders_column( $column, $post_id ) {

    if ( 'igs_returned_orders' !== $column ) {
      return;
    }

    $this->output_returned_orders( wc_get_order( $post_id ) );

  }

  /**
   * Render the uncollected-orders cell – HPOS orders list.
   *
   * @since 1.0.0
   * @param string   $column
   * @param WC_Order $order
   * @return void
   */
  public function render_returned_orders_column_hpos( $column, $order ) {

    if ( 'igs_returned_orders' !== $column ) {
      return;
    }

    $this->output_returned_orders( $order );

  }

  /**
   * Echo the count of wc-returned orders for the customer attached to an order.
   *
   * Results are cached per customer ID for the duration of the request so the
   * list table never fires more than one DB query per unique customer.
   *
   * @since 1.0.0
   * @param WC_Order|false $order
   * @return void
   */
  private function output_returned_orders( $order ) {

    if ( ! $order ) {
      echo '&mdash;';
      return;
    }

    $customer_id = $order->get_customer_id();

    if ( ! $customer_id ) {
      echo '&mdash;';
      return;
    }

    $cache_key = 'igs_returned_' . $customer_id;
    $count     = wp_cache_get( $cache_key, 'igs_cs' );

    if ( false === $count ) {
      $ids   = wc_get_orders( array(
        'customer_id' => $customer_id,
        'status'      => array( 'wc-returned' ),
        'limit'       => -1,
        'return'      => 'ids',
      ) );
      $count = count( $ids );
      wp_cache_set( $cache_key, $count, 'igs_cs', 300 );
    }

    if ( $count > 0 ) {
      printf(
        '<span style="color:red;font-weight:bold;" title="%s">%d</span>',
        esc_attr( sprintf( _n( '%d uncollected order', '%d uncollected orders', $count, 'igs-client-system' ), $count ) ),
        (int) $count
      );
    } else {
      echo '&mdash;';
    }

  }

  /**
   * Render the prepaid cell – classic (CPT) orders list.
   *
   * @since 1.0.0
   * @param string $column
   * @param int    $post_id
   * @return void
   */
  public function render_prepaid_column( $column, $post_id ) {

    if ( 'igs_prepaid' !== $column ) {
      return;
    }

    $this->output_prepaid( wc_get_order( $post_id ) );

  }

  /**
   * Render the prepaid cell – HPOS orders list.
   *
   * @since 1.0.0
   * @param string   $column
   * @param WC_Order $order
   * @return void
   */
  public function render_prepaid_column_hpos( $column, $order ) {

    if ( 'igs_prepaid' !== $column ) {
      return;
    }

    $this->output_prepaid( $order );

  }

  /**
   * Echo a visual indicator for whether the order was created as prepaid.
   *
   * @since 1.0.0
   * @param WC_Order|false $order
   * @return void
   */
  private function output_prepaid( $order ) {

    if ( ! $order ) {
      echo '&mdash;';
      return;
    }

    if ( '1' === $order->get_meta( '_igs_prepaid' ) ) {
      printf(
        '<span style="color:green;font-weight:bold;" title="%s">&#10003;</span>',
        esc_attr( __( 'Prepaid', 'igs-client-system' ) )
      );
    } else {
      echo '&mdash;';
    }

  }

  /**
   * AJAX handler: search products of type subscription / variable-subscription.
   *
   * For variable-subscription parents the response contains each individual
   * variation so the user can pick the exact variant.
   *
   * Response: { "id": "Formatted product name", ... }
   *
   * @since 1.0.0
   * @return void
   */
  public function ajax_search_subscription_products() {

    check_ajax_referer( 'search-products', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      wp_die( -1 );
    }

    $term = isset( $_GET['term'] ) ? wc_clean( wp_unslash( $_GET['term'] ) ) : '';

    if ( '' === $term ) {
      wp_send_json( array() );
    }

    $results = array();

    // --- Simple subscriptions ---
    $sub_ids = wc_get_products( array(
      's'      => $term,
      'type'   => 'subscription',
      'status' => 'publish',
      'limit'  => 20,
      'return' => 'ids',
    ) );

    foreach ( $sub_ids as $id ) {
      $product = wc_get_product( $id );
      if ( $product ) {
        $results[ $id ] = wp_strip_all_tags( $product->get_formatted_name() );
      }
    }

    // --- Variable subscriptions: search parent, include all variations ---
    $var_ids = wc_get_products( array(
      's'      => $term,
      'type'   => 'variable-subscription',
      'status' => 'publish',
      'limit'  => 10,
      'return' => 'ids',
    ) );

    foreach ( $var_ids as $parent_id ) {
      $parent = wc_get_product( $parent_id );
      if ( ! $parent ) continue;

      foreach ( $parent->get_children() as $variation_id ) {
        $variation = wc_get_product( $variation_id );
        if ( $variation && $variation->exists() ) {
          $results[ $variation_id ] = wp_strip_all_tags( $variation->get_formatted_name() );
        }
      }
    }

    wp_send_json( $results );

  }

}

IGS_CS_Admin_Order_Hooks::instance();
