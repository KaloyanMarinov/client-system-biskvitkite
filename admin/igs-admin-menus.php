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
   * Menu page slug for Subscriptions.
   *
   * @since 1.0.0
   * @access private
   * @var string $subscriptions_slug Subscriptions menu slug.
   */
  public $subscriptions_slug = 'igs-subscriptions';

  /**
   * Menu page slug for Clients.
   *
   * @since 1.0.0
   * @access private
   * @var string $clients_slug Clients menu slug.
   */
  public $clients_slug = 'igs-clients';

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
      __('Schedule', 'igs-client-system'),
      __('Schedule', 'igs-client-system'),
      'manage_options',
      'igs-schedule',
      array($this, 'igs_cs_schedule_page')
    );

    add_submenu_page(
      'igs-client-software',
      __('Clients', 'igs-client-system'),
      __('Clients', 'igs-client-system'),
      'manage_options',
      $this->get_clients_slug(),
      array($this, 'igs_cs_clients_page')
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

      igs_cs_get_template( 'admin/edit-subscription', array(
        'subscription' => $subscription,
        'subscription_page' => $this
      ) );
    } else {
      igs_cs_get_template( 'admin/subscriptions-list', array(
        'subscription_page' => $this
      ) );
    }

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

    igs_cs_get_template( 'admin/clients', array(
      'clients_page' => $this
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
   * Get subscriptions menu slug.
   *
   * @since 1.0.0
   * @return string Menu slug.
   */
  public function get_clients_slug() {
    return $this->clients_slug;
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
}
