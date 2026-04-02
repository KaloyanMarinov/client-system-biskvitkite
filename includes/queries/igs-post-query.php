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

class IGS_CS_Post_Query extends IGS_CS_Query {

	/**
	 * Get the default allowed params.
	 *
	 * @return array
	 */
	protected function igs_get_default_query_args() {

		return array(
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

	}

	/**
	 * Create a new query.
	 *
	 * @param array $params Criteria to query on in a format similar to WP_Query.
	 */
	public function __construct( $params = array() ) {

    parent::__construct( $params );

    $this->igs_set_paged();

		$this->query = new WP_Query( $this->igs_get_query_args() );

	}

  /*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Functions for getting params data.
	*/

  protected function igs_set_paged( ) {

    if (
      $this->igs_get_param( 'igs_type') == '4' ||
      $this->igs_get_param('igs_per_page') > 0 &&
      ( $this->igs_get_param('igs_post_type') == 'post' && is_home() ) || is_post_type_archive( $this->igs_get_param('igs_post_type') )
    ) {
      $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
      $this->igs_set_query_arg( 'paged',  $paged);
    }

	}

	public function igs_page_posts() {

		$per_page = $this->igs_get_param('igs_per_page', 0 );

		if ( $per_page > 0 && $per_page < $this->igs_get_post_count() )
			return $per_page;

		return $this->igs_get_post_count();

	}

	public function igs_total_posts() {

		$results = $this->igs_get_param('igs_results', 0 );

		if ( $results > 0 && $results < $this->igs_get_found_posts() )
			return $results;

		return $this->igs_get_found_posts();

	}


  public function igs_has_more() {

    if ( ! $this->igs_get_query()->have_posts() || $this->igs_get_param( 'igs_type') == '4' )
      return;

    $results  = $this->igs_get_param('igs_results', 0 );
    $per_page = $this->igs_get_param('igs_per_page', 0 );

    if ( $this->igs_get_found_posts() > 0 && ( $results == 0 || $this->igs_get_found_posts() < $results ) )
      $results = $this->igs_get_found_posts();

    if ( $results > 0 ) {
      if ( $results < $per_page )
        $per_page = $results;

      return $per_page > 0 && $results > $per_page + $this->igs_get_param( 'igs_offset' );

    } else {
      return true;
    }

  }

}
