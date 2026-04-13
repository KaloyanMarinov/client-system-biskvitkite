<?php

/**
 * Admin Order Handler.
 *
 * Handles all admin menu registrations for the plugin.
 *
 * @since      1.0.0
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/admin
 */
class IGS_CS_Admin_Order {

  /**
	 * The single instance of the class.
	 *
	 * @var IGS_CS_Admin_Order
	 * @since 1.0.0
	 */
	private static $_instance = null;

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

  public function get_shipping_address( $order = null ) {

    if ( ! $order )
      return;

    if ( is_numeric( $order ) ) {
      if ( ! $order = wc_get_order( $order ) )
        return;
    }

    if( ! $shipping_methods = $order->get_shipping_methods() )
      return;

    $current = reset( $shipping_methods );

    if ( str_contains( $current->get_method_id(), 'econt') ) {
      return $this->get_econt_address( $order );
    } elseif ( str_contains($current->get_method_id(), 'speedy' ) ) {
      return $this->get_speedy_address( $order );
    }

    return;

  }

  public function get_econt_address( $order ) {

    if ( ! $order )
      return;

    if ( is_numeric( $order ) ) {
      if ( ! $order = wc_get_order( $order ) )
        return;
    }

    if ( ! class_exists( 'Econt_mySQL' ) )
      return;

    $getoffice   = new Econt_mySQL;
    $shipping_to = $order->get_meta( 'Econt_Shipping_To', true );

    if ( $shipping_to == 'OFFICE' ) {
      $office_code = $order->get_meta( 'Econt_Office', true );
      $office      = $getoffice->getOfficeByOfficeCode($office_code);

      $address[]   = __( 'Shipping to office', 'igs-client-system' );
      $address[] = __('Office', 'woocommerce-econt') . ': ' . $office['name'];
      $address[] = __('Address', 'igs-client-system') . ': ' . $office['address'];
      $address[] = __('Postcode', 'woocommerce-econt') . ': ' . $order->get_meta( 'Econt_Office_Postcode', true );

      return $address;

    } elseif( $shipping_to == 'MACHINE' ) {
      $machine_code = $order->get_meta( 'Econt_Machine', true );
      $machine      = $getoffice->getOfficeByOfficeCode($machine_code);

      $address[]   = __( 'Shipping to APS', 'igs-client-system' );
      $address[] = __('Office', 'woocommerce-econt') . ': ' . $machine['name'];
      $address[] = __('Address', 'igs-client-system') . ': ' . $machine['address'];
      $address[] = __('Postcode', 'woocommerce-econt') . ': ' . $order->get_meta( 'Econt_Machine_Postcode', true );

      return $address;

      } elseif( $shipping_to == 'DOOR' ) {

      $address[]   = __( 'Shipping to address', 'igs-client-system' );

      $address[] = $order->get_meta( 'Econt_Door_Postcode', true ) . ' ' . $order->get_meta( 'Econt_Door_Town', true );

      if ( $quarter = $order->get_meta( 'Econt_Door_Quarter', true ) ) {
        $address[] = __('Quarter', 'woocommerce-econt') . ': ' . $quarter ;
      }

      if ( $building_num = $order->get_meta( 'Econt_Door_building_num', true ) ) {
        $address[] = __('Building num', 'woocommerce-econt') . ': ' . $building_num ;
      }

      if ( $street = $order->get_meta( 'Econt_Door_Street', true ) ) {
        $address[] = __('Street', 'woocommerce-econt') . ': ' . $street ;
      }

      if ( $street_num = $order->get_meta( 'Econt_Door_street_num', true ) ) {
        $address[] = __('Street num', 'woocommerce-econt') . ': ' . $street_num ;
      }

      if ( $notes = $order->get_meta( 'Econt_Door_Other', true ) ) {
        $address[] = __('Notes', 'woocommerce-econt') . ': ' . $notes ;
      }

      return $address;

    }

    return;

  }

  public function get_speedy_address( $order ) {

    if ( ! $order )
      return;

    if ( is_numeric( $order ) ) {
      if ( ! $order = wc_get_order( $order ) )
        return;
    }

    $shipping_to = $order->get_meta( 'speedy_shipping_to', true );

    if ( $shipping_to == 'OFFICE' ) {

      $address[] = __( 'Shipping to office', 'igs-client-system' );
      $address[] = __('Locality', 'woocommerce-speedy') . ': ' . $order->get_meta( 'speedy_site_name', true );
      $address[] = __('Office', 'woocommerce-speedy') . ': ' . $order->get_meta( 'speedy_pickup_office_name', true );
      $address[] = __('Postcode', 'woocommerce-speedy') . ': ' . $order->get_meta( 'speedy_post_code', true );

      return $address;

    } elseif( $shipping_to == 'APT' ) {

      $address[] = __( 'Shipping to APS', 'igs-client-system' );
      $address[] = __('Locality', 'woocommerce-speedy') . ': ' . $order->get_meta( 'speedy_site_name', true );
      $address[] = __('Address', 'igs-client-system') . ': ' . $order->get_meta( 'speedy_pickup_apt_name', true );
      $address[] = __('Postcode', 'woocommerce-speedy') . ': ' . $order->get_meta( 'speedy_post_code', true );

      return $address;

      } elseif( $shipping_to == 'ADDRESS' ) {

      $address[] = __( 'Shipping to address', 'igs-client-system' );
      $address[] = __('Locality', 'woocommerce-speedy') . ': ' . $order->get_meta( 'speedy_site_name', true );

      if ( $street = $order->get_meta( 'speedy_street_name', true ) ) {
        $address[] = __('Street', 'woocommerce-speedy') . ': ' . $street ;
      }

      if ( $street_num = $order->get_meta( 'speedy_street_no', true ) ) {
        $address[] = __('Street num', 'woocommerce-speedy') . ': ' . $street_num ;
      }

      if ( $notes = $order->get_meta( 'speedy_address_note', true ) ) {
        $address[] = __('Address note', 'woocommerce-speedy') . ': ' . $notes ;
      }

      return $address;

    }

    return;

  }

}
