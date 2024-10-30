<?php

/**
 *
 * @link              https://www.msy.be/
 * @since             1.0.0
 * @package           Msydrop_Shipping
 *
 * @wordpress-plugin
 * Plugin Name:       MSY Drop Shipping
 * Plugin URI:        https://www.msy.be/
 * Description:       MSY INVEST іѕ an innovative, online, and cеrtіfіеd B2B (business-to-business) whоlеѕаlе manufacturer of goods who tirelessly strive to create tools and resources that aid our customers in their drive for satisfaction by offering high quаlіtу household and trendy items at whоlеѕаlе and bulk рrісеѕ.
 * Version:           1.1.1
 * Requires at least: 5.0
 * Tested up to:      6.2
 * Author:            DigiXoft
 * Author URI:        https://profiles.wordpress.org/arslansaleem363/
 * License:           GPL-3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       msydrop-shipping
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MSYDROP_SHIPPING_VERSION', '1.1.1' );
define( 'MSYDROP_SHIPPING_SLUG', 'msydrop-shipping' );
define( 'MSYDROP_SHIPPING_USER', 'SHOPIFY' );
define( 'MSYDROP_SHIPPING_AUTH', 'RwpkP9O1MvzI6xVULodkZ2b2yxkdU1ErGWeRlziBucIgJTWT1g4QXURw3JeH' );
define( 'MSYDROP_SHIPPING_FILE', __FILE__ );
define( 'MSYDROP_SHIPPING_URL', plugin_dir_url( __FILE__ ) );
define( 'MSYDROP_SHIPPING_PATH', plugin_dir_path( __FILE__ ) );
define( 'MSYDROP_SHIPPING_CPATH', plugin_dir_path( __FILE__ ) . 'includes/classes/' );


if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    function msy_wc_require_admin_notice() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'WooCommerce plugin has not activated, you need to install and activate WooCommerce in order to use MSY!', 'msydrop-shipping' ); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'msy_wc_require_admin_notice' );
    return;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/Msydrop_Shipping_Activator.php
 */
function activate_msydrop_shipping() {
	require_once MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping_Activator.php';
	Msydrop_Shipping_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/Msydrop_Shipping_Deactivator.php
 */
function deactivate_msydrop_shipping() {
	require_once MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping_Deactivator.php';
	Msydrop_Shipping_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_msydrop_shipping' );
register_deactivation_hook( __FILE__, 'deactivate_msydrop_shipping' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_msydrop_shipping() {

	$plugin = new Msydrop_Shipping();
	$plugin->run();

}
run_msydrop_shipping();
