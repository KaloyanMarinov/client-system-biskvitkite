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
   * Returns the total orders for this object.
   *
   * @since 1.0.0
   * @return int
   */
  public function igs_order_count() {

    return wc_get_customer_order_count( $this->igs_get_id() );

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

  public function igs_get_edit_button() {

    return wp_sprintf( '<a %3$s>%1$s %2$s</a>',
      __('Edit', 'igs-client-system'),
      igs_cs_get_template_html( 'icons/edit' ),
      igs_cs_html_attributes(array(
        'href'  => admin_url( 'admin.php?page=igs-clients&action=edit&user_id=' . $this->igs_get_id() ),
        'class' => 'f-a button tc-w bg-b bg-h-2 px-10',
      ))
    );

  }

  /**
   * Setup user data globally.
   *
   * @since 1.0.0
   * @return self
   */
  public static function igs_setup_user_data( $user_id ) {

    global $user;

    if ( $user && $user->igs_get_id() === $user_id )
      return $user;

    unset($GLOBALS['user']);

    $GLOBALS['user'] = new self($user_id);

    return $GLOBALS['user'];
  }
}
