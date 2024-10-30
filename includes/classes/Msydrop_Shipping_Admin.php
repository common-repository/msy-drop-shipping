<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.msy.be/
 * @since      1.0.0
 *
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 * @author     DigiXoft <arslan@digixoft.com>
 */

if( ! class_exists('Msydrop_Shipping_Admin') ){

    class Msydrop_Shipping_Admin {

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
         * @param      string    $plugin_name       The name of this plugin.
         * @param      string    $version    The version of this plugin.
         */
        public function __construct( $plugin_name, $version ) {

            $this->plugin_name = $plugin_name;
            $this->version = $version;

        }

        /**
         * Register the stylesheets for the admin area.
         *
         * @since    1.0.0
         */
        public function enqueue_styles() {

            wp_enqueue_style( $this->plugin_name, MSYDROP_SHIPPING_URL . 'admin/css/msydrop-shipping-admin.css', array(), $this->version, 'all' );

        }

        /**
         * Register the JavaScript for the admin area.
         *
         * @since    1.0.0
         */
        public function enqueue_scripts() {

            wp_enqueue_script( $this->plugin_name, MSYDROP_SHIPPING_URL . 'admin/js/msydrop-shipping-admin.js', array( 'jquery' ), $this->version, false );
            wp_localize_script( $this->plugin_name, 'msy_settings',
                array(
                    'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                    'nonce'     => wp_create_nonce('msy_nonce')
                )
            );

        }

        function register_settings(){
            register_setting('msy-main-settings', 'msy_main_settings_options');
            register_setting('msy-price-settings', 'msy_price_settings_options');
        }

        function admin_menu() {
            add_menu_page(
                'MSY Settings',
                'MSY',
                'manage_options',
                'msy-main-settings',
                null,
                'dashicons-store',
                55.6
            );
            add_submenu_page(
                'msy-main-settings',
                'MSY Settings',
                'Main Settings',
                'manage_options',
                'msy-main-settings',
                array($this, 'main_settings'),
            );
            add_submenu_page(
                'msy-main-settings',
                'Price Settings',
                'Price Settings',
                'manage_options',
                'msy-price-settings',
                array($this, 'price_settings')
            );
            add_submenu_page(
                'msy-main-settings',
                'MSY Products',
                'Products',
                'manage_options',
                'msy-base-products',
                array($this, 'base_products')
            );
            add_submenu_page(
                'msy-main-settings',
                'Imported Products',
                'Imports',
                'manage_options',
                'msy-import-products',
                array($this, 'import_products')
            );
            add_submenu_page(
                'msy-main-settings',
                'MSY Pending Orders',
                'Pending Orders',
                'manage_options',
                'msy-pending-orders',
                array($this, 'base_orders')
            );
            add_submenu_page(
                'msy-main-settings',
                'MSY ALL Orders',
                'All Orders',
                'manage_options',
                'msy-base-orders',
                array($this, 'baseA_orders')
            );
            add_submenu_page(
                'msy-main-settings',
                'MSY Categories',
                'Categories',
                'manage_options',
                'msy-base-categories',
                array($this, 'base_categories')
            );
            add_submenu_page(
                'msy-main-settings',
                'General Information',
                'General Info',
                'manage_options',
                'msy-general-info',
                array($this, 'general_info')
            );
            add_submenu_page(
                'msy-main-settings',
                'Shipping Information',
                'Shipping Info',
                'manage_options',
                'msy-shipping-info',
                array($this, 'shipping_info')
            );
        }

        function main_settings(){
            include_once MSYDROP_SHIPPING_PATH . 'admin/partials/main-settings.php';
        }

        function price_settings(){
            include_once MSYDROP_SHIPPING_PATH . 'admin/partials/price-settings.php';
        }

        function base_products(){
            include_once MSYDROP_SHIPPING_PATH . 'admin/partials/base-products.php';
        }

        function base_orders(){
            include_once MSYDROP_SHIPPING_PATH . 'admin/partials/products-orders.php';
        }

        function baseA_orders(){
            include_once MSYDROP_SHIPPING_PATH . 'admin/partials/products-all-orders.php';
        }

        function base_categories(){
            include_once MSYDROP_SHIPPING_PATH . 'admin/partials/base-categories.php';
        }

        function import_products(){
            include_once MSYDROP_SHIPPING_PATH . 'admin/partials/import-products.php';
        }

        function general_info(){
            include_once MSYDROP_SHIPPING_PATH . 'admin/partials/general-info.php';
        }

        function shipping_info(){
            include_once MSYDROP_SHIPPING_PATH . 'admin/partials/shipping-info.php';
        }

        function pre_update_option($old_value, $value, $option){
            if( $option == 'msy_main_settings_options' ){
                $post_args = array(
                    'gender'        => isset($value['gender']) ? $value['gender'] : '',
                    'first_name'    => isset($value['first_name']) ? $value['first_name'] : '',
                    'last_name'     => isset($value['last_name']) ? $value['last_name'] : '',
                    'email'         => isset($value['email']) ? $value['email'] : '',
                    'phone'         => isset($value['phone']) ? $value['phone'] : '',
                    'company'       => isset($value['company']) ? $value['company'] : '',
                    'vat_number'    => isset($value['vat_number']) ? $value['vat_number'] : '',
                    'street'        => isset($value['street']) ? $value['street'] : '',
                    'postcode'      => isset($value['postcode']) ? $value['postcode'] : '',
                    'city'          => isset($value['city']) ? $value['city'] : '',
                    'country_id'    => isset($value['country_id']) ? $value['country_id'] : ''
                );
                $headers = array(
                    'userid'        => MSYDROP_SHIPPING_USER,
                    'authorization' => MSYDROP_SHIPPING_AUTH
                );
                $response = wp_remote_post(
                    'https://msyds.madtec.be/api/register',
                    array(
                        'body'      => json_encode($post_args),
                        'headers'   => $headers
                    )
                );
                if( ! is_wp_error($response) ){
                    $data = wp_remote_retrieve_body($response);
                    $data = ($data) ? json_decode($data, true) : array();
                    if( isset($data['result'], $data['userid'], $data['authorization'], $data['webpassword']) && $data['result'] == 'ok'){
                        $data['wp_id'] = get_current_user_id();
                        $data['timestamp'] = time();
                        update_option('msy_user_registration_data', $data, 'no');
                        delete_option('msyds_notice_message');
                    } elseif( isset($data['result'], $data['error'], $data['user']) && $data['result'] == 'no' &&  $data['error'] == 'User exist') {
                        $response = wp_remote_post(
                            'https://msyds.madtec.be/api/register/update',
                            array(
                                'body'      => json_encode($post_args),
                                'headers'   => $headers
                            )
                        );
                        $new_data = wp_remote_retrieve_body($response);
                        $new_data = ($new_data) ? json_decode($new_data, true) : array();
                        if( isset($new_data['result']) && $new_data['result'] == 'ok'  || isset($new_data['result']) && $new_data['result'] == 'no' && isset($new_data['error']) && $new_data['error'] == 'User exist'){
                            $reg_data = get_option('msy_user_registration_data', []);
                            if( isset($data['user']['webpassword']) ){
                                $password = $data['user']['webpassword'];
                            } else {
                                $password = isset($reg_data['webpassword']) ? $reg_data['webpassword'] : '';
                            }
                            $upd_data = array(
                                'userid'        => $data['user']['userid'],
                                'authorization' => $data['user']['authorization'],
                                'webpassword'   => $password,
                                'wp_id'         => get_current_user_id(),
                                'timestamp'     => time()
                            );
                            update_option('msy_user_registration_data', $upd_data, 'no');
                            delete_option('msyds_notice_message');
                        }
                    } elseif( isset($data['result'], $data['error']) && $data['result'] == 'no' &&  $data['error'] != 'User exist') {
                        update_option('msy_user_registration_data', array(), 'no');
                        $notice = array('type'=>'error', 'msg'=>$data['error'], 'data'=>$data);
                        update_option('msyds_notice_message', $notice, 'no');
                    } else {
                        $notice = array('type'=>'error', 'msg'=>__('Something went wrong while connecting.'), 'data'=>$data);
                        update_option('msyds_notice_message', $notice, 'no');
                    }
                }
            }
        }

        static function display_notice($option_key='msyds_notice_message'){
            $notice = get_option($option_key, array());
            if( $notice && isset($notice['type'], $notice['msg']) ){
                if( $notice['type'] == 'success' ){ ?>
                    <div id="setting-error-settings_updated" class="notice notice-success is-dismissible">
                        <p><strong><?php echo __($notice['msg']); ?></strong></p>
                    </div>
                <?php }
                if( $notice['type'] == 'error' ){ ?>
                    <div id="setting-error-settings_updated" class="notice notice-error is-dismissible">
                        <p><strong><?php echo __($notice['msg']); ?></strong></p>
                    </div>
                <?php }
                delete_option($option_key);
            }
        }

        function add_cron_intervals($schedules) {
            $schedules['fifteenm'] = array(
                'interval'  => 15 * MINUTE_IN_SECONDS,
                'display'   => __('Fifteen Minutes')
            );
            $schedules['sixhours'] = array(
                'interval'  => 6 * HOUR_IN_SECONDS,
                'display'   => __('Six Hours')
            );
            return $schedules;
        }

        function plugin_screen_links( $links ) {
            $plugin_links = array(
                '<a href="' . admin_url( 'admin.php?page=msy-main-settings' ) . '">' . __( 'Settings' ) . '</a>'
            );
            $links = array_merge( $plugin_links, $links );
            return $links;
        }

    }

}

