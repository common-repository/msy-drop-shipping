<?php

defined( 'ABSPATH' ) || exit;

global $product;

$images = get_post_meta($product->get_id(), 'msyds_pics_urls', true);
$imgs_array = $images ? explode('||', $images) : array();

if ( $imgs_array ) {
	foreach ( $imgs_array as $item ) {
        echo '<div data-thumb="' . esc_url( $item ) . '" data-thumb-alt="' . esc_attr( get_the_title() ) . '" class="woocommerce-product-gallery__image"><a href="' . esc_url( $item ) . '"><img src="' . $item . '"></a></div>';
	}
}
