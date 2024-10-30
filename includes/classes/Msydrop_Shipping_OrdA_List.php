<?php

/**
 * The products functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 * @author     DigiXoft <arslan@digixoft.com>
 */

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if( ! class_exists('Msydrop_Shipping_OrdA_List') ){

    class Msydrop_Shipping_OrdA_List extends WP_List_Table
    {

        private $per_page = 20;
        private $total_items = 0;

        function __construct($args = array())
        {
            parent::__construct($args);
        }

        /**
         * Prepare the items for the table to process
         *
         * @return Void
         */
        public function prepare_items()
        {
            global $wpdb;
            $columns = $this->get_columns();
            $hidden = $this->get_hidden_columns();
            $sortable = $this->get_sortable_columns();
            $this->process_bulk_action();

            $data = $this->table_data();
            $this->items = $data;

            $totalItems = $this->total_items;

            $this->set_pagination_args( array(
                'total_items' => $totalItems,
                'per_page'    => $this->per_page
            ) );

            $this->_column_headers = array($columns, $hidden, $sortable);
        }

        public function column_cb($item)
        {
            parent::column_cb($item);
            echo '<input type="checkbox" name="order_ids[]" value="'.esc_attr($item->ID).'">';
        }

        /**
         * Override the parent columns method. Defines the columns to use in your listing table
         *
         * @return Array
         */
        public function get_columns()
        {
            $columns = array(
                //'cb'        => '<input id="cb-select-all-1" type="checkbox">',
                'order'     => __('Order'),
                'date'      => __('Date'),
                'details'   => __('Details'),
                'status'    => __('Status')
            );

            return $columns;
        }

        /**
         * Define which columns are hidden
         *
         * @return Array
         */
        public function get_hidden_columns()
        {
            return array();
        }

        /**
         * Define the sortable columns
         *
         * @return Array
         */
        public function get_sortable_columns()
        {
            return array();
        }

        /**
         * Get the table data
         *
         * @return Array
         */
        private function table_data()
        {
            $currentPage = $this->get_pagenum();
            $args = array(
                'post_type'     => 'shop_order',
                'post_status'   => 'any',
                'orderby'       => 'date',
                'order'         => 'DESC',
                'paged'         => $currentPage,
                'posts_per_page'=> $this->per_page,
                'meta_key'      => 'msyds_product_include',
                'meta_value'    => 'yes',
                'meta_query'    => array(
                    array(
                        'key'       => 'msyds_order_synced',
                        'value'     => array('yes', 'no'),
                        'compare'   => 'IN'
                    )
                )
            );
            $query = new WP_Query($args);
            if( $query->have_posts() ){
                $data = $query->posts;
                $this->total_items = $query->found_posts;
            } else {
                $data = array();
            }
            return $data;
        }

        /**
         * Define what data to show on each column of the table
         *
         * @param  Array $item        Data
         * @param  String $column_name - Current column name
         *
         * @return Mixed
         */
        public function column_default( $item, $column_name )
        {
            switch( $column_name ) {
                case 'order':
                    $fname = get_post_meta($item->ID, '_shipping_first_name', true);
                    $lname = get_post_meta($item->ID, '_shipping_last_name', true);
                    return '<b>#' . $item->ID . ' ' . $fname . ' ' . $lname . '</b>';
                case 'date':
                    return $item->post_date;
                case 'status':
                    $ordSynced = get_post_meta($item->ID, 'msyds_order_synced', true);
                    if( $ordSynced == 'yes' ){
                        return 'Synced';
                    }else{
                        return 'Pending';
                    }
                case 'details':
                    $order = wc_get_order($item->ID);
                    $products = $order->get_items();
                    $output = '';
                    if( $products ){
                        foreach ($products as $product) {
                            $msyds_product = $product->get_meta( 'msyds_product_id', true );
                            if( $msyds_product == 'yes' ){
                                $msyds_order_id = $product->get_meta( '_msyds_product_order_id', true );
                                $msyds_error = $product->get_meta( 'msyds_product_order_error', true );
                                $msyds_price = $product->get_total();
                                $output .= '<b>' . $product->get_name() . '</b> * ' . $product->get_quantity() . ' = ' . wc_price($msyds_price) . '<br/>';
                                if( $msyds_order_id ){
                                    $output .= '<b>'.__('Order ID').':</b> '.$msyds_order_id.'<br/>';
                                } elseif( $msyds_error ){
                                    $output .= '<b>'.__('Order Error').':</b> '.$msyds_error.'';
                                }
                            }
                        }
                    }
                    return $output;

                default:
                    return print_r( $item, true );
            }
        }

        function get_bulk_actions(){
            return array();
        }

        /**
         * Allows you to process the data by the variables set in the $_GET
         *
         * @return Mixed
         */
        public function process_bulk_action(){

        }

        public function extra_tablenav($which){

        }

        protected function get_table_classes() {
            $mode = get_user_setting( 'posts_list_mode', 'list' );

            $mode_class = esc_attr( 'table-view-' . $mode );

            return array( 'widefat', 'striped', $mode_class, $this->_args['plural'] );
        }

    }

}
