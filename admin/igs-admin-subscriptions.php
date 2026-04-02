<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_CS/Admin
 * @author     igamingsolutions.com <support@igamingsolutions.com>
 *
 */
class IGS_CS_Admin_Subscriptions extends IGS_CS_Loader {

  /**
   * The single instance of the Admin class.
   *
   * @since 1.0.0
   * @access private
   * @static
   * @var IGS_CS_Admin_Subscriptions|null $_instance Singleton instance.
   */
  private static $_instance = null;

  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct( ) {

    $this->includes();

  }

  /**
   * Loads necessary admin-specific dependencies.
   *
   * @since 1.0.0
   */
  private function includes() {

    require_once IGS_CS_ABSPATH . '/admin/components/igs-admin-list-subscription.php';
    require_once IGS_CS_ABSPATH . '/admin/components/igs-admin-schedule.php';
    require_once IGS_CS_ABSPATH . '/admin/components/igs-admin-users.php';

  }

  /**
   * Get Subscriptions List Component.
   *
   * @since  1.0.0
   * @return IGS_CS_List_Subscription
   */
  public function get_list() {
    return IGS_CS_List_Subscription::instance();
  }

  /**
   * Get Shedule Component.
   *
   * @since  1.0.0
   * @return IGS_CS_Schedule
   */
  public function get_schedule() {
    return IGS_CS_Schedule::instance();
  }

  /**
   * Get Users Component.
   *
   * @since  1.0.0
   * @return IGS_CS_Users
   */
  public function get_users() {
    return IGS_CS_Users::instance();
  }

  /**
   * Function for `filter_subscription_statuses` filter-hook.
   *
   * @param array $statuses List of subscription statuses
   *
   * @return array
   */
  public static function filter_subscription_statuses( $statuses ) {

    unset( $statuses['wc-switched'] );
    unset( $statuses['wc-expired'] );
    unset( $statuses['wc-pending-cancel'] );

    return $statuses;

  }

}
