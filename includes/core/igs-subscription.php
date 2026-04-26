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

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Subscription' ) ) {
  return;
}

class IGS_CS_Subscription extends WC_Subscription {

  /**
   * Initialize Slot
   *
   * @param WP_Post|int $post Product instance or ID.
   */
  public function __construct( $post = 0 ) {

    parent::__construct( $post );

  }

  /*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Functions for getting review data.
  */
  /**
   * Returns the status name for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_status() {

    return $this->get_status();

  }

  public function igs_set_customer() {

    if ( ! $user_id = $this->get_user_id() )
      return;

    return new IGS_CS_User( $user_id );
  }


  /**
   * Returns the author abbr for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_abbr() {
    return mb_substr($this->get_billing_first_name(), 0, 1) . mb_substr($this->get_billing_last_name(), 0, 1);
  }

  /**
   * Returns the user billing email for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_billing_email() {
    return $this->get_billing_email();
  }

  /**
   * Returns the user billing phone for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_billing_phone() {
    return $this->get_billing_phone();
  }

  /**
   * Returns the Subscription Start date for this object.
   *
   * @since  1.0.0
   * @return string Subscription date.
   *
   */
  public function igs_get_start_date( $format = null ) {

    if ( empty( $this->get_date( 'start' ) ) )
      return;

    if ( ! $format ) {
      $format = get_option( 'date_format' );
    }

    $timestamp = strtotime( $this->get_date( 'start' ) );

    return date_i18n( $format, $timestamp );

  }

  /**
   * Returns the Subscription next date for this object.
   *
   * @since  1.0.0
   * @return string Subscription date.
   *
   */
  public function igs_get_next_date( $format = null ) {

    if ( empty( $this->get_date( 'next_payment' ) ) )
      return;

    if ( ! $format ) {
      $format = get_option( 'date_format' );
    }

    $timestamp = strtotime( $this->get_date( 'next_payment' ) );

    return date_i18n( $format, $timestamp );

  }

  /**
   * Returns the Subscription next date for this object.
   *
   * @since  1.0.0
   * @return string Subscription date.
   *
   */
  public function igs_get_end_date( $format = null ) {

    if ( empty( $this->get_date( 'end_date' ) ) )
      return;

    if ( ! $format ) {
      $format = get_option( 'date_format' );
    }

    $timestamp = strtotime( $this->get_date( 'end_date' ) );

    return date_i18n( $format, $timestamp );

  }

  /**
   * Returns the Last order date for this object.
   *
   * @since  1.0.0
   * @return string Subscription date.
   *
   */
  public function igs_get_last_order_date( $format = null ) {

    if ( ! $format ) {
      $format = get_option( 'date_format' );
    }

    $timestamp = strtotime( $this->get_date( 'last_order_date_created' ) );

    return date_i18n( $format, $timestamp );

  }

  public function igs_get_days_to_renew() {

    $today        = new DateTime('today');
    $renewal_date = new DateTime($this->igs_get_next_date());
    $diff         = $today->diff($renewal_date);

    return (int)$diff->format('%r%a');

  }

  public function igs_get_start_months() {

    $today      = new DateTime('today');
    $start_date = new DateTime($this->igs_get_start_date());
    $interval   = $start_date->diff($today);

    return (int)($interval->y * 12) + $interval->m;

  }

  /**
   * Returns the Order Notes for this object.
   *
   * @since  1.0.0
   * @param  array $args Query arguments
   * @return stdClass[]
   *
   */
  public function igs_get_notes( $args = array() ) {

    $defaults = array(
      'order_id' => $this->get_id()
    );

    $args = wp_parse_args( $args, $defaults );

    return wc_get_order_notes( $args );

  }

  public function igs_get_shipping_method() {

    if( ! $shipping_methods = $this->get_shipping_methods() )
      return '';

    $current = reset( $shipping_methods );

    return $current->get_method_id();

  }

  public function igs_get_shipping_methods() {

    $package = array(
      'destination' => array(
        'country'   => $this->get_shipping_country(),
        'state'     => $this->get_shipping_state(),
        'postcode'  => $this->get_shipping_postcode(),
        'city'      => $this->get_shipping_city(),
        'address'   => $this->get_shipping_address_1(),
        'address_2' => $this->get_shipping_address_2(),
      ),
      'contents' => $this->get_items(),
      'user'     => array( 'ID' => $this->get_customer_id() ),
    );

    $shipping_zone = WC_Shipping_Zones::get_zone_matching_package( $package );
    return $shipping_zone->get_shipping_methods( true );


  }

  /*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting review data.
  */

  /*
	|--------------------------------------------------------------------------
	| Outputs
	|--------------------------------------------------------------------------
  */
  public function igs_get_days_label() {

    $days = $this->igs_get_days_to_renew();
    $attrs = array(
      'class' => array('d-ib px-8 py-5 fw-b br-5')
    );

    $days_text = wp_sprintf( _n( '%d day', '%d days', abs($days ), 'igs-client-system' ), abs($days ));
    $text = __('Remaining', 'igs-client-system') . ' ' . $days_text;

    if ( $days < 0 ) {
      $attrs['style'] = 'background-color: red; color: #fff';
      $text = __('Overdue by', 'igs-client-system') . ' ' . $days_text;
    } elseif ( $days == 0 ) {
      $attrs['style'] = 'background-color: green; color: #fff';
      $text = __('Today', 'igs-client-system');
    } elseif ( $days < 5 ) {
      $attrs['style'] = 'background-color: yellow; color: #000';
    } else {
      $attrs['style'] = 'background-color: gray; color: #fff';
    }

    return wp_sprintf( '<span %2$s>%1$s</span>',
      $text,
      igs_cs_html_attributes($attrs)
    );
  }

