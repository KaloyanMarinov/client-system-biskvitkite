<?php
/**
 * The Admin List Subscription Component.
 *
 * @package    IGS
 * @subpackage IGS/Admin/Components
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_List_Subscription {

  /**
   * The query handler instance.
   *
   * @since 1.0.0
   * @var IGS_CS_Query|null
   */
  protected $igs_cs_query = null;

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

    if ( is_null( $this->igs_cs_query ) ) {

      $this->igs_cs_query = new IGS_CS_Subscriptions_Query( $this->igs_get_param() );

    }

    return $this->igs_cs_query;

  }

  public function igs_get_param() {

    $defaults = array(
      'igs_type'     => '4',
      'igs_results'  => '',
    );

    return wp_parse_args( $_GET, $defaults );

  }

  /**
   * Get the query handler and execute the search.
   *
   * @since  1.0.0
   * @return IGS_CS_Subscriptions_Query
   */
  public function get_query() {

    return $this->igs_cs_query->igs_get_query();

  }

  public function get_param( $param ) {

    return $this->igs_cs_query->igs_get_param( $param );

  }

  /**
   * Get the query handler and execute the search.
   *
   * @since  1.0.0
   * @return int
   */
  public function no_found_rows() {

    igs_cs_get_template('admin/part/found-results', array('results' => $this->igs_cs_query->igs_get_found_posts()));

  }

  /**
   * Render the filter template.
   */
  public function get_filter( $subscription_page ) {

    igs_cs_get_template( 'admin/part/filter', array(
      'subscription_page' => $subscription_page,
      'module'            => $this
    ) );

  }

  /**
   * Render the filter Sort template.
   */
  public function get_filter_sort() {

    $sorts = apply_filters( 'igs_cs_filter_sort', array(
      'date'      => __('Start Date', 'igs-client-system'),
      'next_date' => __('Next Date', 'igs-client-system'),
      'last_date' => __('Last Date', 'igs-client-system'),
    ), $this );

    $selected = $this->get_param( 'igs_orderby' );

    igs_cs_get_template( 'admin/part/filter-sort', array(
      'sorts'   => $sorts,
      'selected' => $selected
    ) );

  }

  /**
   * Render the filter Order template.
   */
  public function get_filter_order() {

    $orders = apply_filters( 'igs_cs_filter_order', array(
      'ASC'  => __('Ascending', 'igs-client-system'),
      'DESC' => __('Descending', 'igs-client-system'),
    ), $this );

    $selected = $this->get_param( 'igs_order' );

    igs_cs_get_template( 'admin/part/filter-order', array(
      'orders'   => $orders,
      'selected' => $selected
    ) );

  }

  /**
   * Render the filter Per Page template.
   */
  public function get_filter_per_page() {

    $results_per_page = apply_filters( 'igs_cs_filter_per_page', array(8, 16, 24, 32, 40), $this );

    $selected = $this->get_param( 'igs_per_page' );

    igs_cs_get_template( 'admin/part/filter-results', array(
      'results_per_page' => $results_per_page,
      'selected'         => $selected
    ) );

  }


  /**
   * Render the filter status template.
   */
  public function get_filter_status() {

    if ( ! $statuses = wcs_get_subscription_statuses() )
      return;

    $statuses = apply_filters( 'igs_cs_filter_statuses', $statuses, $this );

    $counts = (array) wp_count_posts( 'shop_subscription' );

    foreach (array_keys($statuses) as $key) {
      if ( $key !== 'any' && ( ! isset( $counts[ $key ] ) || $counts[ $key ] == 0 ) ) {
        unset( $statuses[ $key ] );
      }
    }

    $selected = $this->get_param( 'igs_status' );

    igs_cs_get_template( 'admin/part/filter-status', array(
      'statuses' => $statuses,
      'selected' => $selected
    ) );

  }

  /**
   * Render the filter next date template.
   */
  public function get_filter_next_date() {

    $next_days = array(
      ''           => __('All', 'igs-client-system'),
      'today'      => __('Today', 'igs-client-system'),
      'this_week'  => __('This week', 'igs-client-system'),
      'next_week'  => __('Next week', 'igs-client-system'),
      'this_month' => __('This Month', 'igs-client-system'),
      'next_month' => __('Next Month', 'igs-client-system'),
      'delayed'    => __('Delayed', 'igs-client-system')
    );

    $next_days = apply_filters( 'igs_cs_filter_next_days', $next_days, $this );
    $selected = $this->get_param( 'igs_next_date' );

    igs_cs_get_template( 'admin/part/filter-next-date', array(
      'next_days' => $next_days,
      'selected'  => $selected
    ) );

  }

  /**
   * Render the filter number template.
   */
  public function get_filter_number() {

    $value = $this->get_param( 'igs_manually' );

    igs_cs_get_template( 'admin/part/filter-number', array(
      'value' => $value
    ) );

  }

  /**
   * Render the filter Client template.
   */
  public function get_filter_client() {

    $value = $this->get_param( 'igs_client' );

    igs_cs_get_template( 'admin/part/filter-client', array(
      'value' => $value
    ) );

  }

  /**
   * Render the pagination template.
   * * Подаваме нужните данни за страниците директно към темплейта.
   */
  public function get_pagination() {

    $query = $this->get_query();

    return igs_cs_get_pagination( $query );

  }
}
