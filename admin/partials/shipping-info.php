<div class="wrap">
    <h3 class="wp-heading-inline"><?php _e('Shipping Information'); ?></h3>
    <?php
    $response = wp_remote_get(
        'https://msydrop.com/api/getShipping',
        array(
            'body'      => [],
            'timeout'   => 120,
            'headers'   => ''
        )
    );
    $status_code = wp_remote_retrieve_response_code($response);
    if( ! is_wp_error($response) && $status_code == 200 ) {
        $shippingInfo = json_decode(wp_remote_retrieve_body($response), true);
        ?>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
            <tr>
                <td><b><?php _e('Country'); ?></b></td>
                <td><b><?php _e('ISO'); ?></b></td>
                <td><b><?php _e('Price'); ?></b></td>
                <td><b><?php _e('Delivery Days'); ?></b></td>
                <td><b><?php _e('Return Label'); ?></b></td>
                <td><b><?php _e('FR'); ?></b></td>
                <td><b><?php _e('NL'); ?></b></td>
            </tr>
            </thead>
            <tbody>

            <?php
            if($shippingInfo){
                foreach ($shippingInfo as $info){
                    echo "<tr><td>".esc_html($info['en'])."</td>";
                    echo "<td>".esc_html($info['iso'])."</td>";
                    echo "<td>".esc_html($info['price'])."</td>";
                    echo "<td>".esc_html($info['delivery_days'])."</td>";
                    echo "<td>".esc_html($info['return_label'])."</td>";
                    echo "<td>".esc_html($info['fr'])."</td>";
                    echo "<td>".esc_html($info['nl'])."</td></tr>";
                }
            }
            ?>
            </tbody>
        </table>
    <?php } ?>

</div>
