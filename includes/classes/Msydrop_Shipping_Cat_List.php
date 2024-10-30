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

if( ! class_exists('Msydrop_Shipping_Cat_List') ){

    class Msydrop_Shipping_Cat_List extends WP_List_Table
    {

        private $table_name;
        private $per_page = 20;

        function __construct($args = array())
        {
            parent::__construct($args);
            global $wpdb;
            $this->table_name = $wpdb->prefix."msyds_categories";
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

            $totalItems = $wpdb->get_var("SELECT COUNT(id) FROM ".$this->table_name." WHERE 1=1");
            $totalItems = $totalItems > 0 ? $totalItems : 0;

            $this->set_pagination_args( array(
                'total_items' => $totalItems,
                'per_page'    => $this->per_page
            ) );

            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->items = $data;
        }

        public function column_cb($item)
        {
            parent::column_cb($item);
            echo '<input type="checkbox" name="cat_ids[]" value="'.esc_attr($item['id']).'">';
        }

        /**
         * Override the parent columns method. Defines the columns to use in your listing table
         *
         * @return Array
         */
        public function get_columns()
        {
            $columns = array(
                'cb'        => '<input id="cb-select-all-1" type="checkbox">',
                'en_title'  => __('Title'),
                'nl_title'  => __('Titel'),
                'es_title'  => __('Título'),
                'fr_title'  => __('Titre'),
                'de_title'  => __('Titel'),
                'it_title'  => __('Titolo')
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
            global $wpdb;
            $currentPage = $this->get_pagenum();
            $offset = $this->per_page * ($currentPage - 1);
            $data = $wpdb->get_results("SELECT * FROM ".$this->table_name." WHERE 1=1 ORDER BY id ASC LIMIT ".$this->per_page." OFFSET $offset", ARRAY_A);
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
                case 'nl_title':
                case 'es_title':
                case 'fr_title':
                case 'de_title':
                case 'it_title':
                case 'en_title':
                    return ($item['id_parent']>0) ? '-- '.$item[$column_name] : '<b>'.$item[$column_name].'</b>';

                default:
                    return print_r( $item, true );
            }
        }

        function get_bulk_actions(){
            return array();
            /*array(
                'delete'       => 'Delete',
                'Change Status'     => array(
                    'active'       => 'Active',
                    'draft'        => 'Draft',
                )
            );*/
        }

        /**
         * Allows you to process the data by the variables set in the $_GET
         *
         * @return Mixed
         */
        public function process_bulk_action(){
            global $wpdb;
            if ( in_array($this->current_action(), array('delete', 'active', 'draft'))) {
                //implement in next version
            }
        }

        public function extra_tablenav($which){

        }

    }

}
