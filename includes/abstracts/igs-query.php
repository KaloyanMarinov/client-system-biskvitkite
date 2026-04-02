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

abstract class IGS_CS_Query extends IGS_CS_Post_Params {

  /**
	 * Stores Params data.
	 *
	 * @var array
	 */
	protected $query_args = array();

	/**
	 * Stores Params data.
	 *
	 * @var WP_Query
	 */
	protected $query;

  protected function igs_formatted_args() {
    $query_args  = array();
    $params_keys = array_keys( $this->params );

    foreach ($params_keys as $key) {

			if ( method_exists( $this, $key ) ) {

				if ( is_null( $this->$key() ) )
					return;

        $query_args = array_merge( $query_args, $this->$key() );
      } elseif ( $this->igs_get_param( $key ) && $this->igs_get_tax_query() && $this->igs_get_tax_query_param( $key ) ) {
        $query_args['tax_query'][] = $this->igs_get_tax_query_param( $key ) ;
      } elseif ( ! is_null( $this->igs_get_param( $key ) ) && $this->igs_get_meta_query() && $this->igs_get_meta_param( $key ) ) {
        $query_args['meta_query'][] = $this->igs_get_meta_param( $key ) ;
      }
    }

    return $query_args;
  }

	/**
	 * Create a new query.
	 *
	 * @param array $params Criteria to query on in a format similar to WP_Query.
	 */
	public function __construct( $params = array() ) {

    parent::__construct( $params );

		$this->igs_set_query_args();

	}

  /*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Functions for getting query data.
	*/

	/**
	 * Get the current params.
	 *
	 * @return array
	 */
	public function igs_get_query_args() {

		return apply_filters( 'igs_query_args', $this->query_args );

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
	 * @return WP_Query
	 */
  public function igs_get_query() {

    return $this->query;

	}

	/**
	 * Get WP_Query Founds Posts
	 *
	 * @return int
	 */
  public function igs_get_found_posts() {

    return $this->igs_get_query()->found_posts;

  }

	/**
	 * Get WP_Query Post Count
	 *
	 * @return int
	 */
	public function igs_get_post_count() {

    return $this->igs_get_query()->post_count;

  }

	/**
	 * Get WP_Query Offset
	 *
	 * @return int
	 */
	public function igs_get_offset() {

    return $this->igs_get_query()->offset;

	}

	/**
	 * Get WP_Query Posts
	 *
	 * @return array
	 */
	public function igs_get_posts() {

    return $this->igs_get_query()->posts;

	}

  /*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Functions for getting query data.
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

}
