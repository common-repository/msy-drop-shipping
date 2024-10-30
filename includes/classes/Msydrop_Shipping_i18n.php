<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.msy.be/
 * @since      1.0.0
 *
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 * @author     DigiXoft <arslan@digixoft.com>
 */

if( ! class_exists('Msydrop_Shipping_i18n') ){

    class Msydrop_Shipping_i18n {

        /**
         * Load the plugin text domain for translation.
         *
         * @since    1.0.0
         */
        public function load_plugin_textdomain() {

            load_plugin_textdomain(
                'msydrop-shipping',
                false,
                dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
            );

        }

    }

}