  /**
   * Returns the status name for this object.
   *
   * @since 1.0.0
   * @return string
   */
  public function igs_get_status_name() {

    $status = $this->igs_get_status();
    $attrs = array(
      'class' => array('d-ib px-8 py-5 fw-b br-5')
    );

    if ( 'active' ==  $status ) {
      $attrs['style'] = 'background-color: green; color: #fff';
    } elseif ( 'on-hold' == $status || 'pending-cancel' == $status ) {
      $attrs['style'] = 'background-color: yellow; color: #000';
    } else {
      $attrs['style'] = 'background-color: red; color: #fff';
    }

    return wp_sprintf( '<span %2$s>%1$s</span>',
      wcs_get_subscription_status_name( $status ),
      igs_cs_html_attributes($attrs)
    );

  }

  public function igs_get_months_subscriber() {

    if ( $this->igs_get_start_months() == 0 ) {
      return $this->igs_get_start_date();
    }

    return wp_sprintf( _n( 'One Month', '%d Months', $this->igs_get_start_months(), 'igs-client-system' ), $this->igs_get_start_months());

  }

  public function igs_get_renew_button() {

    $renew_url = wp_nonce_url(
      add_query_arg( array(
        'action'          => 'igs_cs_manual_renew',
        'subscription_id' => $this->get_id()
      ), admin_url( 'admin.php?page=igs-subscriptions' ) ),
      'igs_cs_renew_nonce'
    );

    $warning = __( 'Are you sure you want to create a renewal order?', 'igs-client-system' );

    return wp_sprintf( '<a %2$s>%1$s</a>',
      __( 'Renew Now', 'igs-client-system' ),
      igs_cs_html_attributes(array(
        'href'    => esc_url ($renew_url),
        'class'   => 'f-1 button bg-1 bg-h-3 tc-w tc-h-6',
        'onclick' => "return confirm('". $warning ."');"
      ))
    );
  }

  public function igs_get_edit_button() {

    return wp_sprintf( '<a %2$s>%1$s</a>',
      igs_cs_get_template_html( 'icons/edit' ),
      igs_cs_html_attributes(array(
        'href'  => admin_url( 'admin.php?page=' . IGS_CS()->admin()->menus()->get_subscriptions_slug() . '&action=edit&id=' . $this->get_id() ),
        'class' => 'f-a button tc-w bg-b bg-h-2 px-10',
      ))
    );

  }

  public function igs_get_birthday_badge() {

    $months = $this->igs_get_start_months();

    if ( $months == 0 || $months % 12 !== 0 )
      return;

    return wp_sprintf( '<p %3$s>%1$s%2$s</p>',
      igs_cs_get_template_html( 'icons/gift' ),
      __('Happy Birthday', 'igs-client-system'),
      igs_cs_html_attributes(array(
        'class' => 'd-f ai-c gx-10 fs-14 fw-b tc-1'
      ))
    );

  }

  /*
	|--------------------------------------------------------------------------
	| Static Functions
	|--------------------------------------------------------------------------
  */

  /**
   * When the_post is called, put artivle data into a global.
   *
   * @param mixed $subscription Post Object.
   * @return IGS_CS_Subscription
   */
  public static function igs_setup_data( $post ) {

    if ( empty( $post->post_type ) || 'shop_subscription' !== $post->post_type ) {
      return;
    }

    unset( $GLOBALS['subscription'] );

    $GLOBALS['subscription'] = new self( $post );

    return $GLOBALS['subscription'];
  }

  /**
   * Function for `filter_subscription_statuses` filter-hook.
   *
   * @param array $statuses List of subscription statuses
   *
   * @return array
   */
  public static function filter_subscription_statuses( $statuses ) {

    $all_status = array(
      'any' => __('All', 'igs-client-system')
    );

    $statuses = array_merge( $all_status, $statuses );

    return $statuses;

  }

  /**
   * Function for `woocommerce_subscription_period_interval_strings` filter-hook.
   *
   * @param array $intervals List of possible subscription periods
   *
   * @return array
   */
  public static function filter_subscription_period_interval_strings( $intervals ) {

    unset( $intervals[4] );
    unset( $intervals[5] );

    return $intervals;
  }


  /**
   * Function for `woocommerce_subscription_periods` filter-hook.
   *
   * @param array $periods List of time periods allowed
   *
   * @return array
   */
  public static function filter_subscription_periods( $periods ) {

    unset( $periods['day'] );
    unset( $periods['year'] );

    return $periods;
  }


  /**
   * Function for `woocommerce_can_subscription_be_updated` filter-hook.
   *
   * @param boolean $can_be_updated
   * @param WC_Subscription $subscription
   *
   * @return boolean
   */
  public static function filter_allow_reactivation( $can_be_updated, $subscription ) {

    if ( $subscription->has_status( 'cancelled' ) ) {
      return true;
    }

    return $can_be_updated;
  }

