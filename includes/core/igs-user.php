<?php

/**
 *
 * @link       https://igamingsolutions.com
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Core
 *
 */

defined('ABSPATH') || exit;

class IGS_CS_User {

  /**
   * ID for this object.
   *
   * @since 1.0.0
   * @var int
   */
  protected $id;

  /**
   * Post Data for this object.
   *
   * @since 1.0.0
   * @var WP_User
   */
  protected $data;

  /**
   * Get user data from cache or load it if not cached.
   *
   * @since 1.0.0
   * @param int $user_id User ID.
   * @return WP_User|false User object or false if not found.
   */
  protected function igs_set_user_data() {

    $user_meta = $this->igs_get_user_meta();

    $socials = array();

    foreach ($user_meta as $key => $value) {
      if ( str_starts_with($key, 'billing_') || in_array( $key, $socials ) ) {
        $this->set_prop($key, $value[0]);
      }

      if ( $key == 'price_list' ) {
        $this->set_prop($key, $value[0]);
      }

    }

  }

  /**
   * Get the user if ID is passed, otherwise the product is new and empty.
   *
   * @param int|WP_User|object $user User to init.
   */
  public function __construct( $user = 0 ) {
    $cache_key     = 'igs_user_' . ( is_object( $user ) ? $user->ID : $user ) . '_';
    $cached_object = wp_cache_get( $cache_key, 'igs_user' );

    if ( false !== $cached_object && $cached_object instanceof self ) {

      foreach ( get_object_vars( $cached_object ) as $prop => $value ) {
        $this->{$prop} = $value;
      }

      return;
    }

    if (is_numeric($user) && $user > 0) {
      $this->igs_set_id($user);
      $user = get_user_by('ID', $user);
    } elseif ($user instanceof self) {
      $this->igs_set_id($user->id);
    } elseif ( ! empty( $user ) ) {
      $this->set_id($user->ID);
    }

    $this->igs_set_data($user);
    $this->igs_set_user_data();

    $base_expiration = MONTH_IN_SECONDS;
    $random_offset   = mt_rand( 0, DAY_IN_SECONDS );
    $expiration_time = $base_expiration + $random_offset;
    wp_cache_set( $cache_key, $this, 'igs_user', $expiration_time );

  }

  /**
   * Prefix for action and filter hooks on data.
   *
   * @since 1.0.0
   * @return string
   */
  protected function igs_get_hook_prefix( $type = 'data' ) {
    return 'igs_get_user_' . $type . '_';
  }

  /**
   * Sets a prop for a setter method.
   *
   * This stores changes in a special array so we can track what needs saving
   * the DB later.
   *
   * @since 3.0.0
   * @param string $prop Name of prop to set.
   * @param mixed  $value Value of the prop.
   */
  protected function set_prop($prop, $value) {
    $this->data->$prop = $value;
  }

  /**
   * Set ID.
   *
   * @since 1.0.0
   * @param int $id ID.
   */
  public function igs_set_id($id) {
    $this->id = absint($id);
  }

  /**
   * Set data.
   *
   * @since 1.0.0
   * @param WP_User $user.
   */
  public function igs_set_data($user) {
    $this->data = $user;
  }

  /**
   * Get User Meda.
   *
   * @since 1.0.0
   * @return mixed;
   *
   */
  public function igs_get_user_meta() {

    $value = get_user_meta( $this->igs_get_id() );

    return apply_filters( $this->igs_get_hook_prefix( 'meta' ), $value, $this );

  }

  /**
   * Get Property
   *
   * @since 1.0.0
   * @param string $prop.
   * @return string;
   */
  public function igs_get_prop($prop) {
    return apply_filters($this->igs_get_hook_prefix() . $prop, $this->data->get($prop), $this);
  }

  /**
   * Returns the unique ID for this object.
   *
   * @since 1.0.0
   * @return int
   */
  public function igs_get_id() {
    return $this->id;
  }

  /**
   * Returns the date registered for this object.
   *
   * @since 1.0.0
   * @return int
   */
  public function date_registered( $format= null ) {

    if ( empty( $this->igs_get_prop( 'user_registered' ) ) )
      return;

    if ( ! $format ) {
      $format = get_option( 'date_format' );
    }

    $timestamp = strtotime( $this->igs_get_prop( 'user_registered' ) );

    return date_i18n( $format, $timestamp );

  }

