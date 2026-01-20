<?php
/**
 * Plugin Name: Client System for Biskvitkite
 * Plugin URI: https://igamingsolutions.net/
 * Description: Biskvitkite.bg client management extension
 * Version: 1.0.0
 * Author: iGaming Solutions LTD
 * Author URI: https://igamingsolutions.net/
 * Developer: iGaming Solutions LTD
 * Developer URI: https://igamingsolutions.net/
 * Text Domain: igs-client-system
 * Domain Path: /languages
 * Requires Plugins: woocommerce-subscriptions, woocommerce
 *
 * License: Proprietary
 * License URI: https://igamingsolutions.com/terms
 * Copyright (c) 2026 iGaming Solutions LTD. All rights reserved.
 * This software and its documentation are the property of iGaming Solutions LTD.
 * Copying, distribution, or modification is strictly prohibited
 * without express written consent.
 *
 * @package           IGS_Client_System
 * @since             1.0.0
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * Security check: Abort if this file is called directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Define Constants.
 */
if ( ! defined( 'IGS_CS_PLUGIN_FILE' ) ) {
	define( 'IGS_CS_PLUGIN_FILE', __FILE__ );
}

require_once dirname( IGS_CS_PLUGIN_FILE ) . '/includes/core/igs-dependencies.php';
$dependency_manager = new IGS_CS_Dependency_Manager();

if ( ! $dependency_manager->has_valid_dependencies() ) {
  add_action( 'admin_notices', [ $dependency_manager, 'display_dependency_admin_notice' ] );
  return;
}

/**
 * The code that runs during plugin activation.
 *
 * @since    1.0.0
 * @return   void
 */
function activate_igs_client_system() {
  require_once dirname( IGS_CS_PLUGIN_FILE ) . '/includes/igs-activator.php';
  IGS_CS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * @since    1.0.0
 * @return   void
 */
function deactivate_igs_client_system() {
  require_once dirname( IGS_CS_PLUGIN_FILE ) . '/includes/igs-activator.php';
  IGS_CS_Deactivator::deactivate();
}

/**
 * Register Hooks.
 */
register_activation_hook( __FILE__, 'activate_igs_client_system' );
register_deactivation_hook( __FILE__, 'deactivate_igs_client_system' );

/**
 * The core plugin class definition.
 */
require_once dirname( IGS_CS_PLUGIN_FILE ) . '/includes/igs-client-system.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 * @return   void
 */
function run_client_system() {
  IGS_Client_System::instance();
}

/**
 * Global accessor function for the IGS Client System.
 *
 * @since  1.0.0
 * @return IGS_Client_System The single instance of the plugin.
 */
function IGS_CS() {
  return IGS_Client_System::instance();
}

/**
 * Execute the plugin.
 */
run_client_system();