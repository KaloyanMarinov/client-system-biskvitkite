<?php

/**
 * The Main Hooks functionality of the plugin.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS
 * @subpackage IGS/Admin/Hooks
 * @author     igamingsolutions.com <support@igamingsolutions.com>
 *
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Hooks extends IGS_CS_Loader {

	/**
	 * The single instance of the class.
	 *
	 * @var IGS_CS_Hooks
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Main IGS_CS_Hooks Instance.
	 *
	 * Ensures only one instance of IGS_CS_Hooks is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return IGS_CS_Hooks - Main instance.
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
	public function __construct( ) {

		parent::__construct();

    $this->hooks();
		$this->run();

	}

  /**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
   *
   * @return void
	 */
	private function hooks() {
    $this->add_action( 'plugins_loaded', $this, 'load_dependent_classes' );
    $this->add_action( 'the_post', $this, 'igs_the_post', 10, 2 );
	}

  public function load_dependent_classes() {

    if ( class_exists( 'WC_Subscription' ) ) {
      require_once IGS_CS_ABSPATH . '/includes/core/igs-subscription.php';
    }

  }

  /**
   * Function for `the_post` action-hook.
   *
   * @param WP_Post  $post Post object.
   * @param WP_Query $query.
   *
   * @return void
   */
  public function igs_the_post( $post, $query ) {

    switch ($post->post_type) {

      case 'shop_subscription':
        IGS_CS_Subscription::igs_setup_data($post);
        break;

    }

  }
}

IGS_CS_Hooks::instance();
