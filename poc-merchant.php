<?php

/**
 * Plugin Name: POC Merchant
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constant variables
define( 'POC_MERCHANT_PLUGIN_FILE', __FILE__ );
define( 'POC_MERCHANT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'POC_MERCHANT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/vendor/autoload.php';

use App\POC_Merchant;

// Register activation hook
register_activation_hook( POC_MERCHANT_PLUGIN_FILE, array( 'POC_Merchant', 'activate' ) );

// Register deactivation hook
register_deactivation_hook( POC_MERCHANT_PLUGIN_FILE, array( 'POC_Merchant', 'deactivate' ) );

// Run plugin
POC_Merchant::instance();
