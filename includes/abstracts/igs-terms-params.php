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

abstract class IGS_CS_Terms_Params extends IGS_CS_Params {

  /**
	 * Get the default allowed params.
	 *
	 * @return array
	 */
	protected function get_default_params() {

		return array(
      'igs_type'     => '1',
      'igs_orderby'  => 'name',
      'igs_order'    => 'DESC',
      'igs_results'  => '',
      'igs_per_page' => '',
		);

	}

  /**
	 * Create a new query.
	 *
	 * @param array $args Criteria to query on in a format similar to WP_Query.
	 */
	public function __construct( $params = array() ) {

		parent::__construct( $params );

	}

  /**
	 * Set a Taxonomy.
	 */
	protected function igs_taxonomy() {

		return [ 'taxonomy' => $this->get_param('igs_taxonomy') ];

	}

	protected function igs_object() {

		return ['object_ids' => $this->get_param('igs_object') ];

	}

  protected function igs_search() {

    return ['search' => $this->get_param( 'igs_search' )];

  }

	/**
	 * Set a Show only Parant.
	 */
	protected function igs_only_parent() {

		if ( $this->get_param('igs_only_parent') )
			return [ 'parent' => 0 ];

		return [];

	}

	/**
	 * Set a Parent.
	 */
	protected function igs_parent() {
		return [ 'parent' => $this->get_param('igs_parent') ];
	}

  /**
	 * Set a Include Terms.
	 */
	protected function igs_terms() {

    $terms = igs_array_format(  $this->get_params(), 'igs_terms' );

		return [ 'include' => $terms ];
	}

	/**
	 * Set a Exclude Terms.
	 */
	protected function igs_not_terms() {

    $terms = $this->get_param('igs_not_terms');

    if ( ! is_array( $terms )) {
      $arr = array();
      for ($i = 0; $i < $terms; $i++) {
        $arr[$i] = $this->params['igs_terms_' . $i];
      }

      $this->set_param('igs_not_terms',  $arr);
    }

		return [ 'exclude' => $this->get_param('igs_not_terms') ];
	}

  /**
	 * Set a Hide Empty.
	 */
	protected function igs_hide_empty() {

		return [ 'hide_empty' => $this->get_param('igs_hide_empty') ];

	}

	/**
	 * Set a Number.
	 */
	protected function set_number() {

    $results  = $this->get_param('igs_results', 0);
    $per_page = $this->get_param('igs_per_page', 0);

    if ( $per_page == 0 && $results == 0 ) {
      return [ 'number' => 0 ];
    }

		$offset = $this->get_param('igs_offset');

		if ( $offset && ! empty( $results ) && $offset * 2 > $results ) {
			$results -= $offset;
		}

		if ( $results == 0 || $per_page && $results > $per_page ) {
			return [ 'number' => $per_page ];
    } else {
			return [ 'number' => $results ];
    }

  }

	protected function igs_results() {

		return $this->set_number();

	}

	/**
	 * Set a Order Meta Key
	 */
	protected function set_order_meta_key( ) {
    $orderby = $this->get_orderby();

    if ( 'post_type_count' === $orderby ) {
			$meta_key = '_igs_' . sanitize_key( $this->get_param('igs_posttype') ) . '_count';
      $this->set_param('igs_meta_key', $meta_key);
    } elseif ( 'casinos' === $orderby ) {
			$this->set_param('igs_meta_key', '_igs_post_count');
		}

		return[];

	}

  /**
	 * Set a OrderBy
	 */
  protected function set_orderby( $orderby ) {

    if (
			'post_type_count' === $orderby ||
			'casinos'         === $orderby
		) {
      $orderby = 'meta_value_num';
    }

    return [ 'orderby' => $orderby ];

  }

	/**
	 * Set the meta query params.
	 *
	 * @return array
	 */
	protected function set_meta_params() {

		if ( $this->get_param( 'igs_type' ) !== '3' )
			return;

		$meta_args = array(
			'igs_casinos' => $this->set_meta_query_param('_igs_post_count', $this->get_param('igs_casinos'), '>=', 'NUMERIC')
		);

		if ( $this->get_orderby() === 'post_type_count' ) {
			$meta_key = '_igs_' . sanitize_key( $this->get_param('igs_posttype') ) . '_count';
			$meta_args[$meta_key] = $this->set_meta_query_param($meta_key, $this->get_param($meta_key), '>', 'NUMERIC');
		}

    return apply_filters( 'igs_terms_meta_params', $meta_args );

	}

}
