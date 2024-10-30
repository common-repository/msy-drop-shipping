<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.msy.be/
 * @since      1.0.0
 *
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 * @author     DigiXoft <arslan@digixoft.com>
 */

if( ! class_exists('Msydrop_Shipping_Deactivator') ){

    class Msydrop_Shipping_Deactivator {

        /**
         * Short Description. (use period)
         *
         * Long Description.
         *
         * @since    1.0.0
         */
        public static function deactivate() {
            wp_clear_scheduled_hook( 'msy_drop_shipping_categories' );
            wp_clear_scheduled_hook( 'msy_drop_shipping_products' );
            wp_clear_scheduled_hook( 'msy_drop_shipping_images' );
            wp_clear_scheduled_hook( 'msy_drop_shipping_products_sync' );
        }

    }

}

