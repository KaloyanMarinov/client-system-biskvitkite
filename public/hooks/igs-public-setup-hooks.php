<?php

/**
 * Public Setup Hooks.
 *
 * Global frontend initialisation:
 * - Sets up the current-user context (IGS_CS_User global) on init so
 *   that pricing and visibility hooks can read it later in the same request.
 *
 * Add further bootstrap hooks here as the frontend grows.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Public/Hooks
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Public_Setup_Hooks extends IGS_CS_Loader {

  /**
   * @var IGS_CS_Public_Setup_Hooks|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Public_Setup_Hooks
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct() {
    parent::__construct();
    $this->hooks();
    $this->run();
  }

  /**
   * @since 1.0.0
   * @return void
   */
  private function hooks() {
    $this->add_action( 'init', 'IGS_CS_User', 'igs_setup_user_data', 10 );
  }

}

IGS_CS_Public_Setup_Hooks::instance();