  /**
   * Returns the author first name.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_first_name() {
    return $this->igs_get_prop('first_name');
  }

  /**
   * Returns the author last name.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_last_name() {
    return $this->igs_get_prop('last_name');
  }

  /**
   * Returns the author email address.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_email() {
    return $this->igs_get_prop('user_email');
  }

  /**
   * Returns the post title for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_name() {
    return $this->igs_get_first_name() . ' ' . $this->igs_get_last_name();
  }

  /**
   * Returns the user display name for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_display_name() {
    return $this->igs_get_prop('display_name');
  }

  /**
   * Returns the author abbr for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_abbr() {
    return mb_substr($this->igs_get_first_name(), 0, 1) . mb_substr($this->igs_get_last_name(), 0, 1);
  }

  /**
   * Returns the user billing first name for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_billing_first_name() {
    return $this->igs_get_prop('billing_first_name');
  }

  /**
   * Returns the user billing last name for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_billing_last_name() {
    return $this->igs_get_prop('billing_last_name');
  }

  /**
   * Returns the user billing name for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_billing_name() {
    return $this->igs_get_billing_first_name() . ' ' . $this->igs_get_billing_last_name();
  }

  /**
   * Returns the user billing email for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_billing_email() {
    return $this->igs_get_prop('billing_email');
  }

  /**
   * Returns the user billing phone for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_billing_phone() {
    return $this->igs_get_prop('billing_phone');
  }

  public function igs_get_price_list() {

    return $this->igs_get_prop('price_list');

  }

  public function igs_get_price_list_label() {

    if ( ! $term_id = $this->igs_get_price_list() )
      return __('Standard', 'igs-client-system');

    $price_list = get_term_by( 'term_id', $term_id, 'product_prices_list' );

    return $price_list->name;

  }

  /**
   * Returns the user has subscription for this object.
   *
   * @since 1.0.0
   * @return boolean
   */
  public function igs_has_subscription() {

    return wcs_user_has_subscription( $this->igs_get_id() );

  }

  /**
   * Returns the user has active subscription for this object.
   *
   * @since 1.0.0
   * @return boolean
   */
  public function igs_has_active_subscription() {

    if ( wcs_user_has_subscription( $this->igs_get_id(), '', 'active' ) ) {
      return __('Yes', 'igs-client-system');
    } else {
      return __('No', 'igs-client-system');
    }

  }

  /**
   * Returns the user subscriptions for this object.
   *
   * @since 1.0.0
   * @return boolean
   */
  public function igs_get_subscriptions() {

    $subscirptions = new IGS_CS_Subscriptions_Query(array(
      'igs_type'     => '3',
      'igs_customer' => $this->igs_get_id(),
      'igs_per_page' => '',
      'igs_results'  => '',
      'igs_status'   => 'any',
      'igs_orderby'  => 'date'
    ));

    return $subscirptions->igs_get_query();

  }

  /**
   * Returns the total orders for this object.
   *
   * @since 1.0.0
   * @return int
   */
  public function igs_order_count() {

    return wc_get_customer_order_count( $this->igs_get_id() );

  }

  public function igs_get_orders() {

    $orders = wc_get_orders( array(
      'customer_id' => $this->igs_get_id(),
      'limit'       => -1,
      'status'      => 'any',
    ) );

    return $orders;
  }

  public function igs_order_returned_count() {

    $args = array(
      'customer' => $this->igs_get_id(),
      'status'   => 'wc-returned',
      'return'   => 'ids',
      'limit'    => -1,
    );

    $orders = wc_get_orders($args);

    return count($orders);
  }

