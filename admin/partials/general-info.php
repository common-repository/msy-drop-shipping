<div class="wrap">
    <h3 class="wp-heading-inline"><?php _e('General Information'); ?></h3>
        <?php
            $response = wp_remote_get(
                'https://msydrop.com/api/getInformation',
                array(
                    'body'      => [],
                    'timeout'   => 120,
                    'headers'   => ''
                )
            );
            $status_code = wp_remote_retrieve_response_code($response);
            if( ! is_wp_error($response) && $status_code == 200 ) {
                $generalInfo = json_decode(wp_remote_retrieve_body($response), true);
            ?>
        <table class="form-table widefat">
            <tbody>
            <?php
            if($generalInfo){
                foreach ($generalInfo as $info){
                    echo "<tr><td>".wp_kses_post($info['content'])."</td></tr>";
                }
            } ?>
            </tbody>
        </table>
    <?php } ?>

</div>
