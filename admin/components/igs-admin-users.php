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
   * @var array|null
   */
  protected $users = null;

  /**
	 * Stores Params data.
	 *
	 * @var array
	 */
	protected $params = array();

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

    $this->params = $this->get_params();
    $this->users  = $this->get_users_only_subscription();

  }

  public function get_params() {

    $defaults = array(
      'igs_orderby'  => 'date',
      'igs_order'    => 'DESC',
      'igs_per_page' => 32,
    );

    return wp_parse_args( $_GET, $defaults );

  }

  /**
	 * Get the value of a params variable.
	 *
	 * @param string $param Params variable to get value for.
	 * @param mixed  $default Default value if query variable is not set.
	 * @return mixed Param variable value if set, otherwise default.
	 */
	public function get_param( $param, $default = null ) {

		if ( ! isset( $this->params[ $param ] ) || $this->params[ $param ] == '' )
			return $default;

		return $this->params[ $param ];

	}

  protected function get_users_only_subscription() {

    $cache_key    = 'igs_get_users' ;
    $cached_users = wp_cache_get( $cache_key, 'igs_users' );

    if ( false === $cached_users || ! is_array( $cached_users ) ) {

      global $wpdb;

      $cached_users = $wpdb->get_col( "
        SELECT DISTINCT meta_value
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_customer_user'
        AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'shop_subscription')
        AND meta_value > 0
      " );

      $base_expiration = MONTH_IN_SECONDS;
      $random_offset   = mt_rand( 0, DAY_IN_SECONDS );
      $expiration_time = $base_expiration + $random_offset;
      wp_cache_set( $cache_key, $cached_users, 'igs_users', $expiration_time );

    }

    return $cached_users;

  }

  public function get_users() {

    return $this->users;

  }

  /**
   * Get the query handler and execute the search.
   *
   * @since  1.0.0
   * @return int
   */
  public function no_found_rows() {

    igs_cs_get_template('admin/part/found-results', array('results' => count( $this->get_users() )));

  }

  public function get_user_by_page() {

    if ( ! $users = $this->get_users() )
      return;

    $paged    = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
    $per_page = 24;
    $offset   = $per_page * $paged;

    return array_slice( $users, $offset, $per_page );

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
   * Render the filter active subscribtion template.
   */
  public function get_filter_active_subscriber() {

    $options = array(
      ''    => __('All', 'igs-client-system'),
      'yes' => __('Yes', 'igs-client-system'),
      'no'  => __('No', 'igs-client-system'),
    );


    $options = apply_filters( 'igs_cs_get_filter_active_subscriber', $options, $this );

    $selected = $this->get_param( 'igs_active_subscriber' );

    igs_cs_get_template( 'admin/part/filter-user-active_subscriber', array(
      'options'  => $options,
      'selected' => $selected
    ) );

  }

  /**
   * Render the filter Sort template.
   */
  public function get_filter_sort() {

    $sorts = apply_filters( 'igs_cs_user_filter_sort', array(
      'name'   => __('Name', 'igs-client-system'),
      'date'   => __('Date registered', 'igs-client-system'),
      'orders' => __('Orders', 'igs-client-system'),
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
   * Render the filter name template.
   */
  public function get_filter_name() {

    $label       = __('Name', 'igs-client-system');
    $placeholder = esc_attr__( 'Enter name', 'igs-client-system' );
    $name        = 'igs_name';
    $value       = $this->get_param( $name );

    igs_cs_get_template( 'admin/part/filter-text', array(
      'label'       => $label,
      'placeholder' => $placeholder,
      'name'        => $name,
      'value'       => $value
    ) );

  }

  /**
   * Render the filter email template.
   */
  public function get_filter_email() {

    $label       = __('Email address', 'igs-client-system');
    $placeholder = esc_attr__( 'Enter e-mail', 'igs-client-system' );
    $name        = 'igs_email';
    $value       = $this->get_param( $name );

    igs_cs_get_template( 'admin/part/filter-text', array(
      'label'       => $label,
      'placeholder' => $placeholder,
      'name'        => $name,
      'value'       => $value
    ) );

  }

  /**
   * Render the filter phone template.
   */
  public function get_filter_phone() {

    $label       = __('Phone number', 'igs-client-system');
    $placeholder = esc_attr__( 'Enter phone number', 'igs-client-system' );
    $name        = 'igs_phone';
    $value       = $this->get_param( $name );

    igs_cs_get_template( 'admin/part/filter-text', array(
      'label'       => $label,
      'placeholder' => $placeholder,
      'name'        => $name,
      'value'       => $value
    ) );

  }

}