  /**
   * Returns the author description for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_description() {
    return $this->igs_get_prop('user_description');
  }

  /**
   * Returns the author link for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_link() {
    return get_author_posts_url($this->igs_get_id());
  }

  public function igs_get_edit() {

    return wp_sprintf( '<a %2$s>%1$s</a>',
      igs_cs_get_template_html( 'icons/edit' ),
      igs_cs_html_attributes(array(
        'href'  => admin_url( 'admin.php?page='. IGS_CS()->admin()->menus()->get_customer_slug() .'&action=edit&user_id=' . $this->igs_get_id() ),
        'class' => 'f-a button tc-w bg-b bg-h-2 px-10',
      ))
    );

  }

  public function igs_get_edit_button() {

    return wp_sprintf( '<a %3$s>%1$s %2$s</a>',
      __('Edit', 'igs-client-system'),
      igs_cs_get_template_html( 'icons/edit' ),
      igs_cs_html_attributes(array(
        'href'  => admin_url( 'admin.php?page='. IGS_CS()->admin()->menus()->get_customer_slug() .'&action=edit&user_id=' . $this->igs_get_id() ),
        'class' => 'f-a button tc-w bg-b bg-h-2 px-10',
      ))
    );

  }

  public function igs_get_orders_table() {

    igs_cs_get_template( 'admin/part/user-orders-table', array(
      'orders' => $this->igs_get_orders()
    ) );

  }

  public function igs_get_subscription_table() {

    igs_cs_get_template( 'admin/part/user-subscriptions-table', array(
      'subscriptions' => $this->igs_get_subscriptions()
    ) );

  }

  /**
   * Setup user data globally.
   *
   * @since 1.0.0
   * @return self
   */
  public static function igs_setup_user_data( $user_id = null ) {

    global $user;

    if ( $user_id && is_a( $user, IGS_CS_User::class ) && $user->get_id() === $user_id )
      return $user;

    unset($GLOBALS['user']);

    if ( ! $user_id ) {
      $user_id = get_current_user_id();
    }

    if ( ! $user_id )
      return;

    $GLOBALS['user'] = new self($user_id);

    return $GLOBALS['user'];

  }

  public static function igs_handle_save_user() {

    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die(__('You do not have sufficient permissions to access this page.', 'igs-client-system'));
    }

    check_admin_referer( 'igs_user_save_action', 'igs_user_save_nonce' );

    $user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
    $user    = get_userdata( $user_id );

    if ( ! $user ) {
      wp_die( esc_html__( 'Customer not found.', 'igs-client-system' ) );
    }

    $errors   = array();
    $base_url = admin_url( 'admin.php?page='. IGS_CS()->admin()->menus()->get_customer_slug() .'&action=edit&user_id=' . $user_id);

    $input_data['first_name']    = sanitize_text_field( $_POST['first_name'] );
    $input_data['last_name']     = sanitize_text_field( $_POST['last_name'] );
    $input_data['billing_phone'] = sanitize_text_field( $_POST['billing_phone'] );
    $input_data['email']         = sanitize_email( $_POST['email'] );
    $input_data['price_list']    = $_POST['price_list'];

    if ( empty( $input_data['first_name'] ) ) $errors[] = 'first_name';
    if ( empty( $input_data['last_name'] ) )  $errors[] = 'last_name';
    if ( empty( $input_data['billing_phone'] ) ) $errors[] = 'phone_number';

    if ( empty( $input_data['email'] ) ) {
      $errors[] = 'email_required';
    } elseif ( ! is_email( $input_data['email'] ) ) {
      $errors[] = 'email_invalid';
    } else {
      $existing_user_id = email_exists( $input_data['email'] );
      if ( $existing_user_id && $existing_user_id !== $user_id ) {
        $errors[] = 'email_exists';
      }
    }

    if ( ! empty( $errors ) ) {
      set_transient('igs_edit_user_data_' . $user_id, $input_data, 300);
      wp_safe_redirect( add_query_arg( 'errors', implode( ',', $errors ), $base_url ) );
      exit;
    }

    wp_update_user( array(
      'ID'           => $user_id,
      'first_name'   => $input_data['first_name'],
      'last_name'    => $input_data['last_name'],
      'user_email'   => $input_data['email'],
    ) );

    update_user_meta( $user_id, 'billing_phone', $input_data['billing_phone'] );
    update_user_meta( $user_id, 'price_list', $input_data['price_list'] );

    wp_safe_redirect( add_query_arg( 'updated', '1', $base_url ) );
    exit;

  }
}
