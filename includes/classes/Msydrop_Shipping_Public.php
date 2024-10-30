<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.msy.be/
 * @since      1.0.0
 *
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 * @author     DigiXoft <arslan@digixoft.com>
 */

if( ! class_exists('Msydrop_Shipping_Public') ){

    class Msydrop_Shipping_Public {

        /**
         * The ID of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $plugin_name    The ID of this plugin.
         */
        private $plugin_name;

        /**
         * The version of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $version    The current version of this plugin.
         */
        private $version;

        /**
         * Initialize the class and set its properties.
         *
         * @since    1.0.0
         * @param      string    $plugin_name       The name of the plugin.
         * @param      string    $version    The version of this plugin.
         */
        public function __construct( $plugin_name, $version ) {

            $this->plugin_name = $plugin_name;
            $this->version = $version;

        }

        /**
         * Register the stylesheets for the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function enqueue_styles() {

            wp_enqueue_style( $this->plugin_name, MSYDROP_SHIPPING_URL . 'public/css/msydrop-shipping-public.css', array(), $this->version, 'all' );

        }

        /**
         * Register the JavaScript for the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function enqueue_scripts() {

            wp_enqueue_script( $this->plugin_name, MSYDROP_SHIPPING_URL . 'public/js/msydrop-shipping-public.js', array( 'jquery' ), $this->version, false );

        }

    }

}

