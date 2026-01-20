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
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'IGS_CS_ABSPATH' ) ) {
	define( 'IGS_CS_ABSPATH', plugin_dir_path( __FILE__ ) );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activator.php
 */
function igs_wi_activate_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
	IGS_CS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deactivator.php
 */
function igs_wi_deactivate_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
	IGS_CS_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'igs_wi_activate_plugin' );
register_deactivation_hook( __FILE__, 'igs_wi_deactivate_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
if ( ! class_exists( 'IGS_Client_System', false ) ) {
  require_once IGS_CS_ABSPATH . '/includes/class-client-system.php';
}

/**
 * Function for delaying initialization of the extension until after WooCommerce is loaded.
 * @return IGS_Client_System
 */
function IGS_CS() {
  return IGS_Client_System::instance();
}