  public static function related_orders_table_header_columns($columns) {

    $columns = array(
      esc_html__( 'Order #', 'igs-client-system' ),
      esc_html__( 'Date', 'igs-client-system' ),
      esc_html__( 'Type order', 'igs-client-system' ),
      esc_html__( 'Status', 'igs-client-system' ),
      esc_html__( 'Total', 'igs-client-system' ),
      esc_html__( 'Actions', 'igs-client-system' ),
    );

    return $columns;

  }

  public static function related_orders_table_row( $order ) {
    remove_action('wcs_related_orders_meta_box_rows', 'WCS_Meta_Box_Related_Orders::output_rows',  10);

    $orders_to_display     = array();
		$subscriptions         = array();
		$initial_subscriptions = array();
		$orders_by_type        = array();
		$unknown_orders        = array(); // Orders which couldn't be loaded.
		$is_subscription       = wcs_is_subscription( $order );
		$this_order            = $order;

		if ( $is_subscription ) {
			$subscription    = wcs_get_subscription( $order );
			$subscriptions[] = $subscription;

			$initial_subscriptions         = wcs_get_subscriptions_for_resubscribe_order( $subscription );
			$orders_by_type['resubscribe'] = WCS_Related_Order_Store::instance()->get_related_order_ids( $subscription, 'resubscribe' );
		} else {
			$subscriptions         = wcs_get_subscriptions_for_order( $order, array( 'order_type' => array( 'parent', 'renewal' ) ) );
			$initial_subscriptions = wcs_get_subscriptions_for_order( $order, array( 'order_type' => array( 'resubscribe' ) ) );
		}

		foreach ( $subscriptions as $subscription ) {
			if ( 1 === count( $subscriptions ) && $subscription->get_parent_id() ) {
				$orders_by_type['parent'][] = $subscription->get_parent_id();
			}
			$orders_by_type['renewal'] = $subscription->get_related_orders( 'ids', 'renewal' );
			$subscription->update_meta_data( '_relationship', __( 'Subscription', 'igs-client-system' ) );
			$orders_to_display[] = $subscription;
		}

		foreach ( $initial_subscriptions as $subscription ) {
			$subscription->update_meta_data( '_relationship', __( 'Initial Subscription', 'igs-client-system' ) );
			$orders_to_display[] = $subscription;
		}

		foreach ( $orders_by_type as $order_type => $orders ) {
			foreach ( $orders as $order_id ) {
				$order = wc_get_order( $order_id );

				switch ( $order_type ) {
					case 'renewal':
						$relation = __('Renewal', 'igs-client-system');
						break;
					case 'parent':
						$relation = __('New Subscription', 'igs-client-system');
						break;
					case 'resubscribe':
						$relation = wcs_is_subscription( $order ) ? __( 'Resubscribed Subscription', 'igs-client-system' ) : __( 'Resubscribe Order', 'igs-client-system' );
						break;
					default:
						$relation = _x( 'Unknown Order Type', 'igs-client-system' );
						break;
				}

				if ( $order ) {
					$order->update_meta_data( '_relationship', $relation );
					$orders_to_display[] = $order;
				} else {
					$unknown_orders[] = array(
						'order_id' => $order_id,
						'relation' => $relation,
					);
				}
			}
		}

		if ( has_filter( 'woocommerce_subscriptions_admin_related_orders_to_display' ) ) {
			wcs_deprecated_hook( 'woocommerce_subscriptions_admin_related_orders_to_display', 'subscriptions-core 5.1.0', 'wcs_admin_subscription_related_orders_to_display' );

			$orders_to_display = apply_filters( 'woocommerce_subscriptions_admin_related_orders_to_display', $orders_to_display, $subscriptions, get_post( $this_order->get_id() ) );
		}

		$orders_to_display = apply_filters( 'wcs_admin_subscription_related_orders_to_display', $orders_to_display, $subscriptions, $this_order );

		wcs_sort_objects( $orders_to_display, 'date_created', 'descending' );

		foreach ( $orders_to_display as $order ) {
			if ( $order->get_id() === $this_order->get_id() ) {
				continue;
			}

			igs_cs_get_template( '/admin/part/related-orders-row', array( 'order' => $order ) );

		}

		foreach ( $unknown_orders as $order_and_relationship ) {
			$order_id     = $order_and_relationship['order_id'];
			$relationship = $order_and_relationship['relation'];

			include WC_SUBSCRIPTIONS_CORE_PATH . 'admin/meta-boxes/views/html-unknown-related-orders-row.php';
		}


  }

  /**
   * Update every product line item in a renewal order to the current price.
   *
   * Applies the customer's price-list override when one is configured,
   * identical to the logic in IGS_CS_Public_Pricing_Hooks::get_price().
   *
   * @since 1.0.0
   * @param WC_Order        $order
   * @param WC_Subscription $subscription
   * @return void
   */
  private static function update_renewal_product_prices( WC_Order $order, WC_Subscription $subscription ) {

    // Always use the current site currency for renewal orders so that currency
    // changes (e.g. BGN → EUR) are reflected immediately on new renewals.
    $order->set_currency( get_woocommerce_currency() );

    $user       = new IGS_CS_User( $subscription->get_user_id() );
    $price_list = $user->igs_get_price_list();

    foreach ( $order->get_items() as $item ) {
      /** @var WC_Order_Item_Product $item */
      $product = $item->get_product();

      if ( ! $product ) {
        continue;
      }

      $price = (float) $product->get_price();

      if ( $price_list ) {
        $custom = $product->get_meta( '_list_price_' . $price_list );
        if ( $custom !== '' && $custom !== false ) {
          $price = (float) wc_format_decimal( $custom );
        }
      }

      $qty = $item->get_quantity();
      $item->set_subtotal( $price * $qty );
      $item->set_total( $price * $qty );
      $item->save();
    }

    $order->calculate_totals();

  }

