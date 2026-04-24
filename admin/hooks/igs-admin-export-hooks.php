<?php

/**
 * Admin Export Hooks.
 *
 * Handles the two admin-post actions that stream Excel exports:
 * - Subscriptions export (grouped by date when status is active)
 * - Orders export
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Admin/Hooks
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Admin_Export_Hooks extends IGS_CS_Loader {

  /**
   * @var IGS_CS_Admin_Export_Hooks|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Admin_Export_Hooks
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
    $this->add_action( 'admin_post_igs_export_subscriptions', $this, 'handle_subscriptions_export', 10 );
    $this->add_action( 'admin_post_igs_export_orders',        $this, 'handle_orders_export',        10 );
  }

  /**
   * Stream a subscriptions XLS export.
   *
   * When the status filter is 'wc-active' the subscriptions are grouped by
   * next-payment date.  All other statuses produce a flat list.
   *
   * @since 1.0.0
   * @return void
   */
  public static function handle_subscriptions_export() {

    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die( __( 'You do not have sufficient permissions to access this page.', 'igs-client-system' ) );
    }

    check_admin_referer( 'igs_export_subscriptions_action', 'igs_export_subscriptions_nonce' );

    $defaults = array(
      'igs_type'     => '3',
      'igs_per_page' => '',
      'igs_results'  => '',
      'igs_order'    => 'ASC',
    );

    $params = wp_parse_args( $_POST, $defaults );

    if ( $params['igs_status'] !== 'wc-active' ) {
      $params['igs_next_date'] = '';
      $params['igs_orderby']   = 'date';
    }

    $params    = apply_filters( 'igs_export_subscription_params', $params );
    $igs_query = ( new IGS_CS_Subscriptions_Query( $params ) )->igs_get_query();

    if ( ! $igs_query->have_posts() ) {
      wp_redirect( add_query_arg( array(
        'page'         => IGS_CS()->admin()->menus()->get_export_slug(),
        'export_error' => 'no_results',
      ), admin_url( 'admin.php' ) ) );
      exit;
    }

    if ( $params['igs_status'] === 'wc-active' ) {

      $meta_query = $igs_query->query['meta_query'];
      $date_range = array();

      foreach ( $meta_query as $clause ) {
        if ( isset( $clause['key'] ) && '_schedule_next_payment' === $clause['key'] ) {
          $date_range = $clause['value'];
          break;
        }
      }

      if ( empty( $date_range ) ) {
        wp_die( 'Could not determine date range from query. Please contact the administrator.' );
      }

      $data_format  = 'd.m.Y';
      $begin        = new DateTime( ( new DateTime( $date_range[0] ) )->format( $data_format ) );
      $finish       = new DateTime( ( new DateTime( $date_range[1] ) )->format( $data_format ) );
      $finish->modify( '+1 day' );

      $grouped_data = array();
      foreach ( new DatePeriod( $begin, new DateInterval( 'P1D' ), $finish ) as $date ) {
        $grouped_data[ $date->format( $data_format ) ] = array();
      }

      foreach ( $igs_query->get_posts() as $id ) {
        $sub          = new IGS_CS_Subscription( $id );
        $next_payment = $sub->igs_get_next_date( $data_format );

        if ( $next_payment && isset( $grouped_data[ $next_payment ] ) ) {
          $grouped_data[ $next_payment ][] = $sub;
        }
      }

      igs_cs_get_template( 'admin/part/export-active-subscirptions-xls', array(
        'grouped_data' => $grouped_data,
      ) );

    } else {

      igs_cs_get_template( 'admin/part/export-subscirptions-xls', array(
        'igs_query' => $igs_query,
      ) );

    }

    exit;

  }

  /**
   * Resolve a period key into a date_created range string for wc_get_orders().
   *
   * Returns an empty string when the period is 'all' or unrecognised.
   *
   * @since 1.0.0
   * @param string $period  One of: all, this_week, this_month, this_year, last_month, last_year.
   * @return string  Date range string "YYYY-MM-DD...YYYY-MM-DD" or empty.
   */
  private static function get_period_date_range( $period ) {

    $now = new DateTime( 'now', new DateTimeZone( wp_timezone_string() ) );

    switch ( $period ) {

      case 'this_week':
        $start = clone $now;
        $start->modify( 'monday this week' );
        $end = clone $now;
        $end->modify( 'sunday this week' );
        break;

      case 'this_month':
        $start = new DateTime( $now->format( 'Y-m-01' ), new DateTimeZone( wp_timezone_string() ) );
        $end   = new DateTime( $now->format( 'Y-m-t' ),  new DateTimeZone( wp_timezone_string() ) );
        break;

      case 'this_year':
        $start = new DateTime( $now->format( 'Y' ) . '-01-01', new DateTimeZone( wp_timezone_string() ) );
        $end   = new DateTime( $now->format( 'Y' ) . '-12-31', new DateTimeZone( wp_timezone_string() ) );
        break;

      case 'last_month':
        $first_of_this = new DateTime( $now->format( 'Y-m-01' ), new DateTimeZone( wp_timezone_string() ) );
        $start = clone $first_of_this;
        $start->modify( '-1 month' );
        $end = clone $first_of_this;
        $end->modify( '-1 day' );
        break;

      case 'last_year':
        $last_year = (int) $now->format( 'Y' ) - 1;
        $start     = new DateTime( $last_year . '-01-01', new DateTimeZone( wp_timezone_string() ) );
        $end       = new DateTime( $last_year . '-12-31', new DateTimeZone( wp_timezone_string() ) );
        break;

      default:
        return '';

    }

    return $start->format( 'Y-m-d' ) . '...' . $end->format( 'Y-m-d' );

  }

  /**
   * Stream an orders XLS export.
   *
   * @since 1.0.0
   * @return void
   */
  public static function handle_orders_export() {

    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die( __( 'You do not have sufficient permissions to access this page.', 'igs-client-system' ) );
    }

    check_admin_referer( 'igs_export_orders_action', 'igs_export_orders_nonce' );

    $defaults = array(
      'limit'  => -1,
      'return' => 'ids',
    );

    $params = wp_parse_args( $_POST, $defaults );

    $status   = isset( $params['status'] )           ? $params['status']                                  : '';
    $period   = isset( $params['igs_order_period'] ) ? sanitize_text_field( $params['igs_order_period'] ) : 'all';
    $date_raw = isset( $params['igs_order_date'] )   ? sanitize_text_field( $params['igs_order_date'] )   : '';

    if ( 'date' === $period ) {

      // Single-date export: convert d.m.Y → Y-m-d, then filter by the right field.
      $filter_date = '';
      if ( $date_raw ) {
        $dt = DateTime::createFromFormat( 'd.m.Y', $date_raw );
        if ( $dt ) {
          $filter_date = $dt->format( 'Y-m-d' );
        }
      }

      if ( $filter_date ) {
        if ( 'wc-cooking' === $status ) {
          // wc-cooking: filter by preparation date meta.
          $params['meta_query'] = array(
            array(
              'key'     => '_igs_preparation_date',
              'value'   => $filter_date,
              'compare' => '=',
            ),
          );
        } else {
          // All other statuses: filter by order creation date (full day).
          $params['date_created'] = $filter_date . '...' . $filter_date . ' 23:59:59';
        }
      }

    } elseif ( 'all' !== $period ) {

      // Named period (this_week, this_month, etc.) — applies to all statuses.
      $date_range = self::get_period_date_range( $period );
      if ( $date_range ) {
        $params['date_created'] = $date_range;
      }

    }

    // Remove our custom fields before passing to wc_get_orders().
    unset( $params['igs_order_period'], $params['igs_order_date'] );

    $params = apply_filters( 'igs_export_orders_params', $params );
    $order_ids = wc_get_orders( $params );

    if ( ! $order_ids ) {
      wp_redirect( add_query_arg( array(
        'page'         => IGS_CS()->admin()->menus()->get_export_slug(),
        'export_error' => 'no_results',
      ), admin_url( 'admin.php' ) ) );
      exit;
    }

    // Group orders by date into separate sheets.
    // wc-cooking → group by _igs_preparation_date; all others → group by date_created.
    $grouped_orders = array();
    foreach ( $order_ids as $order_id ) {
      $order = wc_get_order( $order_id );
      if ( ! $order ) continue;

      if ( 'wc-cooking' === $status ) {
        $raw      = $order->get_meta( '_igs_preparation_date' );
        $sort_key = ( $raw && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) ? $raw : '0000-00-00';
      } else {
        $sort_key = $order->get_date_created()->format( 'Y-m-d' );
      }

      $grouped_orders[ $sort_key ][] = $order;
    }
    ksort( $grouped_orders );

    igs_cs_get_template( 'admin/part/export-orders-xls', array(
      'grouped_orders' => $grouped_orders,
      'type'           => sanitize_text_field( $_POST['igs_order_type'] ),
      'status'         => $status,
    ) );

    exit;

  }

}

IGS_CS_Admin_Export_Hooks::instance();
