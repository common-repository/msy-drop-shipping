<?php

/**
 * The categories functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Msydrop_Shipping
 * @subpackage Msydrop_Shipping/includes
 * @author     DigiXoft <arslan@digixoft.com>
 */

if( ! class_exists('Msydrop_Shipping_Order') ){

    class Msydrop_Shipping_Order
    {

        function wc_order_save($order){
            $items = $order->get_items();
            if( $items ){
                foreach ( $items as $item_id => $item ) {
                    $msyds_product = $item->get_meta( 'msyds_product_id', true );
                    if( $msyds_product == 'yes' ){
                        update_post_meta($order->get_id(), 'msyds_product_include', 'yes');
                        update_post_meta($order->get_id(), 'msyds_order_synced', 'no');
                        break;
                    }
                }
            }
        }

        function wc_order_item_meta($item){
            $product_id = absint($item->get_product_id());
            global $wpdb;
            $table_name = $wpdb->prefix."msyds_products";
            $query = $wpdb->prepare("SELECT * FROM ".$table_name." WHERE `id_prodwc`='".esc_sql($product_id)."'");
            $exists = $wpdb->get_row($query);
            if( $exists ){
                $item->update_meta_data( 'msyds_product_id', 'yes' );
                $item->update_meta_data( 'msyds_product_ean', esc_sql($exists->ean) );
                $item->update_meta_data( 'msyds_product_sku', esc_sql($exists->sku) );
            }
        }

        function process_all_orders(){
            if ( isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'msy_nonce') ) {
                $args = array(
                    'post_type'     => 'shop_order',
                    'post_status'   => 'any',
                    'posts_per_page'=> -1,
                    'meta_key'      => 'msyds_order_synced',
                    'meta_value'    => 'no',
                    'meta_query'    => array(
                        array(
                            'key'       => 'msyds_order_deleted',
                            'compare'   => 'NOT EXISTS'
                        )
                    )
                );
                if( isset($_POST['order_ids']) ){
                    if( $_POST['order_ids'] && is_array($_POST['order_ids']) ){
                        $args['post__in'] = array_filter($_POST['order_ids'], 'ctype_digit');
                    } else {
                        $response = array('success'=>false, 'msg'=>__('No order has selected, select orders and try again!'));
                        wp_send_json($response);
                    }
                }
                $query = new WP_Query($args);
                if( $query->have_posts() ) {
                    $productsArray = array();
                    $ordersIDs = array();
                    $itemsObjs = array();
                    global $wpdb;
                    $table_name =  $wpdb->prefix . 'msyds_products';
                    while ( $query->have_posts() ){
                        $query->the_post();
                        $order = wc_get_order(get_the_ID());
                        $products = $order->get_items();
                        if( $products ){
                            foreach ($products as $item_id => $item){
                                $product_id = absint($item->get_product_id());
                                $sql = $wpdb->prepare("SELECT `id_product`, `ean`, `sku` FROM `$table_name` WHERE `id_prodwc`=".esc_sql($product_id));
                                $result = $wpdb->get_row($sql);
                                if( $result ){
                                    $ordersIDs[$result->sku] = $order->get_id();
                                    $itemsObjs[$result->sku] = $item_id;
                                    $orderArray = array(
  					                  "reforder" => $order->get_id(),
   						              "name" => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
    						          "address" => $order->get_shipping_address_1(),
                                      "post" => $order->get_shipping_postcode(),
                                      "city" => $order->get_shipping_city(),
    						          "countrycode" => $order->get_shipping_country(),
 						              "telephone" => $order->get_billing_phone(),
  						              "email" => $order->get_billing_email(),
   						              "sku" => $result->sku,
    					              "quan" => $item->get_quantity()
						);
						$productsArray[] = $orderArray;
                            
                               }
                            }
                        }
                    }
                    wp_reset_postdata();

                    $orderApiData = $productsArray;
                    $creds = get_option('msy_user_registration_data', []);
                    if( isset($creds['userid'], $creds['authorization']) && $creds['userid'] > 0 && $creds['authorization'] ) {
                        $headers = array(
                            'userid' => $creds['userid'],
                            'authorization' => $creds['authorization'],
                            'content-type' => 'application/json',
                        ); 

                        $api_response = wp_remote_post('https://msyds.madtec.be/api/orders', array(
                            'headers' => $headers,
                            'body' => json_encode($orderApiData)
                        ));

                        if( ! is_wp_error($api_response) ){
                            $data = json_decode(wp_remote_retrieve_body($api_response), true);
                            if( isset($data['orderID'], $data['Orders']) && $data['orderID'] && $data['Orders'] ){
                                foreach ($data['Orders'] as $order) {
                                    if( isset($order['id']) && $order['id'] > 0 ){
                                        if( isset($itemsObjs[$order['sku']]) ){
                                            wc_update_order_item_meta($itemsObjs[$order['sku']], '_msyds_product_order_data', json_encode($order));
                                            wc_update_order_item_meta($itemsObjs[$order['sku']], '_msyds_product_order_id', esc_sql($order['id']));
                                            update_post_meta($ordersIDs[$order['sku']], 'msyds_main_order_id', esc_sql($data['orderID']));
                                            update_post_meta($ordersIDs[$order['sku']], 'msyds_order_synced', 'yes');
                                        }
                                    } elseif( isset($order['error']) && $order['error'] ) {
                                        if( isset($itemsObjs[$order['sku']]) ){
                                            wc_update_order_item_meta($itemsObjs[$order['sku']], '_msyds_product_order_data', json_encode($order));
                                            wc_update_order_item_meta($itemsObjs[$order['sku']], 'msyds_product_order_error', esc_sql($order['error']));
                                            update_post_meta($ordersIDs[$order['sku']], 'msyds_main_order_id', esc_sql($data['orderID']));
                                            update_post_meta($ordersIDs[$order['sku']], 'msyds_order_synced', 'yes');
                                        }
                                    }
                                }
                                update_option('msyds_last_order_sync_date', date('Ymd'), 'no');
                                $response = array('success'=>true, 'msg'=>$data['message']);
                            } else {
                                $response = array('success'=>false, 'msg'=>$data['message']);
                            }
                        } else {
                            $response = array('success'=>false, 'msg'=>$api_response->get_error_message());
                        }
                    } else {
                        $response = array('success'=>false, 'msg'=>__('API credentials not found!'));
                    }
                } else {
                    $response = array('success'=>false, 'msg'=>__('No data found to sync!'));
                }
            } else {
                $response = array('success'=>false, 'msg'=>__('Security nonce mismatched, reload and try again!'));
            }
            wp_send_json($response);
        }

        function delete_order(){
            if ( isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'msy_nonce') ) {
                if( isset($_POST['order_id']) && $_POST['order_id'] > 0 ){
                    $order_id = absint(sanitize_text_field($_POST['order_id']));
                    update_post_meta($order_id, 'msyds_order_deleted', 'yes');
                    $response = array('success'=>true, 'msg'=>__('Order has deleted successfully!'));
                } else {
                    $response = array('success'=>false, 'msg'=>__('Required parameter not found, try again!'));
                }
            } else {
                $response = array('success'=>false, 'msg'=>__('Security nonce mismatched, reload and try again!'));
            }
            wp_send_json($response);
        }

        function wc_order_item_get_meta_data($formatted_meta, $item){
            $hidden_keys = array('msyds_product_id', 'msyds_product_ean', 'msyds_product_sku');
            if( $formatted_meta ){
                foreach($formatted_meta as $key => $meta){
                    if( in_array($meta->key, $hidden_keys) ) {
                        unset($formatted_meta[$key]);
                    }
                }
            }
            return $formatted_meta;
        }
    }

}
