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
    $this->add_action('admin_menu', 'IGS_CS_Admin_Menus', 'add_admin_menus',10);
    $this->add_action('admin_bar_menu', 'IGS_CS_Admin_Menus', 'add_adminbar_menus', 100, 1);
    $this->add_action('save_post', $this, 'save_post_action', 10, 2 );
    $this->add_action('before_delete_post', $this, 'save_post_action', 10, 2 );
    $this->add_action('profile_update', $this, 'profile_update_action', 10, 3);
    $this->add_action('wcs_related_orders_meta_box_rows', 'IGS_CS_Subscription', 'related_orders_table_row', 5 );
    $this->add_action('admin_post_igs_save_subscription_data', 'IGS_CS_Subscription', 'igs_handle_save_subscription', 10 );
    $this->add_action('admin_post_igs_save_user_data', 'IGS_CS_User', 'igs_handle_save_user', 10 );
    $this->add_action('admin_post_igs_export_subscriptions', $this,  'igs_handle_subscriptions_export', 10);
    $this->add_action('admin_post_igs_export_orders', $this,  'igs_handle_orders_export', 10);

    $this->add_action('in_admin_header', $this, 'igs_cs_header_template', 10);
    $this->add_action('igs_cs_before_content', $this, 'igs_cs_page_title', 10);
    $this->add_action('igs_cs_content', $this, 'igs_cs_render_page_content', 10);
    $this->add_action('woocommerce_product_data_panels', 'IGS_CS_Admin_Product_Data', 'igs_visibility_product_data', 10);
    $this->add_action('woocommerce_process_product_meta', 'IGS_CS_Admin_Product_Data', 'save_product_visibility_roles', 10, 1);
    $this->add_action('woocommerce_product_options_pricing', 'IGS_CS_Admin_Product_Data', 'product_prices_list_pricing', 10 );
    $this->add_action( 'woocommerce_process_product_meta', 'IGS_CS_Admin_Product_Data', 'save_product_prices_list_pricing', 10, 1 );
    $this->add_action( 'woocommerce_variation_options_pricing', 'IGS_CS_Admin_Product_Data', 'variation_product_prices_list_pricing', 10, 3 );
    $this->add_action('woocommerce_save_product_variation', 'IGS_CS_Admin_Product_Data', 'save_variations_prices_list_pricing', 10, 2);

    $this->add_action( 'igs_renew_subscription', $this, 'update_delivery_price', 10, 2);

    // Filters
		$this->add_filter('style_loader_tag', $this, 'styles_attribute', 10, 3);
    $this->add_filter('woocommerce_after_register_taxonomy', $this, 'igs_woocommerce_register_taxonomy', 10);
    $this->add_action('wcs_subscription_statuses', 'IGS_CS_Admin_Subscriptions', 'filter_subscription_statuses', 10, 1);
    $this->add_action('woocommerce_subscriptions_registered_statuses', 'IGS_CS_Admin_Subscriptions', 'filter_subscription_statuses', 10, 1);
    $this->add_filter('igs_cs_filter_statuses', 'IGS_CS_Subscription', 'filter_subscription_statuses', 10, 1 );
    $this->add_filter('woocommerce_subscription_period_interval_strings', 'IGS_CS_Subscription', 'filter_subscription_period_interval_strings', 10, 1);
    $this->add_filter('woocommerce_subscription_periods', 'IGS_CS_Subscription', 'filter_subscription_periods', 10, 1);
    $this->add_filter('woocommerce_can_subscription_be_updated_to_active', 'IGS_CS_Subscription', 'filter_allow_reactivation', 10, 2);
    $this->add_filter('woocommerce_can_subscription_be_updated_to_on-hold', 'IGS_CS_Subscription', 'filter_allow_reactivation', 10, 2);
    $this->add_filter('wcs_related_orders_table_header_columns', 'IGS_CS_Subscription', 'related_orders_table_header_columns', 10, 1);
    $this->add_filter('wcs_related_orders_table_row_columns', 'IGS_CS_Subscription', 'related_orders_table_row_columns', 10, 1);
    $this->add_filter('woocommerce_current_user_can_edit_customer_meta_fields', null, '__return_false');
    $this->add_filter('user_contactmethods', $this, 'igs_update_contact_methods', 11, 1 );
    $this->add_filter('woocommerce_customer_search_customers', $this, 'filter_search_customers', 10, 2);
    $this->add_filter('woocommerce_product_data_tabs', 'IGS_CS_Admin_Product_Data', 'igs_product_data_tabs', 10, 1);

    $this->add_filter( 'woocommerce_subscriptions_is_duplicate_site', null, '__return_false' );

	}

  /**
   * Enqueue admin-specific Styles and Scripts.
   *
   * @since 1.0.0
   */
  public function enqueue_admin_scripts( $hook ) {

    if ( $hook && false !== strpos( $hook, 'igs-' ) ) {
      wp_enqueue_style('igs-cs-styles', IGS_CS()->plugin_url() . '/resources/css/igs-styles.css', array('select2_style'), null);
      wp_enqueue_script( 'igs-cs-scrptis', IGS_CS()->plugin_url() . '/resources/js/igs-script.js', array('wc-admin-meta-boxes'), null, array('strategy' => 'async', 'in_footer' => true) );

      if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' && $_GET['page'] == 'igs-subscriptions' )  {
        wp_enqueue_script( 'wc-admin-order-meta-boxes', WC()->plugin_url() . '/assets/js/admin/meta-boxes-order.min.js', array(), null );
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

  public static function igs_handle_subscriptions_export() {

    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die(__('You do not have sufficient permissions to access this page.', 'igs-client-system'));
    }

    check_admin_referer( 'igs_export_subscriptions_action', 'igs_export_subscriptions_nonce' );

    $defaults = array(
      'igs_type'     => '3',
      'igs_per_page' => '',
      'igs_results'  => '',
      'igs_order'    => 'ASC'
    );

    $params = wp_parse_args( $_POST, $defaults );

    if ( $params['igs_status'] !== 'wc-active' ) {
      $params['igs_next_date'] = '';
      $params['igs_orderby'] = 'date';
    }

    $params = apply_filters( 'igs_export_subscription_params', $params );

    $igs_cs_query = new IGS_CS_Subscriptions_Query( $params );
    $igs_query = $igs_cs_query->igs_get_query();

    if ( ! $igs_query->have_posts(  ) ) {
      wp_redirect(add_query_arg(array(
        'page' => IGS_CS()->admin()->menus()->get_export_slug(),
        'export_error' => 'no_results'
      ), admin_url('admin.php') ) );
      exit;
    }

    if ( $params['igs_status'] === 'wc-active' ) {
      $meta_query = $igs_query->query['meta_query'];
      $date_range = array();

      foreach ($meta_query as $clause) {
        if (isset($clause['key']) && $clause['key'] === '_schedule_next_payment') {
          $date_range = $clause['value'];
          break;
        }
      }

      if (empty($date_range)) {
        wp_die('Could not determine date range from query. Please contact the administrator.');
      }

      $data_format = 'd.m.Y';
      $start_dt    = new DateTime($date_range[0]);
      $end_dt      = new DateTime($date_range[1]);
      $begin       = new DateTime($start_dt->format($data_format));
      $finish      = new DateTime($end_dt->format($data_format));
      $finish->modify('+1 day');

      $grouped_data = array();
      $period_range = new DatePeriod($begin, new DateInterval('P1D'), $finish);

      foreach ($period_range as $date) {
        $grouped_data[$date->format($data_format)] = array();
      }

      foreach ($igs_query->get_posts() as $id) {
        $sub          = new IGS_CS_Subscription($id);
        $next_payment = $sub->igs_get_next_date($data_format);

        if ($next_payment) {
          if (isset($grouped_data[$next_payment])) {
            $grouped_data[$next_payment][] = $sub;
          }
        }
      }

      igs_cs_get_template( 'admin/part/export-active-subscirptions-xls', array(
        'grouped_data' => $grouped_data
      ) );
    } else {
      igs_cs_get_template( 'admin/part/export-subscirptions-xls', array(
        'igs_query' => $igs_query
      ) );
    }

    exit;

  }

  public static function igs_handle_orders_export() {

    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die(__('You do not have sufficient permissions to access this page.', 'igs-client-system'));
    }

    check_admin_referer( 'igs_export_orders_action', 'igs_export_orders_nonce' );

    $defaults = array(
      'limit'  => -1,
      'return' => 'ids'
    );

    $params = wp_parse_args( $_POST, $defaults );
    $params = apply_filters( 'igs_export_orders_params', $params );

    $orders = wc_get_orders($params);

    if ( ! $orders ) {
      wp_redirect(add_query_arg(array(
        'page' => IGS_CS()->admin()->menus()->get_export_slug(),
        'export_error' => 'no_results'
      ), admin_url('admin.php') ) );
      exit;
    }

    igs_cs_get_template( 'admin/part/export-orders-xls', array(
      'orders' => $orders,
      'type'   => $_POST['igs_order_type']
    ) );

    exit;

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

  public function igs_woocommerce_register_taxonomy() {

    register_taxonomy(
			'product_prices_list',
			apply_filters( 'woocommerce_taxonomy_objects_prices_list', array( 'product' ) ),
			apply_filters(
				'woocommerce_taxonomy_args_prices_list',
				array(
					'hierarchical'          => true,
					'update_count_callback' => '_wc_term_recount',
					'label'                 => __( 'Prices List', 'igs-client-system' ),
					'labels'                => array(
						'name'                  => __( 'Prices List', 'igs-client-system' ),
						'singular_name'         => __( 'Price list', 'igs-client-system' ),
						'menu_name'             => _x( 'Prices List', 'Admin menu name', 'igs-client-system' ),
						'search_items'          => __( 'Search', 'igs-client-system' ),
						'all_items'             => __( 'All Prices List', 'igs-client-system' ),
						'parent_item'           => __( 'Parent list', 'igs-client-system' ),
						'parent_item_colon'     => __( 'Parent list:', 'igs-client-system' ),
						'edit_item'             => __( 'Edit list', 'igs-client-system' ),
						'update_item'           => __( 'Update list', 'igs-client-system' ),
						'add_new_item'          => __( 'Add new list', 'igs-client-system' ),
						'new_item_name'         => __( 'New list name', 'igs-client-system' ),
						'not_found'             => __( 'No prices list found', 'igs-client-system' ),
						'item_link'             => __( 'Price List Link', 'igs-client-system' ),
						'item_link_description' => __( 'A link to a price list.', 'igs-client-system' ),
						'template_name'         => _x( 'Products by Price List', 'Template name', 'igs-client-system' ),
					),
					'public'            => false,
					'show_ui'           => true,
					'meta_box_cb'       => false,
					'show_in_nav_menus' => false,
					'query_var'         => true,
					'capabilities'      => array(
						'manage_terms' => 'manage_product_terms',
						'edit_terms'   => 'edit_product_terms',
						'delete_terms' => 'delete_product_terms',
						'assign_terms' => 'assign_product_terms',
					),
					'rewrite' => false,
				)
			)
		);
  }

  public function igs_update_contact_methods () {
    return array();
  }



  public function filter_search_customers( $args, $term ) {

    $args['role'] = 'customer';

    if ( isset( $args['search_columns'] ) ) {
      $args['search_columns'] = array(
        'ID', 'user_email'
      );
    }

    if ( isset( $args['meta_query'] ) ) {
      $args['meta_query'][] = array(
				'key'     => 'billing_phone',
				'value'   => $term,
				'compare' => 'LIKE',
      );
    }

    return $args;
  }

  public function update_delivery_price( $order_id, $order ) {

    foreach( $order->get_items( 'shipping' ) as $item_id => $item ){
      if( $item->get_method_id() === 'speedy_shipping_method' ) {
        $this->update_speedy_price( $order_id, $order, $item );
      } elseif ( $item->get_method_id() === 'econt_shipping_method' ) {
        $this->update_econt_price( $order_id, $order, $item );
      }
    }
  }

  public function update_speedy_price( $order_id, $order, $item ) {

    $speedy_mysql                      = new Speedy_mySQL;
    $speedy_admin_order                = new Speedy_Admin_Order();
    $loading_data                      = $speedy_mysql->default_loading_data($order_id);
    $order_wp                          = $speedy_admin_order->speedy_order_products($order_id);
    $loading_data['speedy_cod_amount'] = $order_wp['cod_price'];
    $loading_data['speedy_dv_amount']  = $order_wp['dv_price'];
    $loading_data['speedy_weight']     = $order_wp['weight'];
    $loading_data['service_payer']     = $order_wp['payer'];
    $loading_data['action']            = 'speedy_handle_ajax';
    $loading_data['action2']           = 'calculation';

    $url = get_bloginfo('wpurl') . '/wp-admin/admin-ajax.php';
    $result = wp_remote_post( $url, array( 'body'    => $loading_data, ) );

    if( wp_remote_retrieve_response_code( $result ) != 200 ){
      wp_die( __('Server Response Code:', 'woocommerce-speedy') . ' ' . wp_remote_retrieve_response_code( $result ) );
    } elseif ( is_wp_error( $result ) ) {
      wp_die( $result->get_error_message() );
    } else {
      $body = wp_remote_retrieve_body( $result );
      $response = json_decode( $body );

      foreach ($response as $key => $value) {

        if( $key !== 'error' && ! property_exists($value, 'error') ) {

          $speedy_options = get_option('speedy_shipping_method_options');

          if( !isset($speedy_options['inc_shipping_cost']) || !is_numeric($speedy_options['inc_shipping_cost']) ){
            $speedy_options['inc_shipping_cost'] = 1;
          }

          if( !empty($speedy_options['inc_shipping_cost']) ){
            $speedy_total = number_format((float)$value->price->mrejanetRecipient, 2, ".", "");
            $item->set_total( $speedy_total );
            $item->save();
            $order->calculate_totals();
          }

          $order->update_meta_data( 'speedy_destination_services_name', sanitize_text_field($value->serviceName) );
          $order->update_meta_data( 'speedy_total_price', sanitize_text_field(number_format((float)$value->price->mrejanetTotal, 2, ".", "")) );
          $order->update_meta_data( 'speedy_recipient_price', sanitize_text_field(number_format((float)$value->price->mrejanetRecipient, 2, ".", "")) );
          $order->update_meta_data( 'speedy_destination_services_id', sanitize_text_field($value->serviceId . ';' . $value->serviceName . ';' . number_format((float)$value->price->mrejanetTotal, 2, ".", "")  . ';' . number_format((float)$value->price->mrejanetRecipient, 2, ".", "") . ';' . $value->price->currency) );
          $order->save();
        }
      }
    }
  }

  public function update_econt_price( $order_id, $order, $item ) {

    $econt_mysql                        = new Econt_mySQL;
    $loading_data                       = array();
    $intl_delivery                      = ($order->get_billing_country() == 'RO' || $order->get_billing_country() == 'GR') ? true : false;
    $loading_data['receiver_city']      = '';
    $loading_data['receiver_post_code'] = '';
    $loading_data['order_id']           = $order_id;
    $loading_data['payment_method']     = $order->get_payment_method();
    $loading_data['order_cd']           = ($loading_data['payment_method'] == 'cod' || $loading_data['payment_method'] == 'econt_payment') ? 1 : 0;

    if ($order->get_meta( 'Econt_Door_Town', true )) {
        $loading_data['receiver_city'] = $order->get_meta( 'Econt_Door_Town', true );
    } elseif($order->get_meta( 'Econt_Office_Town', true )) {
        $loading_data['receiver_city'] = $order->get_meta( 'Econt_Office_Town', true );
    } elseif($order->get_meta( 'Econt_Machine_Town', true )) {
      $loading_data['receiver_city'] = $order->get_meta( 'Econt_Machine_Town', true );
    }

    if($order->get_meta( 'Econt_Door_Postcode', true )) {
      $loading_data['receiver_post_code'] = $order->get_meta( 'Econt_Door_Postcode', true );
    } elseif($order->get_meta( 'Econt_Office_Postcode', true )) {
      $loading_data['receiver_post_code'] = $order->get_meta( 'Econt_Office_Postcode', true );
    } elseif($order->get_meta( 'Econt_Machine_Postcode', true )) {
      $loading_data['receiver_post_code'] = $order->get_meta( 'Econt_Machine_Postcode', true );
    }

    $loading_data['receiver_office_code'] = '';

    if($order->get_meta( 'Econt_Office', true )) {
      $loading_data['receiver_office_code'] = $order->get_meta( 'Econt_Office', true );
    } elseif($order->get_meta( 'Econt_Machine', true )) {
      $loading_data['receiver_office_code'] = $order->get_meta( 'Econt_Machine', true );
    }

    if( $order->get_billing_company() ) {
      $loading_data['receiver_name'] = $order->get_billing_company();
    } else{
      $loading_data['receiver_name'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    }

    $loading_data['receiver_name_person']  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $loading_data['receiver_email']        = $order->get_billing_email();
    $loading_data['receiver_street']       = ($intl_delivery == true) ? $order->get_meta( 'Econt_Door_Street_Intl', true ) : $order->get_meta( 'Econt_Door_Street', true );
    $loading_data['receiver_quarter']      = ($intl_delivery == true) ? $order->get_meta( 'Econt_Door_Quarter_Intl', true ) : $order->get_meta( 'Econt_Door_Quarter', true );
    $loading_data['receiver_street_num']   = $order->get_meta( 'Econt_Door_street_num', true );
    $loading_data['receiver_street_bl']    = $order->get_meta( 'Econt_Door_building_num', true );
    $loading_data['receiver_street_vh']    = $order->get_meta( 'Econt_Door_Entrance_num', true );
    $loading_data['receiver_street_et']    = $order->get_meta( 'Econt_Door_Floor_num', true );
    $loading_data['receiver_street_ap']    = $order->get_meta( 'Econt_Door_Apartment_num', true );
    $loading_data['receiver_street_other'] = $order->get_meta( 'Econt_Door_Other', true );
    $loading_data['receiver_phone_num']    = $order->get_billing_phone();
    $loading_data['receiver_shipping_to']  = $order->get_meta( 'Econt_Shipping_To', true );

    $loading_data['currency'] = $order->get_currency();
    $loading_data['currency_symbol'] = get_woocommerce_currency_symbol($loading_data['currency']);

    if(!empty($loading_data['receiver_city'])){
      $result = $econt_mysql->create_loading($loading_data, 1);
      if(! array_key_exists('warning', $result)) {
        $econt_options = get_option('econt_shipping_method_options');

        if( !empty($econt_options['inc_shipping_cost']) ) {
          $econt_total = $result['customer_shipping_cost'];
          $item->set_total( $econt_total );
          $item->save();
          $order->calculate_totals();
        }

        $order->update_meta_data( 'Econt_Customer_Shipping_Cost', sanitize_text_field($result['customer_shipping_cost']) );
        $order->update_meta_data( 'Econt_Total_Shipping_Cost', sanitize_text_field($result['total_shipping_cost']) );
        $order->save();
      }
    }
  }

}

IGS_CS_Admin_Hooks::instance();
