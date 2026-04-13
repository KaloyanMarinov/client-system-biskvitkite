<?php

/**
 *
 * @link       https://igamingsolutions.com
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Queries
 *
 */
defined( 'ABSPATH' ) || exit;

class IGS_CS_Users_Query extends IGS_CS_Users_Params {

  /**
	 * Users Params data.
	 *
	 * @var array
	 */
	protected $query_args = array();

	/**
	 * Users data.
	 *
	 * @var WP_User_Query
	 */
	protected $query;

  protected function igs_formatted_args() {
    $query_args  = array();
    $params_keys = array_keys( $this->params );

    foreach ($params_keys as $key) {

			if ( method_exists( $this, $key ) ) {
        $value = $this->$key();

				if ( is_null( $value ) )
					return;

        $query_args = array_merge( $query_args, $value );
      } elseif ( ! is_null( $this->igs_get_param( $key ) ) && $this->igs_get_meta_query() && $this->igs_get_meta_param( $key ) ) {
        $query_args['meta_query'][] = $this->igs_get_meta_param( $key ) ;
      }
    }

    return $query_args;
  }


	/**
	 * Get the default query params.
	 *
	 * @return array
	 */
	protected function igs_get_default_query_args() {

		return array(  );

	}

	/**
	 * Create a new query.
	 *
	 * @param array $params Criteria to query on in a format similar to WP_Query.
	 */
	public function __construct( $params = array() ) {

    parent::__construct( $params );

		$this->igs_set_query_args();
		$this->igs_set_paged();

		$this->query = new WP_User_Query( $this->igs_get_query_args() );

	}


	/**
	 * Get the current params.
	 *
	 * @return array
	 */
	public function igs_get_query_args() {

		return apply_filters( 'igs_users_query_args', $this->query_args );

	}

	/**
	 * Get the value of a params variable.
	 *
	 * @param string $arg query_args variable to get value for.
	 * @param mixed  $default Default value if query variable is not set.
	 * @return mixed Query args variable value if set, otherwise default.
	 */
	public function igs_get_query_arg( $arg, $default = '' ) {

		if ( isset( $this->query_args[ $arg ] ) ) {
			return $this->query_args[ $arg ];
		}

		return $default;

	}

	/**
	 * Get WP_Query
	 *
	 * @return WP_User_Query
	 */
  public function igs_get_query() {

    return $this->query;

	}

  /*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Functions for getting params data.
	*/
  /**
	 * Set a query args variable.
	 */
	protected function igs_set_query_args() {

		$formatted_args = $this->igs_formatted_args();

		if ( ! is_null( $formatted_args ) ) {
			$this->query_args = wp_parse_args( $formatted_args, $this->igs_get_default_query_args() );
		} else {
			$this->query_args = $formatted_args;
		}

	}

	/**
	 * Set a param variable.
	 *
	 * @param string $arg Query_args variable to set.
	 * @param mixed  $value Value to set for param variable.
	 */
	protected function igs_set_query_arg( $arg, $value ) {

		$this->query_args[ $arg ] = $value;

	}

  protected function igs_set_paged( ) {

    $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;

    $this->igs_set_query_arg( 'paged',  $paged);

	}

  protected function igs_set_meta_params() {

		$meta_params = array();
    $price_list = $this->igs_get_param('igs_price_list');

		if ( ! is_null( $price_list ) ) {
      $meta_params['igs_price_list'] = $this->igs_set_meta_param('price_list', $price_list, '=', 'NUMBER');
    }

    return apply_filters( 'igs_subscriptions_meta_params', $meta_params );

	}

}
