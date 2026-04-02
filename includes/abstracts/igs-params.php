<?php

/**
 *
 * @link       https://igamingsolutions.com
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Abstracts
 *
 */

defined( 'ABSPATH' ) || exit;

abstract class IGS_CS_Params {

  /**
	 * Stores Params data.
	 *
	 * @var array
	 */
	protected $params = array();

  /**
	 * Stores Params data.
	 *
	 * @var array
	 */
	protected $meta_query = array();

  /**
	 * Create a new query.
	 *
	 * @param array $args Criteria to query on in a format similar to WP_Query.
	 */
	public function __construct( $params = array() ) {

    $this->params     = wp_parse_args( $params, $this->igs_get_default_params() );
    $this->meta_query = $this->igs_set_meta_params();

    $this->igs_set_order_meta_key();

	}


  /**
	 * Delete a param variable.
	 *
	 * @param string $param Params variable to set.
	 * @param mixed  $value Value to set for param variable.
	 */
	public function igs_delete_param( $param ) {

		unset( $this->params[ $param ] );

	}

  /*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Functions for getting params data.
	*/

	/**
	 * Get the current params.
	 *
	 * @return array
	 */
	public function igs_get_params() {

		return $this->params;

	}

	/**
	 * Get the value of a params variable.
	 *
	 * @param string $param Params variable to get value for.
	 * @param mixed  $default Default value if query variable is not set.
	 * @return mixed Param variable value if set, otherwise default.
	 */
	public function igs_get_param( $param, $default = null ) {

		if ( ! isset( $this->params[ $param ] ) || $this->params[ $param ] == '' )
			return $default;

		return $this->params[ $param ];

	}

  /**
	 * Get the current params.
	 *
	 * @return array
	 */
	public function igs_get_meta_query() {

		return $this->meta_query;

	}

  /**
	 * Get the value of a meta params variable.
	 *
	 * @param string $param Params variable to get value for.
	 * @param mixed  $default Default value if query variable is not set.
	 * @return mixed Param variable value if set, otherwise default.
	 */
	public function igs_get_meta_param( $param, $default = null ) {

		if ( isset( $this->meta_query[ $param ] ) ) {
			return $this->meta_query[ $param ];
		}

		return $default;

	}


  protected function igs_order() {

    return array( 'order' => $this->igs_get_param('igs_order') );

  }

  protected function igs_meta_key() {

    return array( 'meta_key' => $this->igs_get_param('igs_meta_key') );

  }

	protected function igs_meta_type() {
    return array( 'meta_type' => $this->igs_get_param('igs_meta_type') );
  }

  protected function igs_orderby() {

    return $this->igs_set_orderby( $this->igs_get_param('igs_orderby') );

  }

  /**
	 * Set a Offset variable.
	 */
	protected function igs_offset( ) {

    return array( 'offset' => $this->igs_get_param('igs_offset') );

	}

  /**
	 * Set a Fields.
	 */
	protected function igs_fields() {

		return array( 'fields' => $this->igs_get_param('igs_fields') );

	}

	public function igs_get_order() {

		return strtolower( $this->igs_get_param( 'igs_order' ) );

	}


  /*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting params data.
  */


  /**
	 * Set the meta query params.
	 *
	 * @return array
	 */
	protected function igs_set_meta_params( ) {

		return apply_filters( 'igs_meta_params', null );

	}

  /**
	 * Set a Order variable.
	 *
	 * @param string $param Params variable to set.
	 */
	protected function igs_set_order_meta_key( ) { }


  /**
	 * Set a param variable.
	 *
	 * @param string $param Params variable to set.
	 * @param mixed  $value Value to set for param variable.
	 */
	public function igs_set_param( $param, $value ) {

		$this->params[ $param ] = $value;

	}

  /**
	 * Set the meta param.
	 *
	 * @return array
	 */
	protected function igs_set_meta_param( $key, $value, $compare = '=', $type = 'CHAR' ) {

    return array(
      'key'     => $key,
      'value'   => $value,
      'compare' => $compare,
      'type'    => $type
    );

	}

  protected function igs_set_orderby( $orderby ) {

    return array( 'orderby' => $orderby );

  }
}
