<?php
require_once MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping_OrdA_List.php';
$ziplist = new Msydrop_Shipping_OrdA_List();
$ziplist->prepare_items();
?>
<div class="wrap">
    <div id="icon-users" class="icon32"></div>
    <h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
        <input type="hidden" name="page" value="msy-base-categories">
        <?php $ziplist->display(); ?>
    </form>
</div>
