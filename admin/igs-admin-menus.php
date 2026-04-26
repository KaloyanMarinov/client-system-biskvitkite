<?php

/**
 * Admin Menus Handler.
 *
 * Handles all admin menu registrations for the plugin.
 *
 * @since      1.0.0
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/admin
 */
class IGS_CS_Admin_Menus {

  /**
	 * The single instance of the class.
	 *
	 * @var IGS_CS_Admin_Menus
	 * @since 1.0.0
	 */
	private static $_instance = null;

  /**
   * Menu page slug for subscriptions.
   *
   * @since 1.0.0
   * @access private
   * @var string $subscriptions_slug Subscriptions menu slug.
   */
  public $subscriptions_slug = 'igs-subscriptions';

  /**
   * Menu page slug for schedule.
   *
   * @since 1.0.0
   * @access private
   * @var string $clients_slug schedule menu slug.
   */
  public $schedule_slug = 'igs-schedule';

  /**
   * Menu page slug for customers.
   *
   * @since 1.0.0
   * @access private
   * @var string $clients_slug Clients menu slug.
   */
  public $customer_slug = 'igs-customers';

  /**
   * Menu page slug for export.
   *
   * @since 1.0.0
   * @access private
   * @var string $export_slug Export menu slug.
   */
  public $export_slug = 'igs-export';

  /**
   * Menu page slug for new subscription.
   *
   * @since 1.0.0
   * @var string
   */
  public $new_subscription_slug = 'igs-new-subscription';

