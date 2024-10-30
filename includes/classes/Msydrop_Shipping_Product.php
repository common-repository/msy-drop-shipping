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

if( ! class_exists('Msydrop_Shipping_Product') ){

    class Msydrop_Shipping_Product
    {

        function import_from_api(){
            $creds = get_option('msy_user_registration_data', []);
            if( isset($creds['userid'], $creds['authorization']) && $creds['userid'] > 0 && $creds['authorization'] ){
                $headers = array(
                    'userid'        => $creds['userid'],
                    'authorization' => $creds['authorization']
                );
                $response = wp_remote_get(
                    'https://msyds.madtec.be/api/products',
                    array(
                        'body'      => [],
                        'timeout'   => 120,
                        'headers'   => $headers
                    )
                );
                $status_code = wp_remote_retrieve_response_code($response);
                if( ! is_wp_error($response) && $status_code == 200 ){
                    $products = json_decode(wp_remote_retrieve_body($response), true);
                    if( $products ){
                        global $wpdb;
                        $prods_table = $wpdb->prefix.'msyds_products';
                        $query = $wpdb->prepare("SELECT `id`,`id_product`,`id_prodwc`,`sku` FROM $prods_table");
                        $existed_prods = $wpdb->get_results($query);
                        $prods_array = array();
                        if( $existed_prods ){
                            foreach ($existed_prods as $existed_prod) {
                                $prods_array[$existed_prod->id_product.'_'.$existed_prod->sku] = $existed_prod;
                            }
                        }
                        $created = date('Y-m-d H:s:i');
                        $old_prods_sql = array();
                        $new_prods_sql = array();
                        $api_products = array();
                        foreach ($products as $product){
                            if( isset($product['variants']) ){
                                foreach ($product['variants'] as $variant){
                                    $prod_key = $product['id'].'_'.$variant['sku'];
                                    $api_products[$prod_key] = $product['id'];
                                    if( isset($prods_array[$prod_key]) ){
                                        $existed_prod = $prods_array[$product['id'].'_'.$variant['sku']];
                                        $sql = "UPDATE $prods_table SET `id_category`='".esc_sql($product['category'])."', `vendor`='".esc_sql($product['vendor'])."', `en_title`='".esc_sql($variant['en'])."', `nl_title`='".esc_sql($variant['nl'])."', `es_title`='".esc_sql($variant['es'])."', `fr_title`='".esc_sql($variant['fr'])."', `de_title`='".esc_sql($variant['de'])."', `it_title`='".esc_sql($variant['it'])."', `price`='".esc_sql($variant['price'])."', `price_rec`='".esc_sql($variant['price_recommended'])."', `ean`='".esc_sql($variant['ean'])."', `sku`='".esc_sql($variant['sku'])."', ";
                                        $sql .= "`quantity`='".esc_sql($variant['quantity'])."', `weight`='".esc_sql($variant['weight'])."', `volume`='".esc_sql($variant['volume'])."', `length`='".esc_sql($variant['length'])."', `height`='".esc_sql($variant['height'])."', `width`='".esc_sql($variant['width'])."', `pics`='".esc_sql(implode('||', $variant['pics']))."', `created_at`='".esc_sql($created)."' WHERE `id`='".esc_sql($existed_prod->id)."'";
                                        $old_prods_sql[] = $wpdb->prepare(str_replace(';', '', $sql));
                                        if( $existed_prod->id_prodwc > 0 ){
                                            if( $variant['quantity'] > 0 ){
                                                update_post_meta($existed_prod->id_prodwc, '_stock', esc_sql($variant['quantity']));
                                                update_post_meta($existed_prod->id_prodwc, '_stock_status', 'instock');
                                            } else {
                                                update_post_meta($existed_prod->id_prodwc, '_stock', '0');
                                                update_post_meta($existed_prod->id_prodwc, '_stock_status', 'outofstock');
                                            }
                                        }
                                    } else {
                                        $sql = "('".absint($product['id'])."', '".esc_sql($product['category'])."', '".esc_sql($product['vendor'])."', '".esc_sql($variant['en'])."', '".esc_sql($variant['nl'])."', '".esc_sql($variant['es'])."', '".esc_sql($variant['fr'])."', '".esc_sql($variant['de'])."', '".esc_sql($variant['it'])."', '".esc_sql($variant['price'])."', '".esc_sql($variant['price_recommended'])."', '".esc_sql($variant['ean'])."', '".esc_sql($variant['sku'])."', ";
                                        $sql .= "'".esc_sql($variant['quantity'])."', '".esc_sql($variant['weight'])."', '".esc_sql($variant['volume'])."', '".esc_sql($variant['length'])."', '".esc_sql($variant['height'])."', '".esc_sql($variant['width'])."', '".esc_sql(implode('||', $variant['pics']))."', '".esc_sql($created)."')";
                                        $new_prods_sql[] = $wpdb->prepare(str_replace(';', '', $sql));
                                    }
                                }
                            }
                        }
                        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                        if( $old_prods_sql ){
                            $sql = implode('; ', $old_prods_sql) . "; ";
                            $result = dbDelta($sql);
                        }
                        if( $new_prods_sql ){
                            $sql = "";
                            $per_sql = 100;
                            for( $i=0; $i<count($new_prods_sql); $i=$i+$per_sql ){
                                $values = array_slice($new_prods_sql, $i, $per_sql);
                                $sql .= "INSERT INTO $prods_table (`id_product`, `id_category`, `vendor`, `en_title`, `nl_title`, `es_title`, `fr_title`, `de_title`, `it_title`, `price`, `price_rec`, `ean`, `sku`, `quantity`, `weight`, `volume`, `length`, `height`, `width`, `pics`, `created_at`) VALUES " . implode(', ', $values) . "; ";
                            }
                            $result = dbDelta($sql);
                        }
                        //update inventory for no result products
                        if( $prods_array ){
                            foreach ($prods_array as $key => $prod){
                                if( $prod->id_prodwc > 0 && ! isset($api_products[$key]) ){
                                    update_post_meta($prod->id_prodwc, '_stock', '0');
                                    update_post_meta($prod->id_prodwc, '_stock_status', 'outofstock');
                                }
                            }
                        }
                        wp_send_json(array('success'=>true, 'msg'=>__('Products have synced successfully!')));
                    } else {
                        wp_send_json(array('success'=>true, 'msg'=>__('No products found!')));
                    }
                }
            }
            wp_send_json(array('success'=>true, 'msg'=>__('Something went wrong, try again later!')));
            exit;
        }

        function sync_from_api(){
            $creds = get_option('msy_user_registration_data', []);
            if( isset($creds['userid'], $creds['authorization']) && $creds['userid'] > 0 && $creds['authorization'] ){
                $headers = array(
                    'userid'        => $creds['userid'],
                    'authorization' => $creds['authorization']
                );
                $last20Minutes = strtotime(date('Y-m-d H:i:s')) - (20 * MINUTE_IN_SECONDS);
                $response = wp_remote_get(
                    'https://msyds.madtec.be/api/products/since/'.$last20Minutes,
                    array(
                        'body'      => [],
                        'timeout'   => 120,
                        'headers'   => $headers
                    )
                );
                $status_code = wp_remote_retrieve_response_code($response);
                if( ! is_wp_error($response) && $status_code == 200 ){
                    $products = json_decode(wp_remote_retrieve_body($response), true);
                    if( $products ){
                        global $wpdb;
                        $prods_table = $wpdb->prefix.'msyds_products';
                        $query = $wpdb->prepare("SELECT `id`,`id_product`,`id_prodwc`,`sku` FROM $prods_table");
                        $existed_prods = $wpdb->get_results($query);
                        $prods_array = array();
                        if( $existed_prods ){
                            foreach ($existed_prods as $existed_prod) {
                                $prods_array[$existed_prod->id_product.'_'.$existed_prod->sku] = $existed_prod;
                            }
                        }
                        $created = date('Y-m-d H:s:i');
                        $meta_data = json_encode(['updated_at'=>$created]);
                        $old_prods_sql = array();
                        $new_prods_sql = array();
                        foreach ($products as $product){
                            if( isset($product['variants']) ){
                                foreach ($product['variants'] as $variant){
                                    $prod_key = $product['id'].'_'.$variant['sku'];
                                    if( isset($prods_array[$prod_key]) ){
                                        $existed_prod = $prods_array[$product['id'].'_'.$variant['sku']];
                                        $sql = "UPDATE $prods_table SET `id_category`='".esc_sql($product['category'])."', `vendor`='".esc_sql($product['vendor'])."', `en_title`='".esc_sql($variant['en'])."', `nl_title`='".esc_sql($variant['nl'])."', `es_title`='".esc_sql($variant['es'])."', `fr_title`='".esc_sql($variant['fr'])."', `de_title`='".esc_sql($variant['de'])."', `it_title`='".esc_sql($variant['it'])."', `price`='".esc_sql($variant['price'])."', `price_rec`='".esc_sql($variant['price_recommended'])."', `ean`='".esc_sql($variant['ean'])."', `sku`='".esc_sql($variant['sku'])."', ";
                                        $sql .= "`quantity`='".esc_sql($variant['quantity'])."', `weight`='".esc_sql($variant['weight'])."', `volume`='".esc_sql($variant['volume'])."', `length`='".esc_sql($variant['length'])."', `height`='".esc_sql($variant['height'])."', `width`='".esc_sql($variant['width'])."', `pics`='".esc_sql(implode('||', $variant['pics']))."', `created_at`='".esc_sql($created)."', `meta_data`='".esc_sql($meta_data)."' WHERE `id`='".esc_sql($existed_prod->id)."'";
                                        $old_prods_sql[] = $wpdb->prepare(str_replace(';', '', $sql));
                                        if( $existed_prod->id_prodwc > 0 ){
                                            if( $variant['quantity'] > 0 ){
                                                update_post_meta($existed_prod->id_prodwc, '_stock', esc_sql($variant['quantity']));
                                                update_post_meta($existed_prod->id_prodwc, '_stock_status', 'instock');
                                            } else {
                                                update_post_meta($existed_prod->id_prodwc, '_stock', '0');
                                                update_post_meta($existed_prod->id_prodwc, '_stock_status', 'outofstock');
                                            }
                                        }
                                    } else {
                                        $sql = "('".absint($product['id'])."', '".esc_sql($product['category'])."', '".esc_sql($product['vendor'])."', '".esc_sql($variant['en'])."', '".esc_sql($variant['nl'])."', '".esc_sql($variant['es'])."', '".esc_sql($variant['fr'])."', '".esc_sql($variant['de'])."', '".esc_sql($variant['it'])."', '".esc_sql($variant['price'])."', '".esc_sql($variant['price_recommended'])."', '".esc_sql($variant['ean'])."', '".esc_sql($variant['sku'])."', ";
                                        $sql .= "'".esc_sql($variant['quantity'])."', '".esc_sql($variant['weight'])."', '".esc_sql($variant['volume'])."', '".esc_sql($variant['length'])."', '".esc_sql($variant['height'])."', '".esc_sql($variant['width'])."', '".esc_sql(implode('||', $variant['pics']))."', '".esc_sql($created)."', '".esc_sql($meta_data)."')";
                                        $new_prods_sql[] = $wpdb->prepare(str_replace(';', '', $sql));
                                    }
                                }
                            }
                        }
                        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                        if( $old_prods_sql ){
                            $sql = implode('; ', $old_prods_sql) . "; ";
                            $result = dbDelta($sql);
                        }
                        if( $new_prods_sql ){
                            $sql = "";
                            $per_sql = 100;
                            for( $i=0; $i<count($new_prods_sql); $i=$i+$per_sql ){
                                $values = array_slice($new_prods_sql, $i, $per_sql);
                                $sql .= "INSERT INTO $prods_table (`id_product`, `id_category`, `vendor`, `en_title`, `nl_title`, `es_title`, `fr_title`, `de_title`, `it_title`, `price`, `price_rec`, `ean`, `sku`, `quantity`, `weight`, `volume`, `length`, `height`, `width`, `pics`, `created_at`, `meta_data`) VALUES " . implode(', ', $values) . "; ";
                            }
                            $result = dbDelta($sql);
                        }
                        wp_send_json(array('success'=>true, 'msg'=>__('Products have synced successfully!')));
                    } else {
                        wp_send_json(array('success'=>true, 'msg'=>__('No products found!')));
                    }
                }
            }
            wp_send_json(array('success'=>true, 'msg'=>__('Something went wrong, try again later!')));
            exit;
        }

        function import_products(){
            if ( isset($_POST['product_ids'], $_POST['title'], $_POST['sale_price'], $_POST['category_id'], $_POST['nonce']) && $_POST['product_ids'] && wp_verify_nonce($_POST['nonce'], 'msy_nonce') ){
                global $wpdb;
                $table_name = $wpdb->prefix.'msyds_products';
                foreach ($_POST['product_ids'] as $product_id){
                    $product_id = absint(sanitize_text_field($product_id));
                    $query = $wpdb->prepare("SELECT * FROM ".$table_name." WHERE `id`='".$product_id."'");
                    $data = $wpdb->get_row($query, ARRAY_A);
                    if( $data ) {
                        $query = $wpdb->prepare("SELECT * FROM " . $wpdb->postmeta . " WHERE `meta_key`='msyds_product_id' AND meta_value='" . esc_sql($product_id) . "'");
                        $exists = $wpdb->get_row($query, ARRAY_A);
                        if ($exists) {
                            $product = new WC_Product_Simple(absint($exists['post_id']));
                        } else {
                            $product = new WC_Product_Simple();
                        }
                        $title = sanitize_text_field($_POST['title'][$product_id]);
                        $product->set_name(esc_sql($title));
                        $description = wp_kses_post($_POST['description'][$product_id]);
                        $product->set_description(esc_sql($description));
                        $product->set_sku(esc_sql($data['sku']));
                        if (isset($_POST['prod_status'][$product_id]) && $_POST['prod_status'][$product_id] == 'draft') {
                            $product->set_status('draft');
                        } else {
                            $product->set_status('publish');
                        }
                        $product->set_catalog_visibility('visible');
                        $price = sanitize_text_field($_POST['sale_price'][$product_id]);
                        $product->set_price($price);
                        $product->set_regular_price($price);
                        $product->set_manage_stock(true);
                        $product->set_stock_status(($data['quantity'] > 0 ? 'instock' : 'outofstock'));
                        $product->set_stock_quantity(absint($data['quantity']));
                        $product->set_weight(esc_sql($data['weight']));
                        $product->set_length(esc_sql($data['length']));
                        $product->set_width(esc_sql($data['width']));
                        $product->set_height(esc_sql($data['height']));
                        $product->update_meta_data('msyds_base_price', esc_sql($data['price']));
                        $margin = sanitize_text_field($_POST['margin'][$product_id]);
                        $product->update_meta_data('msyds_margin_price', esc_sql($margin));
                        $tax = sanitize_text_field($_POST['tax'][$product_id]);
                        $product->update_meta_data('msyds_tax_price', esc_sql($tax));
                        $product->update_meta_data('msyds_ean_value', esc_sql($data['ean']));
                        $product->update_meta_data('msyds_pics_urls', esc_sql($data['pics']));
                        $product->update_meta_data('msyds_product_id', absint($data['id_product']));
                        if (isset($_POST['category_id'][$product_id]) && $_POST['category_id'][$product_id] > 0) {
                            $category_id = sanitize_text_field($_POST['category_id'][$product_id]);
                            $product->set_category_ids([absint($category_id)]);
                        }
                        $id_prodwc = $product->save();
                        $wpdb->update(
                            $table_name,
                            array('id_prodwc' => absint($id_prodwc)),
                            array('id' => absint($data['id']))
                        );
                        $imports = get_option('msy_imported_products_ids', []);
                        $imports = ($imports && is_array($imports)) ? $imports : array();
                        if (($key = array_search($product_id, $imports)) !== false) {
                            unset($imports[$key]);
                            update_option('msy_imported_products_ids', $imports);
                        }
                        $images_urls = $data['pics'] ? explode('||', $data['pics']) : [];
                        if( $images_urls ){
                            foreach ($images_urls as $key => $url){
                                if ( stripos($url, 'youtube') === FALSE ) {
                                    $image_id = media_sideload_image($url, $id_prodwc, '', 'id');
                                    if( ! is_wp_error($image_id) ){
                                        update_post_meta($id_prodwc, '_thumbnail_id', $image_id);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                $response = array('success'=>true, 'msg'=>__('Product(s) have imported successfully!'));
            } else {
                $response = array('success'=>false, 'msg'=>__('Invalid/incomplete parameters passed!'));
            }
            wp_send_json($response);
        }

        function import_images(){
            $products = get_posts(array(
                'post_type'     => 'product',
                'post_status'   => 'publish',
                'fields'        => 'ids',
                'meta_query'    => array(
                    'relation'  => 'AND',
                    array(
                        'key'       => 'msyds_product_id',
                        'compare'   => 'EXISTS'
                    ),
                    array(
                        'relation'  => 'OR',
                        array(
                            'key'       => '_product_image_gallery',
                            'value'     => 'null',
                            'compare'   => 'NOT EXISTS',
                        ),
                        array(
                            'key'       => '_product_image_gallery',
                            'value'     => '',
                            'compare'   => '=',
                        ),
                    ),
                )
            ));
            if( $products ){
                $images = [];
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                foreach ($products as $product_id){
                    if( ! isset($images[$product_id]) ){
                        $images[$product_id] = [];
                    }
                    $images_urls = get_post_meta($product_id, 'msyds_pics_urls', true);
                    $images_urls = $images_urls ? explode('||', $images_urls) : [];
                    if( $images_urls ){
                        $images_ids = [];
                        foreach ($images_urls as $key => $url){
                            if ( stripos($url, 'youtube') === FALSE ) {
                                $images[$product_id][] = $url;
                                $image_id = media_sideload_image($url, $product_id, '', 'id');
                                if( ! is_wp_error($image_id) ){
                                    if( $key > 0 ){
                                        $images_ids[] = $image_id;
                                    }
                                }
                            }
                        }
                        if( $images_ids ){
                            update_post_meta($product_id, '_product_image_gallery', implode(',', $images_ids));
                        }
                    }
                }
                $response = array('success'=>true, 'msg'=>__('Image have imported successfully!'));
            } else {
                $response = array('success'=>false, 'msg'=>__('No products found to sync images!'));
            }
            wp_send_json($response);
        }

        function add_to_import(){
            if ( isset($_POST['category_id'], $_POST['product_id'], $_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'msy_nonce') ) {
                $imports = get_option('msy_imported_products_ids', []);
                $imports = ($imports && is_array($imports)) ? $imports : array();
                if( $_POST['category_id'] > 0 ){
                    global $wpdb;
                    $table_name = $wpdb->prefix.'msyds_products';
                    $category_id = sanitize_text_field($_POST['category_id']);
                    $query = $wpdb->prepare("SELECT `id` FROM $table_name WHERE (`id_prodwc` IS NULL OR `id_prodwc`=0) AND `id_category`='".absint($category_id)."' AND `quantity`>0");
                    $rows = $wpdb->get_results($query, ARRAY_A);
                    if( $rows ){
                        $prod_ids = array_column($rows, 'id');
                        $imports = array_unique(array_merge($imports, $prod_ids));
                    }
                } elseif( $_POST['product_id'] > 0 ) {
                    $product_id = sanitize_text_field($_POST['product_id']);
                    $imports = array_unique(array_merge($imports, [absint($product_id)]));
                }
                update_option('msy_imported_products_ids', esc_sql($imports));
                $response = array('success'=>true, 'msg'=>__('Products have imported successfully!'));
            } else {
                $response = array('success'=>false, 'msg'=>__('Parameters are missing, try again!'));
            }
            wp_send_json($response);
        }

        function clear_all_imports(){
            if ( isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'msy_nonce') ) {
                update_option('msy_imported_products_ids', []);
                $response = array('success'=>true, 'msg'=>__('Products have removed from imports!'));
            } else {
                $response = array('success'=>false, 'msg'=>__('Parameters are missing, try again!'));
            }
            wp_send_json($response);
        }

        function remove_product(){
            if ( isset($_POST['product_id'], $_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'msy_nonce') ) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'msyds_products';
                $product_id = absint(sanitize_text_field($_POST['product_id']));
                $query = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE `id_prodwc`='" . esc_sql($product_id) . "'");
                $data = $wpdb->get_row($query, ARRAY_A);
                if ($data) {
                    $product = new WC_Product_Simple($product_id);
                    $product->delete(true);
                    $wpdb->update(
                        $table_name,
                        array('id_prodwc' => ''),
                        array('id' => absint($data['id']))
                    );
                    $response = array('success'=>true, 'msg'=>__('Product has removed from shop successfully!'));
                } else {
                    $response = array('success'=>false, 'msg'=>__('Invalid product remove request!'));
                }
            } else {
                $response = array('success'=>false, 'msg'=>__('Parameters are missing, try again!'));
            }
            wp_send_json($response);
        }

        function get_langs(){
            return array(
                'en'    => __('English'),
                'nl'    => __('Dutch'),
                'es'    => __('Spanish'),
                'fr'    => __('French'),
                'de'    => __('German'),
                'it'    => __('Italian')
            );
        }
    }


}
