<?php
$margins = get_option('msy_price_settings_options', []);
$creds = get_option('msy_user_registration_data', []);
$imports = get_option('msy_imported_products_ids', []);
$imports = ($imports && is_array($imports)) ? $imports : array();
$imports = array_filter($imports, 'ctype_digit');
$options = get_option('msy_main_settings_options', []);
$product = new Msydrop_Shipping_Product();
$languages = $product->get_langs();
$lang_code = (isset($options['language']) && isset($languages[$options['language']])) ? $options['language'] : 'en';
global $wpdb;
$table_name = $wpdb->prefix.'msyds_products';
$table_cats = $wpdb->prefix.'msyds_categories';
if( $imports ){
    $query = $wpdb->prepare("SELECT p.*, c.en_title AS c_title FROM $table_name AS p LEFT JOIN $table_cats AS c ON p.id_category=c.id_system WHERE (p.id_prodwc IS NULL OR p.id_prodwc=0) AND p.id IN (".implode(',', $imports).")");
    $rows = $wpdb->get_results($query, ARRAY_A);
} else {
    $rows = array();
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Import Products'); ?></h1>
    <p class="text-red"><?php _e('Please import products by patience and wait for success message. It might take some time due to thumbnails upload. If it takes too much long then you can reload page and import again.'); ?></p>
    <?php
    if( isset($creds['userid'], $creds['authorization']) && $creds['userid'] > 0 && $creds['authorization'] ){
        if( $rows ){ ?>
                <a class="page-title-action" id="clear-all-imports"><?php _e('Clear all Imports'); ?></a>
            <form id="publish-product-form" method="post" action="<?php echo esc_attr(admin_url('admin-post.php')); ?>">
                <button type="button" id="save-products" class="button button-primary alignright"><?php _e('Bulk Import & Save to Store'); ?></button>
                <br/>
                <br/>
                <?php
                $args = array(
                    'taxonomy'   => "product_cat",
                    'hide_empty' => false
                );
                $categories = get_terms($args);
                $headers = array(
                    'userid'        => $creds['userid'],
                    'authorization' => $creds['authorization']
                );
                foreach ($rows as $row) {
                    $p_id = $row['id'];
                    $id_product = $row['id_product'];
                    $images = ($row['pics']) ? explode('||', $row['pics']) : [];
                    $product = array();
                    $m_perc = (isset($margins['shop']) && $margins['shop'] > 0) ? $margins['shop'] : 0;
                    $margin = ($m_perc>0) ? ($m_perc/100 * $row['price']) : $row['price_rec'] - $row['price'];
                    $t_perc = (isset($margins['tax']) && $margins['tax'] > 0) ? $margins['tax'] : 0;
                    $tax_amount = ($t_perc>0) ? ($t_perc/100 * ($row['price'] + $margin)) : 0;
                    $title = isset($row[$lang_code.'_title']) ? $row[$lang_code.'_title'] : $row['en_title'];
                    $response = wp_remote_get(
                        'https://msyds.madtec.be/api/products/'.$id_product.'/desc',
                        array(
                            'body'      => [],
                            'timeout'   => 120,
                            'headers'   => $headers
                        )
                    );
                    $status_code = wp_remote_retrieve_response_code($response);
                    $description = '';
                    if( ! is_wp_error($response) && $status_code == 200 ){
                        $prod_descs = json_decode(wp_remote_retrieve_body($response), true);
                        if( isset($prod_descs[$lang_code]) ){
                            $description = $prod_descs[$lang_code];
                        } elseif( isset($prod_descs['en']) ){
                            $description = $prod_descs['en'];
                        }
                    } ?>
                    <div class="product-details">
                        <nav class="nav-tab-wrapper">
                            <a data-tabid="product-details" class="nav-tab nav-tab-active"><?php _e('Details'); ?></a>
                            <a data-tabid="product-desc" class="nav-tab"><?php _e('Description'); ?></a>
                            <a data-tabid="product-images" class="nav-tab"><?php _e('Images'); ?></a>
                        </nav>
                        <div class="tabs-content">
                            <div class="tab-content tab-content-active" id="product-details">
                                <table class="widefat form-table">
                                    <tbody>
                                    <tr>
                                        <td rowspan="3">
                                            <?php echo $images ? '<img src="'.esc_attr($images[0]).'" alt="thumbnail" class="product-image" loading="lazy">' : ''; ?>
                                            <input type="hidden" name="product_ids[]" class="product_ids" value="<?php echo esc_attr($p_id); ?>">
                                        </td>
                                        <td colspan="4">
                                            <label for="title<?php echo esc_attr($p_id); ?>"><?php _e('Product Title'); ?></label>
                                            <br/>
                                            <input type="text" name="title[<?php echo esc_attr($p_id); ?>]" id="title<?php echo esc_attr($p_id); ?>" data-product_id="<?php echo esc_attr($p_id); ?>" class="form-field prod_title" value="<?php echo esc_attr($title); ?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label for="base_price<?php echo esc_attr($p_id); ?>"><?php _e('Price'); ?></label>
                                            <br/>
                                            <input type="number" name="base_price[<?php echo esc_attr($p_id); ?>]" id="base_price<?php echo esc_attr($p_id); ?>" data-product_id="<?php echo esc_attr($p_id); ?>" class="form-field base_price" value="<?php echo esc_attr(round($row['price'], 2)); ?>" disabled>
                                        </td>
                                        <td>
                                            <label for="margin<?php echo esc_attr($p_id); ?>"><?php _e('Margin'); ?></label>
                                            <br/>
                                            <input type="number" step="0.01" name="margin[<?php echo esc_attr($p_id); ?>]" id="margin<?php echo esc_attr($p_id); ?>" data-product_id="<?php echo esc_attr($p_id); ?>" class="form-field margin" value="<?php echo esc_attr(round($margin, 2)); ?>" required>
                                            <input type="hidden" name="shop_margin[<?php echo esc_attr($p_id); ?>]" id="shop_margin<?php echo esc_attr($p_id); ?>" class="shop_margin" value="<?php echo esc_attr($m_perc); ?>">
                                        </td>
                                        <td>
                                            <label for="tax<?php echo esc_attr($p_id); ?>"><?php _e('Tax'); ?> %</label>
                                            <br/>
                                            <input type="number" step="0.01" name="tax[<?php echo esc_attr($p_id); ?>]" id="tax<?php echo esc_attr($p_id); ?>" data-product_id="<?php echo esc_attr($p_id); ?>" class="form-field tax" value="<?php echo esc_attr(round($t_perc, 2)); ?>" required>
                                            <input type="hidden" name="shop_tax[<?php echo esc_attr($p_id); ?>]" id="shop_tax<?php echo esc_attr($p_id); ?>" class="shop_tax" value="<?php echo esc_attr($t_perc); ?>">
                                        </td>
                                        <td>
                                            <label for="sale_price<?php echo esc_attr($p_id); ?>"><?php _e('Sale Price'); ?></label>
                                            <br/>
                                            <input type="number" step="0.01" name="sale_price[<?php echo esc_attr($p_id); ?>]" id="sale_price<?php echo esc_attr($p_id); ?>" data-product_id="<?php echo esc_attr($p_id); ?>" class="form-field sale_price" value="<?php echo esc_attr(round($row['price'] + $margin + $tax_amount, 2)); ?>" required readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label for="category_id<?php echo esc_attr($p_id); ?>"><?php _e('Category'); ?></label>
                                            <br/>
                                            <select name="category_id[<?php echo esc_attr($p_id); ?>]" id="category_id<?php echo esc_attr($p_id); ?>" data-product_id="<?php echo esc_attr($p_id); ?>" class="form-field category_id">
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
                                        <td>
                                            <label for="cat_title<?php echo esc_attr($p_id); ?>"><?php _e('MSY Category'); ?></label>
                                            <br/>
                                            <input type="text" name="cat_title[<?php echo esc_attr($p_id); ?>]" id="cat_title<?php echo esc_attr($p_id); ?>" class="form-field" value="<?php echo esc_attr($row['c_title']); ?>" disabled>
                                        </td>
                                        <td>
                                            <label for="prod_status<?php echo esc_attr($p_id); ?>"><?php _e('Set as Draft'); ?></label>
                                            <br/>
                                            <input type="checkbox" name="prod_status[<?php echo esc_attr($p_id); ?>]" id="prod_status<?php echo esc_attr($p_id); ?>" data-product_id="<?php echo esc_attr($p_id); ?>" class="form-field prod_status" value="draft">
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-content" id="product-desc">
                            <?php
                                $args = array(
                                    'teeny'          => true,
                                    'media_buttons'  => false,
                                    'textarea_rows'  => 30,
                                    'textarea_name'  => 'description[' . $p_id . ']',
                                );
                                $description = preg_replace('/\s+/', ' ', $description);
                                $description = trim($description);
                                wp_editor($description, 'description_' . $p_id, $args);
                                ?>
                            </div>
                            <div class="tab-content" id="product-images">
                                <?php if( $images ){
                                    $count = 1; ?>
                                    <table class="widefat form-table">
                                        <tbody>
                                        <tr>
                                            <?php foreach ($images as $image) {
                                                if ( stripos($image, 'youtube') === FALSE ) {
                                                    echo '<td><img src="'.esc_attr($image).'" alt="thumbnail" class="product-image" loading="lazy"></td>';
                                                    if( $count%4 == 0 ){
                                                        echo '</tr><tr>';
                                                    }
                                                    $count++;
                                                }
                                            } ?>
                                        </tr>
                                        </tbody>
                                    </table>
                                <?php } ?>
                            </div>
                        </div>
                        <button type="button" class="button button-primary alignright save-product"><?php _e('Save Product'); ?></button>
                    </div>
                <?php } ?>
            </form>
        <?php } else { ?>
            <div class="alert-danger"><?php _e("No products found to import!"); ?></div>
        <?php }
    } else { ?>
        <div class="alert-danger"><?php _e("Invalid API token to fetch description!"); ?></div>
    <?php } ?>
</div>
