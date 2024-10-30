<?php
$options = get_option('msy_main_settings_options', []);
$notice = get_option('msyds_notice_message', []);
$reg_data = get_option('msy_user_registration_data', []);
$product = new Msydrop_Shipping_Product();
$languages = $product->get_langs();
$error_data = array();
if( isset($notice['type'], $notice['msg']) ){
    if( $notice['type'] == 'error' ){
        $error_data = isset($notice['data']) ? $notice['data'] : array();
        $error_msg = is_array($notice['msg']) ? implode(', ', $notice['msg']) : $notice['msg']; ?>
        <div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible">
            <p><strong><?php echo __('Error while connecting with MSY: ') . esc_html($error_msg); ?></strong></p>
        </div>
    <?php }
    delete_option('msyds_notice_message');
} else {
    settings_errors();
} ?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form action="options.php" method="post" id="msy-main-settings">
        <?php
        settings_fields( 'msy-main-settings' );
        do_settings_sections( 'msy-main-settings' );
        ?>
        <table class="form-table widefat">
            <thead>
            <tr>
                <td colspan="2">
                    <strong>
                        <?php _e('Connection Status with MSY: ');
                        echo isset($reg_data['userid']) || (isset($reg_data['error']) && $reg_data['error'] === 'User exist') ? '<span class="connected">'.__('Connected').'</span>' : '<span class="not-connected">'.__('Not Connected').'</span>';
                        // echo isset($reg_data['userid']) ? '<span class="connected">'.__('Connected').'</span>' : '<span class="not-connected">'.__('Not Connected').'</span>'; ?>
                    </strong>
                </td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td width="180"><label for="first_name"><?php _e('First Name'); ?></label></td>
                <td><input type="text" name="msy_main_settings_options[first_name]" id="first_name" class="" value="<?php echo isset($options['first_name']) ? esc_attr($options['first_name']) : ''; ?>" required></td>
            </tr>
            <tr>
                <td><label for="last_name"><?php _e('Last Name'); ?></label></td>
                <td><input type="text" name="msy_main_settings_options[last_name]" id="last_name" class="" value="<?php echo isset($options['last_name']) ? esc_attr($options['last_name']) : ''; ?>" required></td>
            </tr>
            <tr>
                <td><label><?php _e('Gender'); ?></label></td>
                <td>
                    <label class="gender">
                        <input type="radio" name="msy_main_settings_options[gender]" value="m" <?php echo ((isset($options['gender']) && $options['gender'] == 'm') || ! isset($options['gender'])) ? 'checked' : ''; ?>>
                        <?php _e('Male'); ?>
                    </label>
                    <label class="gender">
                        <input type="radio" name="msy_main_settings_options[gender]" value="f" <?php echo (isset($options['gender']) && $options['gender'] == 'f') ? 'checked' : ''; ?>>
                        <?php _e('Female'); ?>
                    </label>
                    <label class="gender">
                        <input type="radio" name="msy_main_settings_options[gender]" value="o" <?php echo (isset($options['gender']) && $options['gender'] == 'o') ? 'checked' : ''; ?>>
                        <?php _e('Other'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td><label for="email"><?php _e('Email'); ?></label></td>
                <td><input type="email" name="msy_main_settings_options[email]" id="email" class="" value="<?php echo isset($options['email']) ? esc_attr($options['email']) : ''; ?>" required></td>
            </tr>
            <tr>
                <td><label for="phone"><?php _e('Phone'); ?></label></td>
                <td><input type="text" name="msy_main_settings_options[phone]" id="phone" class="" value="<?php echo isset($options['phone']) ? esc_attr($options['phone']) : ''; ?>" required></td>
            </tr>
            <tr>
                <td><label for="company"><?php _e('Company'); ?></label></td>
                <td><input type="text" name="msy_main_settings_options[company]" id="company" class="" value="<?php echo isset($options['company']) ? esc_attr($options['company']) : ''; ?>" required></td>
            </tr>
            <tr>
                <td><label for="vat_number"><?php _e('VAT Number'); ?></label></td>
                <td><input type="text" name="msy_main_settings_options[vat_number]" id="vat_number" class="" value="<?php echo isset($options['vat_number']) ? esc_attr($options['vat_number']) : ''; ?>" required></td>
            </tr>
            <tr>
                <td><label for="street"><?php _e('Street'); ?></label></td>
                <td><input type="text" name="msy_main_settings_options[street]" id="street" class="" value="<?php echo isset($options['street']) ? esc_attr($options['street']) : ''; ?>" required></td>
            </tr>
            <tr>
                <td><label for="postcode"><?php _e('Post Code'); ?></label></td>
                <td><input type="text" name="msy_main_settings_options[postcode]" id="postcode" class="" value="<?php echo isset($options['postcode']) ? esc_attr($options['postcode']) : ''; ?>" required></td>
            </tr>
            <tr>
                <td><label for="city"><?php _e('City'); ?></label></td>
                <td><input type="text" name="msy_main_settings_options[city]" id="city" class="" value="<?php echo isset($options['city']) ? esc_attr($options['city']) : ''; ?>" required></td>
            </tr>
            <tr>
                <td><label for="country_id"><?php _e('Country'); ?></label></td>
                <td><input type="text" name="msy_main_settings_options[country_id]" id="country_id" class="" value="<?php echo isset($options['country_id']) ? esc_attr($options['country_id']) : ''; ?>" placeholder="BE" required></td>
            </tr>
            <tr>
                <td><label for="language"><?php _e('Feed Language'); ?></label></td>
                <td>
                    <select name="msy_main_settings_options[language]" id="language">
                        <?php $lang_code = isset($options['language']) ? $options['language'] : 'en';
                        foreach ($languages as $key => $language) {
                            $selected = ($key == $lang_code) ? 'selected' : '';
                            echo '<option value="'.esc_attr($key).'" '.$selected.'>'.esc_html($language).'</option>';
                        } ?>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
        <?php submit_button( 'Save Settings' ); ?>
    </form>
</div>