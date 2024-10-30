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

if( ! class_exists('Msydrop_Shipping_Category') ){

    class Msydrop_Shipping_Category
    {
        function sync_from_api(){
            $creds = get_option('msy_user_registration_data', []);
            if( isset($creds['userid'], $creds['authorization']) && $creds['userid'] > 0 && $creds['authorization'] ){
                $headers = array(
                    'userid'        => $creds['userid'],
                    'authorization' => $creds['authorization']
                );
                $response = wp_remote_get(
                    'https://msyds.madtec.be/api/categories',
                    array(
                        'body'      => [],
                        'headers'   => $headers
                    )
                );
                $status_code = wp_remote_retrieve_response_code($response);
                if( ! is_wp_error($response) && $status_code == 200 ){
                    $categories = json_decode(wp_remote_retrieve_body($response), true);
                    if( $categories ){
                        global $wpdb;
                        $table_name = $wpdb->prefix.'msyds_categories';
                        $wpdb->query("TRUNCATE $table_name");
                        $created = date('Y-m-d H:s:i');
                        $values = array();
                        foreach ($categories as $category){
                            $category_id = absint($category['id']);
                            $en_title = sanitize_text_field($category['en']);
                            $nl_title = sanitize_text_field($category['nl']);
                            $es_title = sanitize_text_field($category['es']);
                            $fr_title = sanitize_text_field($category['fr']);
                            $de_title = sanitize_text_field($category['de']);
                            $it_title = sanitize_text_field($category['it']);
                            $values[] = "('".esc_sql($category_id)."', '', '".esc_sql($en_title)."', '".esc_sql($nl_title)."', '".esc_sql($es_title)."', '".esc_sql($fr_title)."', '".esc_sql($de_title)."', '".esc_sql($it_title)."', '".$created."')";
                            if( isset($category['subcat']) ){
                                foreach ($category['subcat'] as $subcat){
                                    $subcat_id = absint($subcat['id']);
                                    $en_title = sanitize_text_field($subcat['en']);
                                    $nl_title = sanitize_text_field($subcat['nl']);
                                    $es_title = sanitize_text_field($subcat['es']);
                                    $fr_title = sanitize_text_field($subcat['fr']);
                                    $de_title = sanitize_text_field($subcat['de']);
                                    $it_title = sanitize_text_field($subcat['it']);
                                    $values[] = "('".esc_sql($subcat_id)."', '".esc_sql($category_id)."', '".esc_sql($en_title)."', '".esc_sql($nl_title)."', '".esc_sql($es_title)."', '".esc_sql($fr_title)."', '".esc_sql($de_title)."', '".esc_sql($it_title)."', '".$created."')";
                                }
                            }
                        }
                        $sql = "INSERT INTO $table_name (`id_system`, `id_parent`, `en_title`, `nl_title`, `es_title`, `fr_title`, `de_title`, `it_title`, `created_at`) VALUES " . implode(', ', $values);
                        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                        $result = dbDelta($sql);
                    }
                    die('Categories have synced successfully!');
                }
            }
        }
    }

}
