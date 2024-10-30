<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.msy.be/
 * @since      1.0.0
 *
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 * @author     DigiXoft <arslan@digixoft.com>
 */

if( ! class_exists('Msydrop_Shipping') ){

    class Msydrop_Shipping {

        /**
         * The loader that's responsible for maintaining and registering all hooks that power
         * the plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      Msydrop_Shipping_Loader    $loader    Maintains and registers all hooks for the plugin.
         */
        protected $loader;

        /**
         * The unique identifier of this plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      string    $plugin_name    The string used to uniquely identify this plugin.
         */
        protected $plugin_name;

        /**
         * The current version of the plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      string    $version    The current version of the plugin.
         */
        protected $version;

        /**
         * Define the core functionality of the plugin.
         *
         * Set the plugin name and the plugin version that can be used throughout the plugin.
         * Load the dependencies, define the locale, and set the hooks for the admin area and
         * the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function __construct() {
            if ( defined( 'MSYDROP_SHIPPING_VERSION' ) ) {
                $this->version = MSYDROP_SHIPPING_VERSION;
            } else {
                $this->version = '1.0.0';
            }
            $this->plugin_name = 'msydrop-shipping';

            $this->load_dependencies();
            $this->set_locale();
            $this->define_admin_hooks();
            $this->define_public_hooks();

        }

        /**
         * Load the required dependencies for this plugin.
         *
         * Include the following files that make up the plugin:
         *
         * - Msydrop_Shipping_Loader. Orchestrates the hooks of the plugin.
         * - Msydrop_Shipping_i18n. Defines internationalization functionality.
         * - Msydrop_Shipping_Admin. Defines all hooks for the admin area.
         * - Msydrop_Shipping_Public. Defines all hooks for the public side of the site.
         *
         * Create an instance of the loader which will be used to register the hooks
         * with WordPress.
         *
         * @since    1.0.0
         * @access   private
         */
        private function load_dependencies() {

            /**
             * The class responsible for orchestrating the actions and filters of the
             * core plugin.
             */
            require_once MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping_Loader.php';

            /**
             * The class responsible for defining internationalization functionality
             * of the plugin.
             */
            require_once MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping_i18n.php';

            /**
             * The class responsible for defining all actions that occur in the admin area.
             */
            require_once MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping_Category.php';
            require_once MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping_Product.php';
            require_once MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping_Order.php';
            require_once MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping_Admin.php';

            /**
             * The class responsible for defining all actions that occur in the public-facing
             * side of the site.
             */
            require_once MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping_Public.php';

            $this->loader = new Msydrop_Shipping_Loader();

        }

        /**
         * Define the locale for this plugin for internationalization.
         *
         * Uses the Msydrop_Shipping_i18n class in order to set the domain and to register the hook
         * with WordPress.
         *
         * @since    1.0.0
         * @access   private
         */
        private function set_locale() {

            $plugin_i18n = new Msydrop_Shipping_i18n();

            $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

        }

        /**
         * Register all of the hooks related to the admin area functionality
         * of the plugin.
         *
         * @since    1.0.0
         * @access   private
         */
        private function define_admin_hooks() {

            $plugin_admin = new Msydrop_Shipping_Admin( $this->get_plugin_name(), $this->get_version() );

            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

            $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
            $this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
            $this->loader->add_action( 'update_option_msy_main_settings_options', $plugin_admin, 'pre_update_option', 10, 3 );
            $this->loader->add_filter( 'cron_schedules', $plugin_admin, 'add_cron_intervals' );

            $this->loader->add_filter( 'plugin_action_links_'.plugin_basename(MSYDROP_SHIPPING_FILE), $plugin_admin, 'plugin_screen_links' );

            $category = new Msydrop_Shipping_Category();
            $this->loader->add_action( 'msy_drop_shipping_categories', $category, 'sync_from_api' );

            $product = new Msydrop_Shipping_Product();
            $this->loader->add_action( 'msy_drop_shipping_products', $product, 'import_from_api' );
            $this->loader->add_action( 'msy_drop_shipping_images', $product, 'import_images' );
            $this->loader->add_action( 'msy_drop_shipping_products_sync', $product, 'sync_from_api' );
            $this->loader->add_action( 'wp_ajax_msy_sync_api_products', $product, 'import_from_api' );
            $this->loader->add_action( 'wp_ajax_msy_add_to_imports', $product, 'add_to_import' );
            $this->loader->add_action( 'wp_ajax_msy_clear_all_imports', $product, 'clear_all_imports' );
            $this->loader->add_action( 'wp_ajax_msy_import_products', $product, 'import_products' );
            $this->loader->add_action( 'wp_ajax_remove_published_product', $product, 'remove_product' );
            //$product->sync_from_api();

            $order = new Msydrop_Shipping_Order();
            $this->loader->add_action( 'woocommerce_checkout_create_order_line_item', $order, 'wc_order_item_meta', 20 );
            $this->loader->add_action( 'woocommerce_after_order_object_save', $order, 'wc_order_save' );
            $this->loader->add_action( 'wp_ajax_msy_process_all_orders', $order, 'process_all_orders' );
            $this->loader->add_action( 'wp_ajax_msy_delete_pending_order', $order, 'delete_order' );
            $this->loader->add_filter( 'woocommerce_order_item_get_formatted_meta_data', $order, 'wc_order_item_get_meta_data', 10, 2 );

        }

        /**
         * Register all of the hooks related to the public-facing functionality
         * of the plugin.
         *
         * @since    1.0.0
         * @access   private
         */
        private function define_public_hooks() {

            $plugin_public = new Msydrop_Shipping_Public( $this->get_plugin_name(), $this->get_version() );

            $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
            $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        }

        /**
         * Run the loader to execute all of the hooks with WordPress.
         *
         * @since    1.0.0
         */
        public function run() {
            $this->loader->run();
        }

        /**
         * The name of the plugin used to uniquely identify it within the context of
         * WordPress and to define internationalization functionality.
         *
         * @since     1.0.0
         * @return    string    The name of the plugin.
         */
        public function get_plugin_name() {
            return $this->plugin_name;
        }

        /**
         * The reference to the class that orchestrates the hooks with the plugin.
         *
         * @since     1.0.0
         * @return    Msydrop_Shipping_Loader    Orchestrates the hooks of the plugin.
         */
        public function get_loader() {
            return $this->loader;
        }

        /**
         * Retrieve the version number of the plugin.
         *
         * @since     1.0.0
         * @return    string    The version number of the plugin.
         */
        public function get_version() {
            return $this->version;
        }

    }

}
