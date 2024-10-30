<?php
$margins = get_option('msy_price_settings_options', []);
global $wpdb;
$table_name = $wpdb->prefix.'msyds_products';
$table_cats = $wpdb->prefix.'msyds_categories';
$product_id = absint($_GET['product_id']);
$query = $wpdb->prepare("SELECT p.*, c.en_title AS c_title FROM $table_name AS p INNER JOIN `".$table_cats."` AS c ON p.id_category=c.id_system WHERE p.id=".esc_sql($product_id));
$data = $wpdb->get_row($query, ARRAY_A);
?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php esc_html__('Import Product', 'msydrop-shipping'); ?></h1>
        <?php if( $data ){ ?>
            <?php
            $images = ($data['pics']) ? explode('||', $data['pics']) : [];
            $args = array(
                'taxonomy'   => "product_cat",
                'hide_empty' => false
            );
            $categories = get_terms($args);
            $id_product = absint($data['id_product']);
            $query = $wpdb->prepare("SELECT * FROM ".$wpdb->postmeta." WHERE `meta_key`='msyds_product_id' AND `meta_value`='".esc_sql($id_product)."'");
            $exists = $wpdb->get_row($query, ARRAY_A);
            if( $exists ){
                $post_id = absint($exists['post_id']);
                $product = new WC_Product_Simple($post_id);
                $margin = get_post_meta($post_id, 'msyds_margin_price', true);
                $margin = $margin > 0 ? $margin : 0;
                $m_perc = 0;
            } else {
                $product = array();
                $m_perc = isset($margins['shop']) ? $margins['shop'] : 0;
                $margin = ($m_perc>0) ? ($m_perc/100 * $data['price']) : $data['price_rec'] - $data['price'];
            } ?>
            <form id="publish-product-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <table class="widefat form-table">
                    <tbody>
                    <tr>
                        <td rowspan="4">
                            <?php echo $images ? '<img src="'.esc_attr($images[0]).'" alt="thumbnail" class="product-image">' : ''; ?>
                        </td>
                        <td colspan="4">
                            <label for="title"><?php _e('Product Title'); ?></label>
                            <br/>
                            <input type="text" name="title" id="title" class="form-field" value="<?php echo $product ? esc_attr($product->get_name()) : esc_attr($data['en_title']); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="base_price"><?php _e('Price'); ?></label>
                            <br/>
                            <input type="number" name="base_price" id="base_price" class="form-field" value="<?php echo round(esc_attr($data['price']), 2); ?>" disabled>
                        </td>
                        <td>
                            <label for="margin"><?php _e('Margin'); ?></label>
                            <br/>
                            <input type="number" step="0.01" name="margin" id="margin" class="form-field" value="<?php echo round(esc_attr($margin), 2); ?>" required>
                            <input type="hidden" name="shop_margin" id="shop_margin" value="<?php echo esc_attr($m_perc); ?>">
                        </td>
                        <td>
                            <label for="sale_price"><?php _e('Sale Price'); ?></label>
                            <br/>
                            <input type="number" step="0.01" name="sale_price" id="sale_price" class="form-field" value="<?php echo esc_attr(round($data['price'] + $margin, 2)); ?>" required readonly>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <label for="category_id"><?php _e('Category'); ?></label>
                            <br/>
                            <select name="category_id" id="category_id" class="form-field">
                                <option value=""><?php _e('Select Category'); ?></option>
                                <?php if( ! is_wp_error($categories) && $categories ){
                                    foreach ($categories as $category) {
                                        $prod_margin = isset($margins[$category->term_id]) ? $margins[$category->term_id] : '0';
                                        $selected = ($product && in_array($category->term_id, $product->get_category_ids())) ? 'selected' : '';
                                        echo '<option value="'.esc_attr($category->term_id).'" data-margin="'.esc_attr($prod_margin).'" '.$selected.'>'.esc_html($category->name).'</option>';
                                    }
                                } ?>
                            </select>
                        </td>
                        <td colspan="2">
                            <label for="cat_title"><?php _e('MSY Category'); ?></label>
                            <br/>
                            <input type="text" name="cat_title" id="cat_title" class="form-field" value="<?php echo esc_attr($data['c_title']); ?>" disabled>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <?php wp_nonce_field('msy_nonce', 'nonce', false) ?>
                            <input type="hidden" name="product_id" value="<?php echo esc_attr($data['id']); ?>">
                            <input type="hidden" name="action" value="save_publish_product">
                            <button type="submit" class="button button-primary alignright"><?php _e('Save & Publish'); ?></button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
        <?php } else {
            echo "Invalid product import request!";
        } ?>
    </div>
