<?php

/**
 *
 * @link       https://igamingsolutions.com
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Quer
 *
 */

 defined( 'ABSPATH' ) || exit;

class IGS_CS_Subscriptions_Query extends IGS_CS_Post_Query {

	/**
	 * Get the default allowed params.
	 *
	 * @return array
	 */
	protected function igs_get_default_reviews_params() {

    return array(
      'igs_post_type' => 'shop_subscription',
      'igs_status'    => 'wc-active',
      'igs_next_date' => '',
      'igs_orderby'   => 'next_date'
		);

	}

	/**
	 * Create a new query.
	 *
	 * @param array $params
	 */
	public function __construct( $params = array() ) {

    $params = apply_filters( 'igs_subscrptions_params', wp_parse_args( $params, $this->igs_get_default_reviews_params() ) );

    parent::__construct( $params );

	}

  protected function igs_set_orderby( $orderby ) {

    if ( 'next_date' == $orderby ) {
      $orderby = 'meta_value date';
    } elseif ( 'last_date' === $orderby ) {
      $orderby = 'meta_value_num date';
    }

    return [ 'orderby' => $orderby ];

  }

  protected function igs_set_order_meta_key( ) {

    $orderby = $this->igs_get_param('igs_orderby');

    if ( 'next_date' === $orderby ) {
      $this->igs_set_param('igs_meta_key', '_schedule_next_payment');
    } elseif ( 'last_data' === $orderby ) {
      $this->igs_set_param('igs_meta_key', '_last_order_date_created');
    }

	}

	protected function igs_set_meta_params() {

		$meta_params = array();

		if ( $this->igs_get_param( 'igs_type' ) == '3' || $this->igs_get_param( 'igs_type' ) == '4' ) {

			$meta_params = array(
				'igs_next_date' => $this->get_next_data_param(),
				'igs_client'    => $this->get_client_param()
      );
		}

    return apply_filters( 'igs_subscriptions_meta_params', $meta_params );

	}

  protected function get_next_data_param() {

    if ( ! $next_date = $this->igs_get_param('igs_next_date') )
      return;

    $today = date('Y-m-d');
    $compare = 'BETWEEN';
    $value = null;

    switch ($next_date) {
      case 'today':
        $value = array(
          $today . ' 00:00:00',
          $today . ' 23:59:59'
        );
        break;
      case 'this_week':
        $value = array(
          date('Y-m-d 00:00:00', strtotime('monday this week')),
          date('Y-m-d 23:59:59', strtotime('sunday this week'))
        );
        break;
      case 'next_week':
        $value = array(
          date('Y-m-d 00:00:00', strtotime('monday next week')),
          date('Y-m-d 23:59:59', strtotime('sunday next week'))
        );
        break;
      case 'this_month':
        $value = array(
          date('Y-m-01 00:00:00'),
          date('Y-m-t 23:59:59')
        );
        break;
      case 'next_month':
        $value = array(
          date('Y-m-01 00:00:00', strtotime('first day of next month')),
          date('Y-m-t 23:59:59', strtotime('last day of next month'))
        );
        break;
      case 'delayed':
      $value   = current_time('mysql');
      $compare = '<';
      break;
    }

    return $this->igs_set_meta_param('_schedule_next_payment', $value, $compare, 'DATETIME');
  }

  protected function get_client_param() {

    if ( ! $client = $this->igs_get_param('igs_client') )
      return;

    $params = array(
      'relation' => 'OR',
      $this->igs_set_meta_param('_billing_first_name', $client, 'LIKE'),
      $this->igs_set_meta_param('_billing_last_name', $client, 'LIKE'),
      $this->igs_set_meta_param('_billing_email', $client, 'LIKE'),
    );

    return $params;
  }

}
