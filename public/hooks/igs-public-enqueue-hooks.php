<?php

/**
 * Public Enqueue Hooks.
 *
 * Central place for frontend CSS and JavaScript enqueuing.
 * Currently empty — add frontend assets here as the public module grows.
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Public/Hooks
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Public_Enqueue_Hooks extends IGS_CS_Loader {

  /**
   * @var IGS_CS_Public_Enqueue_Hooks|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Public_Enqueue_Hooks
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
    $this->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts', 10 );
  }

  /**
   * Enqueue frontend CSS and JS.
   *
   * @since 1.0.0
   * @return void
   */
  public function enqueue_scripts() {
    // Add frontend assets here when needed.
  }

}

IGS_CS_Public_Enqueue_Hooks::instance();
