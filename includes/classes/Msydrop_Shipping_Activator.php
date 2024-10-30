<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.msy.be/
 * @since      1.0.0
 *
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 * @author     DigiXoft <arslan@digixoft.com>
 */

if( ! class_exists('Msydrop_Shipping_Activator') ){

    class Msydrop_Shipping_Activator {

        /**
         * Short Description. (use period)
         *
         * Long Description.
         *
         * @since    1.0.0
         */
        public static function activate() {
            global $wpdb;
            $table_name = $wpdb->prefix.'msyds_categories';
            $sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
                `id` int NOT NULL AUTO_INCREMENT,
                `id_system` int,
                `id_parent` int NULL,
                `en_title` varchar(30),
                `nl_title` varchar(30),
                `es_title` varchar(30),
                `fr_title` varchar(30),
                `de_title` varchar(30),
                `it_title` varchar(30),
                `meta_data` text DEFAULT NULL,
                `created_at` datetime,
                PRIMARY KEY (id)
            );";
            $wpdb->query($sql);

            $table_name = $wpdb->prefix.'msyds_products';
            $sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
                `id` int NOT NULL AUTO_INCREMENT,
                `id_product` int,
                `id_category` int NULL,
                `id_prodwc` int NULL,
                `vendor` varchar(50),
                `en_title` varchar(200),
                `nl_title` varchar(200),
                `es_title` varchar(200),
                `fr_title` varchar(200),
                `de_title` varchar(200),
                `it_title` varchar(200),
                `price` DECIMAL(10, 4),
                `price_rec` DECIMAL(10, 4),
                `ean` varchar(100) NULL,
                `sku` varchar(100) NULL,
                `quantity` int NULL,
                `weight` varchar(20) NULL,
                `volume` varchar(20) NULL,
                `length` varchar(20) NULL,
                `height` varchar(20) NULL,
                `width` varchar(20) NULL,
                `pics` text DEFAULT NULL,
                `meta_data` text DEFAULT NULL,
                `status` varchar(20) NOT NULL DEFAULT 'active',
                `created_at` datetime,
                PRIMARY KEY (id)
            );";
            $wpdb->query($sql);

            if ( ! wp_next_scheduled ( 'msy_drop_shipping_categories' ) ) {
                wp_schedule_event( time(), 'twicedaily', 'msy_drop_shipping_categories' );
            }
            if ( ! wp_next_scheduled ( 'msy_drop_shipping_products' ) ) {
                wp_schedule_event( time(), 'sixhours', 'msy_drop_shipping_products' );
            }
            if ( ! wp_next_scheduled ( 'msy_drop_shipping_images' ) ) {
                wp_schedule_event( time(), 'fifteenm', 'msy_drop_shipping_images' );
            }
            if ( ! wp_next_scheduled ( 'msy_drop_shipping_products_sync' ) ) {
                wp_schedule_event( time(), 'fifteenm', 'msy_drop_shipping_products_sync' );
            }
            $creds = get_option('msy_user_registration_data', []);
            if( ! isset($creds['userid'], $creds['authorization']) || !($creds['userid'] > 0) || !$creds['authorization'] ){
                //wp_redirect( admin_url( 'admin.php?page=msy-main-settings' ) );
                //exit();
            }

        }

    }

}