	/**
	 * Main IGS_CS_Admin_Menus Instance.
	 *
	 * Ensures only one instance of IGS_CS_Admin_Menus is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return IGS_CS_Admin_Menus - Main instance.
	 */
  public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
    return self::$_instance;
	}

  /**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
   *
	 */
	public function __construct( ) { }

  /**
   * Add main admin menu.
   *
   * @since 1.0.0
   * @access private
   */
  private function add_main_menu() {

    add_menu_page(
      _x('Client Software', 'menu', 'igs-client-system'),
      _x('Client Software', 'menu', 'igs-client-system'),
      'manage_options',
      'igs-client-software',
      '',
      'dashicons-cart',
      56
    );

  }

  /**
   * Add admin submenus.
   *
   * @since 1.0.0
   * @access private
   */
  private function add_submenus() {
    add_submenu_page(
      'igs-client-software',
      __('Subscriptions', 'igs-client-system'),
      __('Subscriptions', 'igs-client-system'),
      'manage_options',
      $this->get_subscriptions_slug(),
      array($this, 'igs_cs_subscription_page')
    );

    add_submenu_page(
      'igs-client-software',
      __('New Subscription', 'igs-client-system'),
      __('New Subscription', 'igs-client-system'),
      'manage_options',
      $this->get_new_subscription_slug(),
      array($this, 'igs_cs_new_subscription_page')
    );

    // add_submenu_page(
    //   'igs-client-software',
    //   __('Orders', 'woocommerce'),
    //   __('Orders', 'woocommerce'),
    //   'manage_options',
    //   'wc-orders',
    //   array($this, 'igs_cs_schedule_page')
    // );

    add_submenu_page(
      'igs-client-software',
      __('Schedule', 'igs-client-system'),
      __('Schedule', 'igs-client-system'),
      'manage_options',
      $this->get_schedule_slug(),
      array($this, 'igs_cs_schedule_page')
    );

    add_submenu_page(
      'igs-client-software',
      __('Customers', 'igs-client-system'),
      __('Customers', 'igs-client-system'),
      'manage_options',
      $this->get_customer_slug(),
      array($this, 'igs_cs_clients_page')
    );

    add_submenu_page(
      'igs-client-software',
      __('Export', 'igs-client-system'),
      __('Export', 'igs-client-system'),
      'manage_options',
      $this->get_export_slug(),
      array($this, 'igs_cs_export_page')
    );

    remove_submenu_page('igs-client-software', 'igs-client-software');
  }

  /**
   * Render subscriptions list page.
   *
   * @since 1.0.0
   */
  public function igs_cs_subscription_page() {

    if ( ! current_user_can('manage_options') ) {
      wp_die(__('You do not have sufficient permissions to access this page.', 'igs-client-system'));
    }

    $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';

    if ( 'edit' === $action && isset($_GET['id'] ) ) {

      $subscription = new IGS_CS_Subscription( $_GET['id'] );

      $temp_data  = get_transient('igs_edit_subscription_data_' . $subscription->get_id());

      if ($temp_data && !isset($_GET['errors'])) {
        delete_transient('igs_edit_subscription_data_' . $subscription->get_id());
        $temp_data = false;
      }

      igs_cs_get_template( 'admin/edit-subscription', array(
        'subscription'      => $subscription,
        'temp_data'         => $temp_data,
        'subscription_page' => $this
      ) );
    } else {
      igs_cs_get_template( 'admin/subscriptions-list', array(
        'subscription_page' => $this
      ) );
    }

  }

  /**
   * Render New Subscription page.
   *
   * @since 1.0.0
   */
  public function igs_cs_new_subscription_page() {

    if ( ! current_user_can('manage_options') ) {
      wp_die(__('You do not have sufficient permissions to access this page.', 'igs-client-system'));
    }

    $notice = isset( $_GET['notice'] ) ? sanitize_text_field( $_GET['notice'] ) : '';
    $sub_warning_id = isset( $_GET['sub_id'] ) ? absint( $_GET['sub_id'] ) : 0;

    igs_cs_get_template( 'admin/new-subscription', array(
      'subscription_page' => $this,
      'notice'            => $notice,
      'sub_warning_id'    => $sub_warning_id,
    ) );

  }

  /**
   * Render Schedule page.
   *
   * @since 1.0.0
   */
  public function igs_cs_schedule_page() {

    if ( ! current_user_can('manage_options') ) {
      wp_die(__('You do not have sufficient permissions to access this page.', 'igs-client-system'));
    }

    igs_cs_get_template( 'admin/schedule-calendar' );

  }

  /**
   * Render Clients page.
   *
   * @since 1.0.0
   */
  public function igs_cs_clients_page() {

    if ( ! current_user_can('manage_options') ) {
      wp_die(__('You do not have sufficient permissions to access this page.', 'igs-client-system'));
    }

    $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';

    if ( 'edit' === $action && isset($_GET['user_id'] ) ) {

      $user = new IGS_CS_User( $_GET['user_id'] );

      $temp_data  = get_transient('igs_edit_user_data_' . $user->igs_get_id());

      if ($temp_data && !isset($_GET['errors'])) {
        delete_transient('igs_edit_user_data_' . $user->igs_get_id());
        $temp_data = false;
      }

      igs_cs_get_template( 'admin/edit-user', array(
        'user'         => $user,
        'temp_data'    => $temp_data,
        'clients_page' => $this
      ) );
    } else {
      igs_cs_get_template( 'admin/clients', array(
        'clients_page' => $this
      ) );
    }

  }

  /**
   * Render Export page.
   *
   * @since 1.0.0
   */
  public function igs_cs_export_page() {

    if ( ! current_user_can('manage_options') ) {
      wp_die(__('You do not have sufficient permissions to access this page.', 'igs-client-system'));
    }

    igs_cs_get_template( 'admin/export', array(
      'export_page' => $this
    ) );
  }

  /**
   * Get subscriptions menu slug.
   *
   * @since 1.0.0
   * @return string Menu slug.
   */
  public function get_subscriptions_slug() {
    return $this->subscriptions_slug;
  }

  /**
   * Get schedule menu slug.
   *
   * @since 1.0.0
   * @return string Menu slug.
   */
  public function get_schedule_slug() {
    return $this->schedule_slug;
  }

  /**
   * Get customer menu slug.
   *
   * @since 1.0.0
   * @return string Menu slug.
   */
  public function get_customer_slug() {
    return $this->customer_slug;
  }

  /**
   * Get export menu slug.
   *
   * @since 1.0.0
   * @return string Menu slug.
   */
  public function get_export_slug() {
    return $this->export_slug;
  }

  /**
   * Get new subscription menu slug.
   *
   * @since 1.0.0
   * @return string Menu slug.
   */
  public function get_new_subscription_slug() {
    return $this->new_subscription_slug;
  }

  /**
   * Add admin menus.
   *
   * @since 1.0.0
   */
  public static function add_admin_menus() {
    $admin_menus = new IGS_CS_Admin_Menus();
    $admin_menus->add_main_menu();
    $admin_menus->add_submenus();
  }

  public static function add_adminbar_menus($wp_admin_bar) {

    if ( ! current_user_can('manage_options')) {
      return;
    }

    $admin_menus = new IGS_CS_Admin_Menus();

    $wp_admin_bar->add_node(array(
      'id'    => 'igs-client-system',
      'title' => '<span class="ab-icon dashicons-cart"></span>' . _x('Client Software', 'menu', 'igs-client-system'),
      'href'  => admin_url('admin.php?page=' . $admin_menus->get_subscriptions_slug() ),
    ));

    $wp_admin_bar->add_node(array(
      'id'     => 'igs-subscription',
      'parent' => 'igs-client-system',
      'title'  => __('Subscriptions', 'igs-client-system'),
      'href'   => admin_url('admin.php?page=' . $admin_menus->get_subscriptions_slug() ),
    ));

    $wp_admin_bar->add_node(array(
      'id'     => 'igs-orders',
      'parent' => 'igs-client-system',
      'title'  => __('Orders', 'woocommerce'),
      'href'   => admin_url( 'admin.php?page=wc-orders' ),
    ));

    $wp_admin_bar->add_node(array(
      'id'     => 'igs-schedule',
      'parent' => 'igs-client-system',
      'title'  => __('Schedule', 'igs-client-system'),
      'href'   => admin_url('admin.php?page=' . $admin_menus->get_schedule_slug() ),
    ));

    $wp_admin_bar->add_node(array(
      'id'     => 'igs-customers',
      'parent' => 'igs-client-system',
      'title'  => __('Customers', 'igs-client-system'),
      'href'   => admin_url('admin.php?page=' . $admin_menus->get_customer_slug() ),
    ));

    $wp_admin_bar->add_node(array(
      'id'     => 'igs-export',
      'parent' => 'igs-client-system',
      'title'  => __('Export', 'igs-client-system'),
      'href'   => admin_url('admin.php?page=' . $admin_menus->get_export_slug() ),
    ));
  }
}
