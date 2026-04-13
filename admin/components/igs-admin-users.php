<?php
/**
 * The Admin Users Component.
 *
 * @package    IGS
 * @subpackage IGS/Admin/Components
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Users {

  /**
   * The query handler instance.
   *
   * @since 1.0.0
   * @var IGS_CS_Users_Query |null
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

      $this->igs_cs_query = new IGS_CS_Users_Query ( $this->igs_get_params() );

    }

    return $this->igs_cs_query;

  }

  public function igs_get_params() {

    $defaults = array(
      'igs_count_total' => true
    );

    return wp_parse_args( $_GET, $defaults );

  }

  public function get_param( $param ) {

    return $this->igs_cs_query->igs_get_param( $param );

  }

  public function get_query() {

    return $this->igs_cs_query->igs_get_query();

  }


  /**
   * Get the query handler and execute the search.
   *
   * @since  1.0.0
   * @return int
   */
  public function no_found_rows() {

    igs_cs_get_template('admin/part/found-results', array('results' => $this->get_query()->get_total() ));

  }

  /**
   * Render the filter template.
   */
  public function get_filter( $users_page ) {

    igs_cs_get_template( 'admin/part/users-filter', array(
      'users_page' => $users_page,
      'module'     => $this
    ) );

  }

  /**
   * Render the filter Sort template.
   */
  public function get_filter_price_list() {

    $name     = 'igs_price_list';
    $label    = __('Price list', 'igs-client-system');
    $selected = $this->get_param( $name );
    $prices   = get_terms(array(
      'taxonomy'   => 'product_prices_list',
      'hide_empty' => false,
      'fields'     => 'id=>name'
    ));

    $options = array(
      ''  => __('All', 'igs-client-system' ),
      '0' => __('Standard', 'igs-client-system' )
    ) + $prices;

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

    $options = apply_filters( 'igs_cs_user_filter_number', array(8, 16, 24, 32, 40), $this );

    $selected = $this->get_param( 'igs_number' );

    igs_cs_get_template( 'admin/part/filter-results-number', array(
      'options'  => $options,
      'selected' => $selected
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
   * * Подаваме нужните данни за страниците директно към темплейта.
   */
  public function get_pagination() {

    $query = $this->get_query();

    $total_users = $query->get_total();
    $users_per_page = $query->get('number');

    $max_num_pages = ceil( $total_users / $users_per_page );

    return igs_cs_get_pagination( $max_num_pages );

  }

  public function igs_display_admin_notices() {

    if ( isset( $_GET['updated'] ) ) {
      wp_admin_notice(
        __('Customer data updated successfully.', 'igs-client-system'),
        array( 'type' => 'success', 'dismissible' => true )
      );
    }

    if ( isset( $_GET['errors'] ) ) {
      $error_codes = explode( ',', $_GET['errors'] );
      $map = array(
        'first_name'     => __('First Name is a required field.', 'igs-client-system'),
        'last_name'      => __('Last Name is a required field.', 'igs-client-system'),
        'phone_number'   => __('Phone number is a required field.', 'igs-client-system'),
        'email_required' => __('Email address is a required field.', 'igs-client-system'),
        'email_invalid'  => __('The provided email format is invalid.', 'igs-client-system'),
        'email_exists'   => __('This email is already registered to another client.', 'igs-client-system'),
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
