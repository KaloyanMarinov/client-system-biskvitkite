<?php

/**
 * The Admin Hooks functionality of the plugin.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS
 * @subpackage IGS/Admin/Hooks
 * @author     igamingsolutions.com <support@igamingsolutions.com>
 *
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Admin_Hooks extends IGS_CS_Loader {

	/**
	 * The single instance of the class.
	 *
	 * @var IGS_CS_Admin_Hooks
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Main IGS_CS_Admin_Hooks Instance.
	 *
	 * Ensures only one instance of IGS_CS_Admin_Hooks is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return IGS_CS_Admin_Hooks - Main instance.
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
	public function __construct( ) {
		parent::__construct();

    $this->hooks();
		$this->run();

	}

  /**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
   *
   * @return void
	 */
	private function hooks() {
    $this->add_action('admin_init', 'IGS_CS_Subscription', 'handle_manual_renewal' );
    $this->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_scripts', 10, 1);
    $this->add_action('admin_menu', 'IGS_CS_Admin_Menus', 'add_admin_menus', 10);
    $this->add_action('save_post', $this, 'save_post_action', 10, 2 );
    $this->add_action('before_delete_post', $this, 'save_post_action', 10, 2 );
    $this->add_action('profile_update', $this, 'profile_update_action', 10, 3);
    $this->add_action('admin_post_igs_save_subscription_data', 'IGS_CS_Subscription', 'igs_handle_save_subscription', 10 );

    $this->add_action('in_admin_header', $this, 'igs_cs_header_template', 10);
    $this->add_action('igs_cs_before_content', $this, 'igs_cs_page_title', 10);
    $this->add_action('igs_cs_content', $this, 'igs_cs_render_page_content', 10);

    // Filters
		$this->add_filter('style_loader_tag', $this, 'styles_attribute', 10, 3);
    $this->add_action('wcs_subscription_statuses', 'IGS_CS_Admin_Subscriptions', 'filter_subscription_statuses', 10, 1);
    $this->add_filter('igs_cs_filter_statuses', 'IGS_CS_Subscription', 'filter_subscription_statuses', 10, 1 );
    $this->add_filter('woocommerce_subscription_period_interval_strings', 'IGS_CS_Subscription', 'filter_subscription_period_interval_strings', 10, 1);
    $this->add_filter('woocommerce_subscription_periods', 'IGS_CS_Subscription', 'filter_subscription_periods', 10, 1);
    $this->add_filter('woocommerce_can_subscription_be_updated_to_active', 'IGS_CS_Subscription', 'filter_allow_reactivation', 10, 2);
    $this->add_filter('woocommerce_can_subscription_be_updated_to_on-hold', 'IGS_CS_Subscription', 'filter_allow_reactivation', 10, 2);
    $this->add_filter('woocommerce_current_user_can_edit_customer_meta_fields', null, 'return__false');
    $this->add_filter('user_contactmethods', $this, 'igs_update_contact_methods', 11, 1 );

	}

  /**
   * Enqueue admin-specific Styles and Scripts.
   *
   * @since 1.0.0
   */
  public function enqueue_admin_scripts( $hook ) {

    if ( $hook && false !== strpos( $hook, 'igs-' ) ) {
      wp_enqueue_style('igs-cs-styles', IGS_CS()->plugin_url() . '/resources/css/igs-styles.css', array('select2_style'), null);
      wp_enqueue_script( 'igs-cs-scrptis', IGS_CS()->plugin_url() . '/resources/js/igs-script.js', array(), null, array('strategy' => 'async', 'in_footer' => true) );

      if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' )  {
        wp_enqueue_script( 'wc-admin-order-meta-boxes', WC()->plugin_url() . '/assets/js/admin/meta-boxes-order.min.js', array( 'wc-admin-meta-boxes', 'wc-backbone-modal', 'selectWoo', 'wc-clipboard' ), null );
        wp_enqueue_script( 'wcs-admin-meta-boxes-order', WC_Subscriptions_Core_Plugin::instance()->get_subscriptions_core_directory_url( 'assets/js/admin/wcs-meta-boxes-order.js' ), [], null, false );
        $params = array(
          'add_order_note_nonce'    => wp_create_nonce( 'add-order-note' ),
          'delete_order_note_nonce' => wp_create_nonce( 'delete-order-note' ),
          'ajax_url'                => admin_url( 'admin-ajax.php' ),
          'post_id'                 => $_GET['id'],
        );

        wp_localize_script( 'wc-admin-meta-boxes', 'woocommerce_admin_meta_boxes', $params );
      }
    }

	}

    /**
   * Function for `save_post` action-hook.
   *
   * @param int     $post_id Post ID.
   *
   * @return void
   */
  public static function save_post_action( $post_id, $post ) {

    if ( ! defined( 'DOING_AUTOSAVE' ) && ! wp_is_post_revision( $post_id ) ) {
      wp_cache_delete('igs_post_'. $post_id .'_', 'igs_post');
    }

  }

  /**
   * Function for `profile_update` action-hook.
   *
   * @param int     $user_id       User ID.
   * @param WP_User $old_user_data Object containing user's data prior to update.
   * @param array   $userdata      The raw array of data passed to wp_insert_user().
   *
   * @return void
   */
  public function profile_update_action( $user_id ){
    wp_cache_delete('igs_user_'. $user_id .'_', 'igs_user');
  }

  public function igs_cs_header_template() {

    $screen = get_current_screen();

    if ( strpos( $screen->id, 'page_igs-' ) === false ) {
      return;
    }

    igs_cs_get_template( 'admin/header' );

  }

  public function igs_cs_page_title() {

    igs_cs_get_template( 'admin/part/page-title' );

  }

  /**
   * Filter style tags to remove default WordPress admin styles on our pages.
   *
   * @since 1.0.0
   * @param string $tag    The style tag.
   * @param string $handle The style handle.
   * @param string $href   The style source URL.
   * @return string
   */
	public function styles_attribute($tag, $handle, $href) {

    $includes = array(
      'dashicons',
      'select2_style',
      'wc-components',
      'woocommerce_admin_styles',
      'econt_style',
      'speedy_style',
    );

    if ( IGS_CS()->is_request( 'admin' )) {

      if ( in_array( $handle, $includes ) || strpos($handle, 'igs-cs-') !== false ) {
        return $tag;
      }

			$screen = get_current_screen();

			if ( $screen && false !== strpos( $screen->id, 'igs-' ) ) {
				return '';
			}
		}


    return $tag;

  }

  public function igs_update_contact_methods () {
    return array();
  }
}

IGS_CS_Admin_Hooks::instance();
