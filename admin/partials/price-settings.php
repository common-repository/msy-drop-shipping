<?php
$options = get_option('msy_price_settings_options', []);
settings_errors();
?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form action="options.php" method="post" id="msy-price-settings">
        <?php
        settings_fields( 'msy-price-settings' );
        do_settings_sections( 'msy-price-settings' );
        ?>
        <table class="form-table widefat">
            <thead>
            <tr>
                <td colspan="2">
                    <b><?php _e('Shop Prices'); ?></b>
                </td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td width="200"><label for="shop-margin"><?php _e('Margin Percentage'); ?></label></td>
                <td><input type="number" name="msy_price_settings_options[shop]" id="shop-margin" class="" value="<?php echo isset($options['shop']) ? esc_attr($options['shop']) : ''; ?>" required>%</td>
            </tr>
            <tr>
                <td><label for="shop-tax"><?php _e('Tax Percentage'); ?></label></td>
                <td><input type="number" name="msy_price_settings_options[tax]" id="shop-tax" class="" value="<?php echo isset($options['tax']) ? esc_attr($options['tax']) : ''; ?>">%</td>
            </tr>
            </tbody>
        </table>
        <?php
        $args = array(
            'taxonomy'   => "product_cat",
            'hide_empty' => false
        );
        $categories = get_terms($args);
        if( ! is_wp_error($categories) && $categories ){ ?>
            <table class="form-table widefat">
                <thead>
                <tr>
                    <td colspan="2">
                        <b><?php _e('Category Margin'); ?></b>
                    </td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $category){
                    echo '<tr><td width="200"><label for="'.esc_attr($category->slug).'">'.esc_html($category->name).'</label></td>';
                    echo '<td><input type="number" name="msy_price_settings_options['.esc_attr($category->term_id).']" id="'.esc_attr($category->slug).'" class="" value="'.(isset($options[$category->term_id]) ? esc_attr($options[$category->term_id]) : '').'">%</td></tr>'; ?>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>
        <?php submit_button( 'Save Settings' ); ?>
    </form>
</div>