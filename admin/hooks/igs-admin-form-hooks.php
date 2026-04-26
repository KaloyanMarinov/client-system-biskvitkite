<?php

/**
 * Admin Form Hooks.
 *
 * Handles all form submissions and profile field operations:
 * - cache busting on post/user save
 * - price-list field on WP user profile pages (including the JS that
 *   repositions the field to a sensible place in the form)
 * - subscription edit form save
 * - customer edit form save
 *
 * @link       https://igamingsolutions.net
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Admin/Hooks
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Admin_Form_Hooks extends IGS_CS_Loader {

  /**
   * @var IGS_CS_Admin_Form_Hooks|null
   */
  private static $_instance = null;

  /**
   * @since 1.0.0
   * @return IGS_CS_Admin_Form_Hooks
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

    // Cache busting
    $this->add_action( 'save_post',         $this, 'bust_post_cache',   10, 2 );
    $this->add_action( 'before_delete_post', $this, 'bust_post_cache',  10, 2 );
    $this->add_action( 'profile_update',     $this, 'bust_user_cache',  10, 1 );

    // Price-list field on WP user profile pages
    $this->add_action( 'user_new_form',              'IGS_CS_User', 'igs_add_price_list_fields', 10, 1 );
    $this->add_action( 'show_user_profile',          'IGS_CS_User', 'igs_add_price_list_fields', 10, 1 );
    $this->add_action( 'edit_user_profile',          'IGS_CS_User', 'igs_add_price_list_fields', 10, 1 );
    $this->add_action( 'personal_options_update',    'IGS_CS_User', 'igs_save_price_list_user_field' );
    $this->add_action( 'edit_user_profile_update',   'IGS_CS_User', 'igs_save_price_list_user_field' );
    $this->add_action( 'user_register',              'IGS_CS_User', 'igs_save_price_list_user_field' );

    // Inline script to move the price-list field next to display-name
    $this->add_action( 'admin_enqueue_scripts', $this, 'enqueue_user_profile_scripts', 10, 1 );

    // Form submissions
    $this->add_action( 'admin_post_igs_save_subscription_data', 'IGS_CS_Subscription', 'igs_handle_save_subscription', 10 );
    $this->add_action( 'admin_post_igs_create_subscription',    'IGS_CS_Subscription', 'igs_handle_create_subscription', 10 );
    $this->add_action( 'admin_post_igs_save_user_data',         'IGS_CS_User',         'igs_handle_save_user',         10 );

  }

  /**
   * Delete the post object cache entry when a post is saved or deleted.
   *
   * @since 1.0.0
   * @param int     $post_id
   * @param WP_Post $post
   * @return void
   */
  public function bust_post_cache( $post_id, $post ) {

    if ( ! defined( 'DOING_AUTOSAVE' ) && ! wp_is_post_revision( $post_id ) ) {
      wp_cache_delete( 'igs_post_' . $post_id . '_', 'igs_post' );
    }

  }

  /**
   * Delete the user object cache entry when a profile is updated.
   *
   * @since 1.0.0
   * @param int $user_id
   * @return void
   */
  public function bust_user_cache( $user_id ) {
    wp_cache_delete( 'igs_user_' . $user_id . '_', 'igs_user' );
  }

  /**
   * Enqueue the small inline script that moves the price-list <select>
   * to a better position in the WP user profile form.
   *
   * The script was previously inlined directly in IGS_CS_User::igs_add_price_list_fields()
   * alongside debug console.log calls.  It now lives here so models stay
   * free of presentation concerns.
   *
   * @since 1.0.0
   * @param string $hook
   * @return void
   */
  public function enqueue_user_profile_scripts( $hook ) {

    if ( ! in_array( $hook, array( 'profile.php', 'user-edit.php', 'user-new.php' ), true ) ) {
      return;
    }

    wp_add_inline_script( 'jquery', "
      jQuery(document).ready(function($) {
        $('.user-price-list-wrap').insertAfter($('.user-display-name-wrap'));
      });
    " );

  }

}

IGS_CS_Admin_Form_Hooks::instance();
