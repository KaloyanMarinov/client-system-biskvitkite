<?php
/**
 * The Admin List Subscription Component.
 *
 * @package    IGS
 * @subpackage IGS/Admin/Components
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Export {

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


  public function __construct() {
  }

  /**
   * Render the filter template.
   */
  public function get_filter( $export_page ) {

    igs_cs_get_template( 'admin/part/export-filter', array(
      'export_page' => $export_page,
      'module'      => $this
    ) );

  }

  /**
   * Render the filter status template.
   */
  public function get_filter_sub_status() {

    if ( ! $statuses = wcs_get_subscription_statuses() )
      return;

    $statuses = apply_filters( 'igs_cs_filter_statuses', $statuses, $this );

    $counts = (array) wp_count_posts( 'shop_subscription' );

    foreach (array_keys($statuses) as $key) {
      if ( $key !== 'any' && ( ! isset( $counts[ $key ] ) || $counts[ $key ] == 0 ) ) {
        unset( $statuses[ $key ] );
      }
    }

    $selected = 'wc-active';

    igs_cs_get_template( 'admin/part/filter-select', array(
      'label'    => __('Status', 'igs-client-system'),
      'name'     => 'igs_status',
      'options'  => $statuses,
      'selected' => $selected,
      'class'    => 'igs_export_sub_status'
    ) );

  }

  public function get_filter_orders_status() {

    if ( ! $statuses = wc_get_order_statuses() )
      return;

    $selected = 'wc-processing';

    igs_cs_get_template( 'admin/part/filter-select', array(
      'label'    => __('Status', 'igs-client-system'),
      'name'     => 'status',
      'options'  => $statuses,
      'selected' => $selected,
    ) );

  }

  /**
   * Render the filter next date template.
   */
  public function get_filter_next_date() {

    $next_days = array(
      'today'      => __('Today', 'igs-client-system'),
      'this_week'  => __('This week', 'igs-client-system'),
      'next_week'  => __('Next week', 'igs-client-system'),
      'this_month' => __('This Month', 'igs-client-system')
    );

    $next_days = apply_filters( 'igs_cs_filter_next_days', $next_days, $this );
    $selected  = 'this_month';

    igs_cs_get_template( 'admin/part/filter-select', array(
      'label'    => _x('Next Date', 'filter', 'igs-client-system'),
      'name'     => 'igs_next_date',
      'options'  => $next_days,
      'selected' => $selected,
      'class'    => 'igs_export_sub_period'
    ) );

  }

  /**
   * Render the filter orders type template.
   */
  public function get_filter_order_type() {

    $options = array(
      'full'  => __('Full', 'igs-client-system'),
      'short' => __('Short', 'igs-client-system'),
    );

    $options  = apply_filters( 'igs_cs_filter_order_types', $options, $this );
    $selected = 'short';

    igs_cs_get_template( 'admin/part/filter-select', array(
      'label'    => _x('Type Export', 'filter', 'igs-client-system'),
      'name'     => 'igs_order_type',
      'options'  => $options,
      'selected' => $selected,
    ) );

  }

  public function igs_display_admin_notices() {

    if ( isset( $_GET['export_error'] ) ) {
      echo '<div class="d-f f-c gy-10 mb-30">';

      if ( $_GET['export_error'] == 'no_results' ) {
        $error = __('No found results', 'igs-client-system');
      }

      wp_admin_notice( $error, array( 'type' => 'error', 'dismissible' => false ) );

      echo '</div>';
    }
  }

  public function igs_get_shipping_addres() {
    return 'address';
  }
}
