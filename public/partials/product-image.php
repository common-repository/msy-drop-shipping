<?php

defined( 'ABSPATH' ) || exit;

global $product;

$images = get_post_meta($product->get_id(), 'msyds_pics_urls', true);
$imgs_array = $images ? explode('||', $images) : array();
$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$post_thumbnail_id = $product->get_image_id();
$wrapper_classes   = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	array(
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--' . 'with-images',
		'woocommerce-product-gallery--columns-' . absint( $columns ),
		'images',
	)
);
?>
<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;">
	<figure class="woocommerce-product-gallery__wrapper">
		<?php
		if ( $imgs_array ) {
            foreach ($imgs_array as $item){
                $html = '<div data-thumb="' . esc_url( $item ) . '" data-thumb-alt="' . esc_attr( get_the_title() ) . '" class="woocommerce-product-gallery__image"><a href="' . esc_url( $item ) . '"><img src="' . $item . '"></a></div>';;
                break;
            }
		} else {
			$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
			$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
			$html .= '</div>';
		}
		echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
		do_action( 'woocommerce_product_thumbnails' );
		?>
	</figure>
</div>
