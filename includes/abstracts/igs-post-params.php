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

abstract class IGS_CS_Post_Params extends IGS_CS_Params {

  /**
	 * Stores Params data.
	 *
	 * @var array
	 */
	protected $tax_query = array();

  /**
	 * Get the default allowed params.
	 *
	 * @return array
	 */
	protected function igs_get_default_params() {

		return array(
			'igs_post_type'     => 'post',
			'igs_type'          => '1',
			'igs_orderby'       => 'date',
			'igs_order'         => 'DESC',
			'igs_status'        => 'publish',
			'igs_fields'        => 'ids',
			'igs_results'       => 24,
			'igs_per_page'      => 24,
			'igs_no_found_rows' => '',
			'igs_post_not_in'   => false
		);

	}

  /**
	 * Formt the taxonomies params.
	 *
	 * @return array
	 */
	protected function igs_format_array_tax_params( $key ) {

		if ( ! is_array( $this->igs_get_param($key) ) && $this->igs_get_param( $key . '_0', false ) ) {
      $values = [];
      for ( $i=0; $i < $this->igs_get_param($key); $i++  ) {
        $values[] = $this->igs_get_param( $key . '_' . $i );
        $this->igs_delete_param( $key . '_' . $i);
      }

      $this->igs_set_param($key, $values);
    }

    return $this->igs_get_param($key);

	}

  /**
	 * Create a new query.
	 *
	 * @param array $args Criteria to query on in a format similar to WP_Query.
	 */
	public function __construct( $params = array() ) {

    $params = apply_filters( 'igs_post_params', wp_parse_args( $params, $this->igs_get_default_params() ) );

		parent::__construct( $params );

    $this->tax_query = $this->igs_set_taxonomies_params();

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
	public function igs_get_tax_query() {

		return $this->tax_query;

	}

  /**
	 * Get the value of a meta params variable.
	 *
	 * @param string $param Params variable to get value for.
	 * @param mixed  $default Default value if query variable is not set.
	 * @return mixed Param variable value if set, otherwise default.
	 */
	public function igs_get_tax_query_param( $param, $default = null ) {

		if ( isset( $this->tax_query[ $param ] ) ) {
			return $this->tax_query[ $param ];
		}

		return $default;

	}

  /**
	 * Set a Post Type.
	 *
	 * @param string $param Params variable to set.
	 */
	protected function igs_post_type( ) {

    return array( 'post_type' => $this->igs_get_param('igs_post_type') );

	}

  protected function igs_no_found_rows() {

		if ( is_bool( $this->igs_get_param( 'igs_no_found_rows' ) ) )
			return array( 'no_found_rows' => $this->igs_get_param( 'igs_no_found_rows' ) );

		$results  = $this->igs_get_param('igs_results', 0);
		$per_page = $this->igs_get_param('igs_per_page', 0);
		$bool     = true;

		if ( $this->igs_get_param('igs_filter') ) {
			$bool = false;
		}

		if ( $results != $per_page && $per_page != 0 ) {
			$bool = false;
		}

		return array( 'no_found_rows' => $bool );

  }

  protected function igs_search() {

    return array( 's' => $this->igs_get_param( 'igs_search' ) );

  }

  /**
	 * Set a Not IN posts.
	 *
	 * @param string $param Params variable to set.
	 */
  protected function igs_post_not_in() {

    global $post;
		/** @var \WP_Post $post */

		if ( ! $post || $post->post_type !== $this->igs_get_param('igs_post_type') )
			return array();

    return array( 'post__not_in' => array( $post->ID ) );

  }

  /**
	 * Set a author variable.
	 */
	protected function igs_author( ) {

    return array( 'author' => $this->igs_get_param('igs_author') );

	}

  /**
	 * Set a post status variable.
	 */
	protected function igs_status( ) {

    return array( 'post_status' => $this->igs_get_param('igs_status') );

	}

	/**
	 * Set a post parent variable.
	 */
	protected function igs_post_parent() {

		return array( 'post_parent' => $this->igs_get_param( 'igs_post_parent' ) );
	}

  /**
	 * Set a Post In variable.
	 */
	protected function igs_manually() {

		if ( ! $post__ids = $this->igs_get_param( 'igs_manually' ) )
      return array();

    if ( ! is_array( $post__ids ) )
      $post__ids = array( $post__ids );

		return array( 'post__in' => $post__ids );

	}

  protected function set_posts_per_page() {

    $results  = $this->igs_get_param('igs_results', 0);
    $per_page = $this->igs_get_param('igs_per_page', 0);

    if ( $per_page == 0 && $results == 0 ) {
      return array( 'posts_per_page' => -1 );
    }

		if ( $results == 0 || ( $per_page && $results > $per_page ) ) {
			return array( 'posts_per_page' => $per_page );
    } else {
			return array( 'posts_per_page' => $results );
    }

  }

  protected function igs_results() {

    return $this->set_posts_per_page();

	}

  protected function igs_per_page() {

    return $this->set_posts_per_page();

	}

  /*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting params data.
  */

  /**
	 * Set the taxonomies params.
	 *
	 * @return array
	 */
	protected function igs_set_taxonomies_params() {

		return apply_filters( 'igs_taxonomies_params', null );

	}

  /**
	 * Set the meta param.
	 *
	 * @return array
	 */
	protected function igs_set_tax_query_param(
    $taxonomy,
    $param,
    $field = 'term_id',
    $operator = 'IN',
    $include_children = false
  ) {

    if ( ! $this->igs_get_param( $param ) && 'EXISTS' !== $operator )
      return;

		return array(
			'taxonomy'         => $taxonomy,
			'field'            => $field,
			'terms'            => $this->igs_format_array_tax_params($param),
			'operator'         => $operator,
			'include_children' => $include_children
    );

	}

}
