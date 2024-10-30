<?php
Msydrop_Shipping_Admin::display_notice();
if( (isset($_GET['product_id']) && $_GET['product_id'] > 0) || (isset($_GET['category_id']) && $_GET['category_id'] > 0) ){
    include 'import-products.php';
} else {
    require_once MSYDROP_SHIPPING_CPATH . 'Msydrop_Shipping_Prod_List.php';
    $ziplist = new Msydrop_Shipping_Prod_List();
    $ziplist->prepare_items();
    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"></div>
        <h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
            <input type="hidden" name="page" value="msy-base-products">
            <?php
            $ziplist->search_box('Search', 'search');
            $ziplist->display(); ?>
        </form>
    </div>
<?php }