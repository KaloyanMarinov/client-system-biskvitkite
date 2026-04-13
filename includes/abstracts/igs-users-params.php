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

abstract class IGS_CS_Users_Params extends IGS_CS_Params {

  /**
	 * Get the default allowed params.
	 *
	 * @return array
	 */
	protected function igs_get_default_params() {

		return array(
      'igs_fields'      => 'ids',
      'igs_number'      => 24,
      'igs_orderby'     => 'registered',
      'igs_order'       => 'DESC',
      'igs_role'        => 'customer',
      'igs_count_total' => false
		);

	}

  /**
	 * Create a new query.
	 *
	 * @param array $args Criteria to query on in a format similar to WP_Query.
	 */
	public function __construct( $params = array() ) {

    $params = apply_filters( 'igs_users_params', wp_parse_args( $params, $this->igs_get_default_params() ) );

		parent::__construct( $params );

	}

  /*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Functions for getting params data.
	*/

  /**
	 * Set a Role
	 *
	 * @param string $param Params variable to set.
	 */
	protected function igs_role( ) {

    return [ 'role' => $this->igs_get_param('igs_role') ];

	}

  protected function igs_number() {

		return [ 'number' => $this->igs_get_param( 'igs_number' ) ];

	}

  protected function igs_include() {

    return array( 'include' => $this->igs_get_param('igs_include') );

  }

  protected function igs_exclude() {

    return array( 'exclude' => $this->igs_get_param('igs_exclude') );

  }

  protected function igs_customer() {

    if ( ! $user_id = $this->igs_get_param( 'igs_customer' ) )
      return [];

    return array( 'include' => array( $user_id ) );

  }

  protected function igs_count_total() {

    return array( 'count_total' => $this->igs_get_param( 'igs_count_total' ) );

  }



  /*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting params data.
  */


}
