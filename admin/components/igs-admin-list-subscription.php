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
   * @var IGS_CS_Subscriptions_Query|null
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

      $this->igs_cs_query = new IGS_CS_Subscriptions_Query( $this->igs_get_params() );

    }

    return $this->igs_cs_query;

  }

  public function igs_get_params() {

    $defaults = array(
      'igs_type'    => '4',
      'igs_results' => '',
      'igs_order'   => 'ASC'
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

    $name     = 'igs_orderby';
    $label    = __('Sort by', 'igs-client-system');
    $selected = $this->get_param( $name );

    $options    = apply_filters( 'igs_cs_filter_sort', array(
      'date'      => __('Start Date', 'igs-client-system'),
      'next_date' => _x('Next Date', 'filter', 'igs-client-system'),
      'last_date' => __('Last Date', 'igs-client-system'),
    ), $this );

    igs_cs_get_template( 'admin/part/filter-select', array(
      'label'    => $label,
      'name'     => $name,
      'options'  => $options,
      'selected' => $selected,
      'class'    => ''
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
   * Render the filter client template.
   */
  public function get_filter_client() {

    $label       = __('Client', 'igs-client-system');
    $placeholder = esc_attr__( 'Search for a customer&hellip;', 'woocommerce-subscriptions' );
    $name        = 'igs_customer';
    $value       = $this->get_param( $name, '' ) ?: '';
    $selected   = '';

    if ( $value ) {
      $user     = get_user_by( 'id', $value );
      $selected = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) . ')';
    }

    igs_cs_get_template( 'admin/part/filter-user', array(
      'label'       => $label,
      'placeholder' => $placeholder,
      'name'        => $name,
      'selected'    => $selected,
      'value'       => $value
    ) );

  }


  /**
   * Render the pagination template.
   */
  public function get_pagination() {

    return igs_cs_get_pagination( $this->igs_cs_query->igs_get_max_num_pages() );

  }

  public function igs_display_admin_notices() {

    if ( isset( $_GET['updated'] ) ) {
      wp_admin_notice(
        __('Subscription data updated successfully.', 'igs-client-system'),
        array( 'type' => 'success', 'dismissible' => true )
      );
    }

    if ( isset( $_GET['errors'] ) ) {
      $error_codes = explode( ',', $_GET['errors'] );
      $map = array(
        'first_name'      => __('First Name is a required field.', 'igs-client-system'),
        'last_name'       => __('Last Name is a required field.', 'igs-client-system'),
        'phone_number'    => __('Phone number is a required field.', 'igs-client-system'),
        'email_required'  => __('Email address is a required field.', 'igs-client-system'),
        'email_invalid'   => __('The provided email format is invalid.', 'igs-client-system'),
        'invoice_company' => __('The Inovice Company name is a required field.', 'igs-client-system'),
        'invoice_mol'     => __('The Inovice Materially Responsible Person is a required field.', 'igs-client-system'),
        'invoice_eik'     => __('The Inovice UIC / Tax ID is a required field.', 'igs-client-system'),
        'invoice_town'    => __('The Inovice Town is a required field.', 'igs-client-system'),
        'invoice_address'  => __('The Inovice Address is a required field.', 'igs-client-system'),
        'deleted_products' => __('The subscription contains deleted products. Please select replacement products before saving.', 'igs-client-system'),
        'no_products'      => __('The subscription must contain at least one product.', 'igs-client-system'),
      );

      echo '<div class="d-f f-c gy-10">';
      foreach ( $error_codes as $code ) {
        if ( isset( $map[ $code ] ) ) {
          wp_admin_notice( $map[ $code ], array( 'type' => 'error', 'dismissible' => false ) );
        }
      }
      echo '</div>';
    }
  }
}
