<?php

/**
 * Shipping Integration.
 *
 * Handles recalculation of Speedy and Econt shipping costs after a
 * manual subscription renewal.  Extracted from IGS_CS_Admin_Hooks so
 * that all third-party courier logic lives in one dedicated file.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Admin/Integrations
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Shipping_Integration {

  /**
   * Singleton instance.
   *
   * @var IGS_CS_Shipping_Integration|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Shipping_Integration
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct() {}

  /**
   * Dispatch to the correct courier handler based on the shipping method
   * attached to the renewal order.
   *
   * @since 1.0.0
   * @param int      $order_id
   * @param WC_Order $order
   * @return void
   */
  public function update_delivery_price( $order_id, $order ) {

    foreach ( $order->get_items( 'shipping' ) as $item_id => $item ) {
      $method_id = $item->get_method_id();

      if ( 'speedy_shipping_method' === $method_id ) {
        $this->update_speedy_price( $order_id, $order, $item );
      } elseif ( 'econt_shipping_method' === $method_id ) {
        $this->update_econt_price( $order_id, $order, $item );
      }
    }

  }

  /**
   * Recalculate and persist Speedy shipping cost for a renewal order.
   *
   * @since 1.0.0
   * @param int                    $order_id
   * @param WC_Order               $order
   * @param WC_Order_Item_Shipping  $item
   * @return void
   */
  public function update_speedy_price( $order_id, $order, $item ) {

    $speedy_mysql   = new Speedy_mySQL();
    $speedy_admin   = new Speedy_Admin_Order();
    $loading_data   = $speedy_mysql->default_loading_data( $order_id );
    $order_wp       = $speedy_admin->speedy_order_products( $order_id );

    $loading_data['speedy_cod_amount'] = $order_wp['cod_price'];
    $loading_data['speedy_dv_amount']  = $order_wp['dv_price'];
    $loading_data['speedy_weight']     = $order_wp['weight'];
    $loading_data['service_payer']     = $order_wp['payer'];
    $loading_data['action']            = 'speedy_handle_ajax';
    $loading_data['action2']           = 'calculation';

    $url    = get_bloginfo( 'wpurl' ) . '/wp-admin/admin-ajax.php';
    $result = wp_remote_post( $url, array( 'body' => $loading_data ) );

    if ( is_wp_error( $result ) ) {
      wp_die( $result->get_error_message() );
    }

    if ( wp_remote_retrieve_response_code( $result ) !== 200 ) {
      wp_die(
        __( 'Server Response Code:', 'woocommerce-speedy' ) . ' ' .
        wp_remote_retrieve_response_code( $result )
      );
    }

    $body     = wp_remote_retrieve_body( $result );
    $response = json_decode( $body );

    foreach ( $response as $key => $value ) {

      if ( 'error' === $key || property_exists( $value, 'error' ) ) {
        continue;
      }

      $speedy_options = get_option( 'speedy_shipping_method_options' );

      if ( empty( $speedy_options['inc_shipping_cost'] ) || ! is_numeric( $speedy_options['inc_shipping_cost'] ) ) {
        $speedy_options['inc_shipping_cost'] = 1;
      }

      if ( ! empty( $speedy_options['inc_shipping_cost'] ) ) {
        $speedy_total = number_format( (float) $value->price->mrejanetRecipient, 2, '.', '' );
        $item->set_total( $speedy_total );
        $item->save();
        $order->calculate_totals();
      }

      $order->update_meta_data( 'speedy_destination_services_name', sanitize_text_field( $value->serviceName ) );
      $order->update_meta_data( 'speedy_total_price',               sanitize_text_field( number_format( (float) $value->price->mrejanetTotal,     2, '.', '' ) ) );
      $order->update_meta_data( 'speedy_recipient_price',           sanitize_text_field( number_format( (float) $value->price->mrejanetRecipient, 2, '.', '' ) ) );
      $order->update_meta_data(
        'speedy_destination_services_id',
        sanitize_text_field(
          $value->serviceId . ';' . $value->serviceName . ';' .
          number_format( (float) $value->price->mrejanetTotal,     2, '.', '' ) . ';' .
          number_format( (float) $value->price->mrejanetRecipient, 2, '.', '' ) . ';' .
          $value->price->currency
        )
      );
      $order->save();
    }

  }

  /**
   * Recalculate and persist Econt shipping cost for a renewal order.
   *
   * @since 1.0.0
   * @param int                    $order_id
   * @param WC_Order               $order
   * @param WC_Order_Item_Shipping  $item
   * @return void
   */
  public function update_econt_price( $order_id, $order, $item ) {

    $econt_mysql   = new Econt_mySQL();
    $intl_delivery = in_array( $order->get_billing_country(), array( 'RO', 'GR' ), true );

    $loading_data = array(
      'receiver_city'        => '',
      'receiver_post_code'   => '',
      'order_id'             => $order_id,
      'payment_method'       => $order->get_payment_method(),
    );

    $loading_data['order_cd'] = in_array(
      $loading_data['payment_method'],
      array( 'cod', 'econt_payment' ),
      true
    ) ? 1 : 0;

    foreach ( array( 'Door', 'Office', 'Machine' ) as $type ) {
      if ( ! $loading_data['receiver_city'] && $order->get_meta( 'Econt_' . $type . '_Town', true ) ) {
        $loading_data['receiver_city'] = $order->get_meta( 'Econt_' . $type . '_Town', true );
      }
      if ( ! $loading_data['receiver_post_code'] && $order->get_meta( 'Econt_' . $type . '_Postcode', true ) ) {
        $loading_data['receiver_post_code'] = $order->get_meta( 'Econt_' . $type . '_Postcode', true );
      }
    }

    $loading_data['receiver_office_code'] = $order->get_meta( 'Econt_Office', true ) ?: $order->get_meta( 'Econt_Machine', true ) ?: '';

    $loading_data['receiver_name'] = $order->get_billing_company()
      ?: $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

    $loading_data['receiver_name_person']  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $loading_data['receiver_email']        = $order->get_billing_email();
    $loading_data['receiver_street']       = $intl_delivery
      ? $order->get_meta( 'Econt_Door_Street_Intl', true )
      : $order->get_meta( 'Econt_Door_Street', true );
    $loading_data['receiver_quarter']      = $intl_delivery
      ? $order->get_meta( 'Econt_Door_Quarter_Intl', true )
      : $order->get_meta( 'Econt_Door_Quarter', true );
    $loading_data['receiver_street_num']   = $order->get_meta( 'Econt_Door_street_num', true );
    $loading_data['receiver_street_bl']    = $order->get_meta( 'Econt_Door_building_num', true );
    $loading_data['receiver_street_vh']    = $order->get_meta( 'Econt_Door_Entrance_num', true );
    $loading_data['receiver_street_et']    = $order->get_meta( 'Econt_Door_Floor_num', true );
    $loading_data['receiver_street_ap']    = $order->get_meta( 'Econt_Door_Apartment_num', true );
    $loading_data['receiver_street_other'] = $order->get_meta( 'Econt_Door_Other', true );
    $loading_data['receiver_phone_num']    = $order->get_billing_phone();
    $loading_data['receiver_shipping_to']  = $order->get_meta( 'Econt_Shipping_To', true );
    $loading_data['currency']              = $order->get_currency();
    $loading_data['currency_symbol']       = get_woocommerce_currency_symbol( $loading_data['currency'] );

    if ( empty( $loading_data['receiver_city'] ) ) {
      return;
    }

    $result = $econt_mysql->create_loading( $loading_data, 1 );

    if ( array_key_exists( 'warning', $result ) ) {
      return;
    }

    $econt_options = get_option( 'econt_shipping_method_options' );

    if ( ! empty( $econt_options['inc_shipping_cost'] ) ) {
      $item->set_total( $result['customer_shipping_cost'] );
      $item->save();
      $order->calculate_totals();
    }

    $order->update_meta_data( 'Econt_Customer_Shipping_Cost', sanitize_text_field( $result['customer_shipping_cost'] ) );
    $order->update_meta_data( 'Econt_Total_Shipping_Cost',    sanitize_text_field( $result['total_shipping_cost'] ) );
    $order->save();

  }

}