  /**
   * Handle manual renewal action.
   *
   * @since 1.0.0
   */
  public static function handle_manual_renewal() {

    if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'igs_cs_manual_renew' ) {
      return;
    }

    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'igs_cs_renew_nonce' ) ) {
      wp_die( __( 'Security check failed.', 'igs-client-system' ) );
    }

    $subscription_id = isset( $_GET['subscription_id'] ) ? absint( $_GET['subscription_id'] ) : 0;
    $subscription    = wcs_get_subscription( $subscription_id );

    if ( ! $subscription ) {
      return;
    }

    $renewal_order = wcs_create_renewal_order( $subscription );

    if ( is_wp_error( $renewal_order ) ) {
      wp_die( $renewal_order->get_error_message() );
    }

    // wcs_create_renewal_order() does not reliably copy the payment method,
    // so set it explicitly from the subscription.
    $renewal_order->set_payment_method( $subscription->get_payment_method() );
    $renewal_order->set_payment_method_title( $subscription->get_payment_method_title() );

    // Remove copied shipping items – the shipping integration will add fresh
    // ones with recalculated prices via the igs_renew_subscription action.
    foreach ( $renewal_order->get_items( 'shipping' ) as $item_id => $item ) {
      $renewal_order->remove_item( $item_id );
    }

    // wcs_create_renewal_order() copies all subscription meta, including
    // _igs_prepaid.  Reset it now; we will set it to '1' later only if the
    // prepaid period is still active.
    $renewal_order->delete_meta_data( '_igs_prepaid' );
    $renewal_order->save();

    // Update product line-item prices to current, respecting the customer's
    // assigned price list.
    self::update_renewal_product_prices( $renewal_order, $subscription );

    // Determine whether the subscription is within its prepaid period.
    // We resolve this before firing igs_renew_subscription so the shipping
    // integration runs normally, then we clean up afterwards.
    $prepaid_active = false;
    if ( '1' === $subscription->get_meta( '_igs_prepaid' ) ) {
      $until_raw = $subscription->get_meta( '_igs_prepaid_until' );
      $until_dt  = $until_raw ? DateTime::createFromFormat( 'd.m.Y', $until_raw ) : false;
      if ( $until_dt && ( (int) $until_dt->format('Ym') > (int) ( new DateTime() )->format('Ym') ) ) {
        $prepaid_active = true;
      }
    }

    // Let the shipping integration (Econt / Speedy) add and price shipping items.
    do_action( 'igs_renew_subscription', $renewal_order->get_id(), $renewal_order );

    // Re-fetch so we see any items the action may have added/modified.
    $renewal_order   = wc_get_order( $renewal_order->get_id() );
    $prepaid_cycles  = (int) $subscription->get_meta( '_igs_prepaid_cycles' );

    if ( $prepaid_cycles > 0 ) {

      // Prepaid bulk order: multiply every line item and shipping by the number
      // of cycles the customer is paying for upfront.
      // Multiply product line items.
      foreach ( $renewal_order->get_items() as $item ) {
        $item->set_subtotal( $item->get_subtotal() * $prepaid_cycles );
        $item->set_total( $item->get_total() * $prepaid_cycles );
        $item->save();
      }

      // Calculate total shipping (after multiplication) then roll it into
      // product line items proportionally, and zero out the shipping lines.
      $total_shipping = 0;
      foreach ( $renewal_order->get_items( 'shipping' ) as $item ) {
        $total_shipping += (float) $item->get_total() * $prepaid_cycles;
      }

      if ( $total_shipping > 0 ) {
        $fee = new WC_Order_Item_Fee();
        $fee->set_name( __( 'Prepaid delivery', 'igs-client-system' ) );
        $fee->set_total( $total_shipping );
        $fee->set_tax_status( 'none' );
        $renewal_order->add_item( $fee );
        $fee->save();
      }

      foreach ( $renewal_order->get_items( 'shipping' ) as $item ) {
        $item->set_total( 0 );
        $item->save();
      }

      $renewal_order->update_meta_data( '_igs_prepaid', '1' );
      $renewal_order->update_meta_data( '_igs_prepaid_cycles', $prepaid_cycles ); // kept for reference
      $renewal_order->calculate_totals();
      $renewal_order->save();

      // Cycles are consumed — clear them from the subscription so future
      // renewals fall into the normal zero-price prepaid path.
      $subscription->update_meta_data( '_igs_prepaid_cycles', 0 );
      $subscription->save();

    } elseif ( $prepaid_active ) {

      // Normal prepaid renewal within the paid period: zero all prices.
      foreach ( $renewal_order->get_items() as $item ) {
        $item->set_subtotal( 0 );
        $item->set_total( 0 );
        $item->save();
      }
      foreach ( $renewal_order->get_items( 'shipping' ) as $item ) {
        $item->set_total( 0 );
        $item->save();
      }
      $renewal_order->update_meta_data( '_igs_prepaid', '1' );
      $renewal_order->calculate_totals();
      $renewal_order->save();

    }

    $is_bacs       = ( 'bacs' === $subscription->get_payment_method() );
    $renewal_status = $is_bacs ? 'on-hold'    : 'processing';
    $renewal_note   = $is_bacs
      ? __( 'Manual system renewal – awaiting bank transfer', 'igs-client-system' )
      : __( 'Manual system renewal', 'igs-client-system' );

    $renewal_order->update_status( $renewal_status, $renewal_note );

    // Let WCS calculate the next payment date using the subscription's own
    // billing interval and period.  When the renewal day is fixed, grab the
    // day-of-month from the *current* next_payment date and stamp it onto the
    // WCS-calculated date so the day never drifts (e.g. renewal on 15.04 with
    // a fixed day of 10 → WCS gives 15.05 → we correct it to 10.05).
    $next_payment_date = $subscription->calculate_date( 'next_payment' );

    if ( '1' === $subscription->get_meta( '_igs_fixed_renewal_day' ) ) {
      $current_next = $subscription->get_date( 'next_payment' ); // UTC MySQL datetime
      $fixed_day    = (int) ( new DateTime( $current_next, new DateTimeZone( 'UTC' ) ) )->format( 'j' );
      $calculated   = new DateTime( $next_payment_date, new DateTimeZone( 'UTC' ) );

      // Clamp to the last day of the target month (e.g. day 31 in February → 28/29).
      $days_in_month = (int) $calculated->format( 't' );
      $calculated->setDate(
        (int) $calculated->format( 'Y' ),
        (int) $calculated->format( 'n' ),
        min( $fixed_day, $days_in_month )
      );

      $next_payment_date = $calculated->format( 'Y-m-d H:i:s' );
    }

    $subscription->update_dates( array(
      'next_payment' => $next_payment_date
    ) );
    $subscription->add_order_note( __( 'The next payment date has been updated based on the manual renewal.', 'igs-client-system' ) );

    wp_redirect( admin_url( 'post.php?post=' . $renewal_order->get_id() . '&action=edit' ) );
    exit;
  }

  public static function igs_handle_save_subscription() {

    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die(__('You do not have sufficient permissions to access this page.', 'igs-client-system'));
    }

    check_admin_referer( 'igs_subscription_save_action', 'igs_subscription_save_nonce' );

    $sub_id = absint( $_POST['subscription_id'] );
    $subscription = wcs_get_subscription( $sub_id );

    if ( ! $subscription ) {
      wp_die( __( 'Subscription not found.', 'igs-client-system' ) );
    }

    $errors   = array();
    $base_url = admin_url( 'admin.php?page='. IGS_CS()->admin()->menus()->get_subscriptions_slug() .'&action=edit&id=' . $sub_id);

    $input_data['_billing_first_name']      = sanitize_text_field( $_POST['_billing_first_name'] );
    $input_data['_billing_last_name']       = sanitize_text_field( $_POST['_billing_last_name'] );
    $input_data['_billing_phone']           = sanitize_text_field( $_POST['_billing_phone'] );
    $input_data['_billing_email']           = sanitize_email( $_POST['_billing_email'] );
    $input_data['_billing_is_invoice']      = $_POST['_billing_is_invoice'];
    $input_data['_billing_invoice_company'] = sanitize_text_field( $_POST['_billing_invoice_company'] );
    $input_data['_billing_invoice_mol']     = sanitize_text_field( $_POST['_billing_invoice_mol'] );
    $input_data['_billing_invoice_eik']     = sanitize_text_field( $_POST['_billing_invoice_eik'] );
    $input_data['_billing_invoice_town']    = sanitize_text_field( $_POST['_billing_invoice_town'] );
    $input_data['_billing_invoice_address'] = sanitize_text_field( $_POST['_billing_invoice_address'] );
    $input_data['customer_note']            = sanitize_text_field( $_POST['customer_note'] );

    // Products validation.
    $posted_product_ids = array_map( 'absint', (array) ( $_POST['igs_line_product_id'] ?? [] ) );
    if ( in_array( 0, $posted_product_ids, true ) ) {
      $errors[] = 'deleted_products';
    }
    if ( empty( array_filter( $posted_product_ids ) ) ) {
      $errors[] = 'no_products';
    }

    if ( empty( $input_data['_billing_first_name'] ) ) $errors[] = 'first_name';
    if ( empty( $input_data['_billing_last_name'] ) ) $errors[]  = 'last_name';
    if ( empty( $input_data['_billing_phone'] ) ) $errors[]      = 'phone_number';

    if ( empty( $input_data['_billing_email'] ) ) {
      $errors[] = 'email_required';
    } elseif ( ! is_email( $input_data['_billing_email'] ) ) {
      $errors[] = 'email_invalid';
    }

    if ( isset($_POST['start_timestamp_utc'] ) ) {
      if( empty( $_POST['start_timestamp_utc'] ) ) {
        $errors[] = 'start_date';
      } else {
        $_POST['start_timestamp_utc'] = strtotime( $_POST['start_timestamp_utc'] );;
      }
    }

    if ( isset($_POST['next_payment_timestamp_utc'] ) ) {
      if( empty( $_POST['next_payment_timestamp_utc'] ) ) {
        $errors[] = 'next_date';
      } else {
        $_POST['next_payment_timestamp_utc'] = strtotime( $_POST['next_payment_timestamp_utc'] . '06:00:00' );
      }
    }

    if ( isset($_POST['end_timestamp_utc'] ) ) {
      if( empty( $_POST['end_timestamp_utc'] ) ) {
        $_POST['end_timestamp_utc'] = 0;
      } else {
        $_POST['end_timestamp_utc'] = strtotime( $_POST['end_timestamp_utc'] );;
      }
    }

    if ( isset( $input_data['_billing_is_invoice'] ) && $input_data['_billing_is_invoice'] ) {
      if ( empty ( $input_data['_billing_invoice_company'] ) ) $errors[] = 'invoice_company';
      if ( empty ( $input_data['_billing_invoice_mol'] ) ) $errors[] = 'invoice_mol';
      if ( empty ( $input_data['_billing_invoice_eik'] ) ) $errors[] = 'invoice_eik';
      if ( empty ( $input_data['_billing_invoice_town'] ) ) $errors[] = 'invoice_town';
      if ( empty ( $input_data['_billing_invoice_address'] ) ) $errors[] = 'invoice_address';
    }

    $is_prepaid      = ! empty( $_POST['_igs_prepaid'] );
    $prepaid_cycles  = $is_prepaid ? absint( $_POST['_igs_prepaid_cycles'] ?? 0 ) : 0;
    $prepaid_until   = $is_prepaid ? sanitize_text_field( wp_unslash( $_POST['_igs_prepaid_until'] ?? '' ) ) : '';

    // If cycles are provided, auto-calculate "paid until" from next_payment + cycles × billing period.
    if ( $is_prepaid && $prepaid_cycles > 0 ) {
      $next_raw        = $subscription->get_date( 'next_payment' ); // UTC MySQL datetime
      $billing_period  = $subscription->get_billing_period();       // month, week, day, year
      $billing_interval = (int) $subscription->get_billing_interval();
      $until_dt_calc   = new DateTime( $next_raw, new DateTimeZone( 'UTC' ) );
      $until_dt_calc->modify( '+' . ( $prepaid_cycles * $billing_interval ) . ' ' . $billing_period . 's' );
      $prepaid_until   = $until_dt_calc->format( 'd.m.Y' );
    }

    if ( $is_prepaid && '' === trim( $prepaid_until ) ) {
      $errors[] = 'prepaid_until';
    }

    if ( ! empty( $errors ) ) {
      set_transient('igs_edit_subscription_data_' . $sub_id, $input_data, 300);
      wp_safe_redirect( add_query_arg( 'errors', implode( ',', $errors ), $base_url ) );
      exit;
    }

    WCS_Meta_Box_Subscription_Data::save( $sub_id, get_post( $sub_id ) );
    WCS_Meta_Box_Schedule::save( $sub_id, get_post( $sub_id ) );

    if ( isset( $_POST['payment_method'] ) ) {
      $new_payment_method = sanitize_text_field( $_POST['payment_method'] );
      $gateways           = WC()->payment_gateways()->payment_gateways();

      $subscription->set_payment_method( $new_payment_method );

      if ( isset( $gateways[ $new_payment_method ] ) ) {
        $subscription->set_payment_method_title( $gateways[ $new_payment_method ]->get_title() );
      }
    }

    if ( isset( $_POST['shipping_method'] ) ) {
      $new_method_id = sanitize_text_field( $_POST['shipping_method'] );

      // Remove all existing shipping items first to avoid duplicates when the
      // method changes (e.g. Speedy → Econt).
      foreach ( $subscription->get_items( 'shipping' ) as $item_id => $item ) {
        $subscription->remove_item( $item_id );
      }

      $all_available_methods = array(
        'local_pickup'           => 'Local Pickup',
        'speedy_shipping_method' => 'Speedy',
        'econt_shipping_method'  => 'Econt Express'
      );
      $method_title = isset( $all_available_methods[ $new_method_id ] ) ? $all_available_methods[ $new_method_id ] : $new_method_id;

      $shipping_item = new WC_Order_Item_Shipping();
      $shipping_item->set_method_id( $new_method_id );
      $shipping_item->set_method_title( $method_title );
      $subscription->add_item( $shipping_item );
      $shipping_item->save();

      $subscription->add_order_note( sprintf( 'Shipping method changed to: %s', $method_title ) );
    }

    // Update product line items.
    if ( ! empty( $posted_product_ids ) ) {
      foreach ( $subscription->get_items() as $item_id => $item ) {
        $subscription->remove_item( $item_id );
      }

      $posted_quantities = array_map( 'absint', (array) ( $_POST['igs_line_qty'] ?? [] ) );
      $user              = new IGS_CS_User( $subscription->get_user_id() );
      $price_list        = $user->igs_get_price_list();

      foreach ( $posted_product_ids as $idx => $product_id ) {
        if ( ! $product_id ) {
          continue;
        }

        $product = wc_get_product( $product_id );
        if ( ! $product ) {
          continue;
        }

        $qty   = max( 1, $posted_quantities[ $idx ] ?? 1 );
        $price = (float) $product->get_price();

        if ( $price_list ) {
          $custom = $product->get_meta( '_list_price_' . $price_list );
          if ( $custom !== '' && $custom !== false ) {
            $price = (float) wc_format_decimal( $custom );
          }
        }

        $line_item = new WC_Order_Item_Product();
        $line_item->set_product( $product );
        $line_item->set_quantity( $qty );
        $line_item->set_subtotal( $price * $qty );
        $line_item->set_total( $price * $qty );
        $subscription->add_item( $line_item );
      }

      $subscription->calculate_totals();
    }

    $subscription->update_meta_data( '_billing_invoice_company', $input_data['_billing_invoice_company'] );
    $subscription->update_meta_data( '_billing_invoice_mol', $input_data['_billing_invoice_mol'] );
    $subscription->update_meta_data( '_billing_invoice_eik', $input_data['_billing_invoice_eik'] );
    $subscription->update_meta_data( '_billing_invoice_vatnum', $input_data['_billing_invoice_vatnum'] );
    $subscription->update_meta_data( '_billing_invoice_mol', $input_data['_billing_invoice_mol'] );
    $subscription->update_meta_data( '_billing_invoice_town', $input_data['_billing_invoice_town'] );
    $subscription->update_meta_data( '_billing_invoice_address', $input_data['_billing_invoice_address'] );
    $subscription->set_customer_note( $input_data['customer_note'] );

    $subscription->update_meta_data( '_igs_prepaid', $is_prepaid ? '1' : '0' );
    $subscription->update_meta_data( '_igs_prepaid_until', $prepaid_until );
    $subscription->update_meta_data( '_igs_prepaid_cycles', $prepaid_cycles );
    $subscription->update_meta_data( '_igs_fixed_renewal_day', ! empty( $_POST['_igs_fixed_renewal_day'] ) ? '1' : '0' );

    $subscription->save();

    wp_redirect( admin_url( 'admin.php?page='. IGS_CS()->admin()->menus()->get_subscriptions_slug() .'&action=edit&id=' . $sub_id . '&updated=true' ) );
    exit;

  }

  /**
   * Handle the "Create New Subscription" form submission.
   *
   * @since 1.0.0
   * @return void
   */
  public static function igs_handle_create_subscription() {

    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die( __( 'You do not have sufficient permissions to access this page.', 'igs-client-system' ) );
    }

    check_admin_referer( 'igs_create_subscription_action', 'igs_create_subscription_nonce' );

    $new_sub_page = admin_url( 'admin.php?page=' . IGS_CS()->admin()->menus()->get_new_subscription_slug() );

    // ── Sanitise input ────────────────────────────────────────────────────────
    $first_name      = sanitize_text_field( $_POST['_billing_first_name'] ?? '' );
    $last_name       = sanitize_text_field( $_POST['_billing_last_name']  ?? '' );
    $phone           = sanitize_text_field( $_POST['_billing_phone']      ?? '' );
    $email           = sanitize_email( $_POST['_billing_email']           ?? '' );
    $customer_note   = sanitize_text_field( $_POST['customer_note']       ?? '' );

    $billing_interval = absint( $_POST['_billing_interval'] ?? 1 );
    $billing_period   = sanitize_text_field( $_POST['_billing_period'] ?? 'month' );
    $start_date_raw   = sanitize_text_field( $_POST['start_timestamp_utc']        ?? '' );
    $next_date_raw    = sanitize_text_field( $_POST['next_payment_timestamp_utc'] ?? '' );

    $payment_method  = sanitize_text_field( $_POST['payment_method']   ?? '' );
    $shipping_method = sanitize_text_field( $_POST['shipping_method']  ?? '' );

    $is_invoice        = ! empty( $_POST['_billing_is_invoice'] ) ? '1' : '';
    $invoice_company   = sanitize_text_field( $_POST['_billing_invoice_company']  ?? '' );
    $invoice_mol       = sanitize_text_field( $_POST['_billing_invoice_mol']       ?? '' );
    $invoice_eik       = sanitize_text_field( $_POST['_billing_invoice_eik']       ?? '' );
    $invoice_vatnum    = sanitize_text_field( $_POST['_billing_invoice_vatnum']    ?? '' );
    $invoice_town      = sanitize_text_field( $_POST['_billing_invoice_town']      ?? '' );
    $invoice_address   = sanitize_text_field( $_POST['_billing_invoice_address']  ?? '' );

    $posted_product_ids = array_map( 'absint', (array) ( $_POST['igs_line_product_id'] ?? [] ) );
    $posted_quantities  = array_map( 'absint', (array) ( $_POST['igs_line_qty']        ?? [] ) );

    // ── Validation ────────────────────────────────────────────────────────────
    $errors = array();

    if ( empty( $first_name ) ) $errors[] = 'first_name';
    if ( empty( $last_name  ) ) $errors[] = 'last_name';
    if ( empty( $phone      ) ) $errors[] = 'phone_number';

    if ( empty( $email ) ) {
      $errors[] = 'email_required';
    } elseif ( ! is_email( $email ) ) {
      $errors[] = 'email_invalid';
    }

    if ( empty( $start_date_raw ) ) {
      $errors[] = 'start_date';
    }
    if ( empty( $next_date_raw ) ) {
      $errors[] = 'next_date';
    }

    if ( empty( array_filter( $posted_product_ids ) ) ) {
      $errors[] = 'no_products';
    }

    if ( $is_invoice ) {
      if ( empty( $invoice_company ) ) $errors[] = 'invoice_company';
      if ( empty( $invoice_mol     ) ) $errors[] = 'invoice_mol';
      if ( empty( $invoice_eik     ) ) $errors[] = 'invoice_eik';
      if ( empty( $invoice_town    ) ) $errors[] = 'invoice_town';
      if ( empty( $invoice_address ) ) $errors[] = 'invoice_address';
    }

    if ( ! empty( $errors ) ) {
      wp_safe_redirect( add_query_arg( 'errors', implode( ',', $errors ), $new_sub_page ) );
      exit;
    }

    // ── Resolve or create the WP user ─────────────────────────────────────────
    $user_id       = null;
    $existing_user = get_user_by( 'email', $email );

    if ( $existing_user ) {
      $user_id = $existing_user->ID;

      // Check whether this user already has a subscription.
      $existing_subs = wcs_get_subscriptions( array(
        'customer_id' => $user_id,
        'subscription_status' => 'any',
      ) );

      if ( ! empty( $existing_subs ) ) {
        $first_sub = reset( $existing_subs );
        wp_safe_redirect( add_query_arg( array(
          'notice' => 'existing_subscription',
          'sub_id' => $first_sub->get_id(),
        ), $new_sub_page ) );
        exit;
      }
    } else {
      // Create a new WP user; wc_create_new_customer() sends a welcome e-mail.
      $username = wc_create_new_customer_username( $email );
      $user_id  = wc_create_new_customer( $email, $username, '', array(
        'first_name' => $first_name,
        'last_name'  => $last_name,
      ) );

      if ( is_wp_error( $user_id ) ) {
        wp_die( esc_html( $user_id->get_error_message() ) );
      }
    }

    // ── Convert dates ─────────────────────────────────────────────────────────
    $start_dt = DateTime::createFromFormat( 'd.m.Y', $start_date_raw );
    $next_dt  = DateTime::createFromFormat( 'd.m.Y', $next_date_raw );

    $start_utc = $start_dt ? $start_dt->format( 'Y-m-d 00:00:00' ) : '';
    $next_utc  = $next_dt  ? $next_dt->format(  'Y-m-d 06:00:00' ) : '';

    // ── Create the subscription ───────────────────────────────────────────────
    $subscription = wcs_create_subscription( array(
      'customer_id'      => $user_id,
      'status'           => 'active',
      'billing_period'   => $billing_period,
      'billing_interval' => $billing_interval,
      'start_date'       => $start_utc,
    ) );

    if ( is_wp_error( $subscription ) ) {
      wp_die( esc_html( $subscription->get_error_message() ) );
    }

    // ── Dates ─────────────────────────────────────────────────────────────────
    if ( $next_utc ) {
      $subscription->update_dates( array( 'next_payment' => $next_utc ) );
    }

    // ── Billing / shipping address ────────────────────────────────────────────
    $subscription->set_billing_first_name( $first_name );
    $subscription->set_billing_last_name(  $last_name );
    $subscription->set_billing_phone(      $phone );
    $subscription->set_billing_email(      $email );
    $subscription->set_customer_note(      $customer_note );

    // ── Payment method ────────────────────────────────────────────────────────
    if ( $payment_method ) {
      $gateways = WC()->payment_gateways()->payment_gateways();
      $subscription->set_payment_method( $payment_method );
      if ( isset( $gateways[ $payment_method ] ) ) {
        $subscription->set_payment_method_title( $gateways[ $payment_method ]->get_title() );
      }
    }

    // ── Shipping method ───────────────────────────────────────────────────────
    if ( $shipping_method ) {
      $all_available_methods = array(
        'local_pickup'           => 'Local Pickup',
        'speedy_shipping_method' => 'Speedy',
        'econt_shipping_method'  => 'Econt Express',
      );
      $method_title = $all_available_methods[ $shipping_method ] ?? $shipping_method;

      $shipping_item = new WC_Order_Item_Shipping();
      $shipping_item->set_method_id( $shipping_method );
      $shipping_item->set_method_title( $method_title );
      $subscription->add_item( $shipping_item );
      $shipping_item->save();
    }

    // ── Products ──────────────────────────────────────────────────────────────
    $user      = new IGS_CS_User( $user_id );
    $price_list = $user->igs_get_price_list();

    foreach ( $posted_product_ids as $idx => $product_id ) {
      if ( ! $product_id ) continue;

      $product = wc_get_product( $product_id );
      if ( ! $product ) continue;

      $qty   = max( 1, $posted_quantities[ $idx ] ?? 1 );
      $price = (float) $product->get_price();

      if ( $price_list ) {
        $custom = $product->get_meta( '_list_price_' . $price_list );
        if ( $custom !== '' && $custom !== false ) {
          $price = (float) wc_format_decimal( $custom );
        }
      }

      $line_item = new WC_Order_Item_Product();
      $line_item->set_product( $product );
      $line_item->set_quantity( $qty );
      $line_item->set_subtotal( $price * $qty );
      $line_item->set_total( $price * $qty );
      $subscription->add_item( $line_item );
    }

    $subscription->calculate_totals();

    // ── Invoice meta ──────────────────────────────────────────────────────────
    $subscription->update_meta_data( '_billing_is_invoice',       $is_invoice );
    $subscription->update_meta_data( '_billing_invoice_company',  $invoice_company );
    $subscription->update_meta_data( '_billing_invoice_mol',      $invoice_mol );
    $subscription->update_meta_data( '_billing_invoice_eik',      $invoice_eik );
    $subscription->update_meta_data( '_billing_invoice_vatnum',   $invoice_vatnum );
    $subscription->update_meta_data( '_billing_invoice_town',     $invoice_town );
    $subscription->update_meta_data( '_billing_invoice_address',  $invoice_address );

    $subscription->save();

    // ── Redirect to edit page ─────────────────────────────────────────────────
    wp_redirect( admin_url( 'admin.php?page=' . IGS_CS()->admin()->menus()->get_subscriptions_slug() . '&action=edit&id=' . $subscription->get_id() . '&created=true' ) );
    exit;

  }
}
