<?php

/**
 * The products functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 * @author     DigiXoft <mailto:arslan@digixoft.com>
 */

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if( ! class_exists('Msydrop_Shipping_Prod_List') ){

    class Msydrop_Shipping_Prod_List extends WP_List_Table
    {

        private $table_name;
        private $table_cats;
        private $per_page = 20;

        function __construct($args = array())
        {
            parent::__construct($args);
            global $wpdb;
            $this->table_name = $wpdb->prefix."msyds_products";
            $this->table_cats = $wpdb->prefix."msyds_categories";
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
            $where = "";
            if( isset($_GET['category']) && $_GET['category'] > 0 ){
                $category = absint($_GET['category']);
                $where .= " AND id_category='".esc_sql($category)."' ";
            }
            if( isset($_GET['catalog']) && $_GET['catalog'] ){
                if( $_GET['catalog'] == 'imported' ){
                    $where .= " AND id_prodwc > 0 ";
                } elseif( $_GET['catalog'] == 'nonimport' ){
                    $where .= " AND (id_prodwc='0' OR id_prodwc IS NULL) ";
                }
            }
            if( isset($_GET['s']) && $_GET['s'] ){
                $search = sanitize_text_field($_GET['s']);
                $where .= " AND en_title LIKE '%".esc_sql($search)."%' ";
            }
            $query = $wpdb->prepare("SELECT COUNT(id) FROM ".$this->table_name." WHERE `status`='active' AND `quantity`>0 $where");
            $totalItems = $wpdb->get_var($query);
            $totalItems = $totalItems > 0 ? $totalItems : 0;

            $this->set_pagination_args( array(
                'total_items' => $totalItems,
                'per_page'    => $this->per_page
            ) );

            $this->_column_headers = array($columns, $hidden, $sortable);
        }

        public function column_cb($item)
        {
            parent::column_cb($item);
            echo '<input type="checkbox" name="prod_ids[]" value="'.esc_attr($item['id_prodwc']).'">';
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
                'image'     => __('Image'),
                'en_title'  => __('Title'),
                'c_title'   => __('Category'),
                'price'     => __('Price'),
                'price_rec' => __('Rec. Price'),
                'ean'       => __('EAN'),
                'sku'       => __('SKU'),
                'quantity'  => __('Quantity'),
                'status'    => __('Status'),
                'action'    => __('Action'),
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
            $where = "";
            if( isset($_GET['category']) && $_GET['category'] > 0 ){
                $category = absint($_GET['category']);
                $where .= " AND p.id_category='".esc_sql($category)."' ";
            }
            if( isset($_GET['catalog']) && $_GET['catalog'] ){
                if( $_GET['catalog'] == 'imported' ){
                    $where .= " AND p.id_prodwc > 0 ";
                } elseif( $_GET['catalog'] == 'nonimport' ){
                    $where .= " AND (p.id_prodwc='0' OR p.id_prodwc IS NULL) ";
                }
            }
            if( isset($_GET['s']) && $_GET['s'] ){
                $search = sanitize_text_field($_GET['s']);
                $where .= " AND p.en_title LIKE '%".esc_sql($search)."%' ";
            }
            $sql = $wpdb->prepare("SELECT p.*, c.en_title AS c_title FROM `".$this->table_name."` AS p LEFT JOIN `".$this->table_cats."` AS c ON p.id_category=c.id_system WHERE p.status='active' AND p.`quantity`>0 $where ORDER BY id DESC LIMIT ".$this->per_page." OFFSET $offset");
            $data = $wpdb->get_results($sql, ARRAY_A);
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
                case 'en_title':
                case 'c_title':
                case 'ean':
                case 'sku':
                case 'quantity':
                    return $item[ $column_name ];
                case 'price':
                case 'price_rec':
                    return $item[$column_name] > 0 ? wc_price($item[$column_name]) : '';
                case 'status':
                    return ucfirst($item[$column_name]);
                case 'image':
                    if( $item['pics'] ){
                        $images = explode('||', $item['pics']);
                        return '<img src="'.esc_attr($images[0]).'" alt="thumbnail" class="thumbnail-image">';
                    }

                case 'action':
                    if( $item['id_prodwc'] > 0 ) {
                        return '<span class="dashicons dashicons-trash" data-product_id="'.esc_attr($item['id_prodwc']).'" title="'.__('Remove Product').'"></span>';
                    } else {
                        return '<span class="dashicons dashicons-database-add" data-product_id="'.esc_attr($item['id']).'" title="'.__('Import Product').'"></span>';
                    }

                default:
                    return print_r( $item, true );
            }
        }

        function get_bulk_actions(){
            return array(
                'remove_shop'   => 'Remove from Shop',
            );
        }

        /**
         * Allows you to process the data by the variables set in the $_GET
         *
         * @return Mixed
         */
        public function process_bulk_action(){
            global $wpdb;
            if ( in_array($this->current_action(), array('remove_shop'))) {
                if (isset($_GET['prod_ids'])) {
                    if (is_array($_GET['prod_ids'])) {
                        $table_name = $wpdb->prefix . 'msyds_products';
                        foreach ($_GET['prod_ids'] as $id) {
                            if ( $id > 0 ) {
                                $id = absint($id);
                                $product = new WC_Product_Simple($id);
                                $product->delete(true);
                                $wpdb->update(
                                    $table_name,
                                    array('id_prodwc' => ''),
                                    array('id_prodwc' => esc_sql($id))
                                );
                            }
                        }
                    }
                }
            }
        }

        public function extra_tablenav($which){
            if( $which == 'top' ){
                global $wpdb;
                $cats_table = $wpdb->prefix . 'msyds_categories';
                $categories = $wpdb->get_results("SELECT `id_system`, `id_parent`, `en_title` FROM ".$cats_table." ORDER BY `en_title` ASC");
                ?>
                <select style="max-width: 131px;" name="category" id="category" class="basic-lookup">
                    <option value=""><?php _e('Select Category'); ?></option>
                    <?php if( $categories ){
                        foreach ($categories as $category){
                            $selected = (isset($_GET['category']) && $_GET['category'] == $category->id_system) ? 'selected' : '';
                            if( $category->id_parent > 0 ){
                                echo "<option value='".esc_attr($category->id_system)."' $selected>-- {$category->en_title}</option>";
                            } else {
                                echo "<option value='".esc_attr($category->id_system)."' $selected>{$category->en_title}</option>";
                            }
                        }
                    } ?>
                </select>
                <select style="max-width: 115px;"  name="catalog" id="catalog" class="basic-lookup">
                    <option value=""><?php _e('Product Type'); ?></option>
                    <?php
                    $options = array(
                        'imported'  => __('Imported'),
                        'nonimport' => __('Non Imported'),
                    );
                    foreach ($options as $key => $option){
                        $selected = (isset($_GET['catalog']) && $_GET['catalog'] == $key) ? 'selected' : '';
                        echo "<option value='".esc_attr($key)."' $selected>$option</option>";
                    }
                    $disabled = (isset($_GET['category']) && $_GET['category'] > 0) ? '' : 'disabled'; ?>
                </select>
                <button type="submit" name="filter_cat" class="button button-primary"><?php _e('Filter'); ?></button>
                <button type="button" name="import_cat" id="import_cat" class="button button-primary" <?php echo $disabled; ?>><?php _e('Import Category'); ?></button>
                <a href="<?php echo admin_url('admin.php?page='.esc_attr($_GET['page'])); ?>" class="button"><?php _e('Reset'); ?></a>
                <button type="button" id="import_msy_products" class="button button-primary"><?php _e('Import Products'); ?></button>
                <?php 
            }
        }

    }

}
