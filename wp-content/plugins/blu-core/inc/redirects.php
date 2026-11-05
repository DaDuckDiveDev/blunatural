<?php
/**
 * 301 redirects from the Shopify URL structure so four years of links and search rankings carry over.
 *   /products/{handle}        -> /product/{handle}/
 *   /collections/all          -> /shop/
 *   /collections/{x}          -> /product-category/{x}/ if it exists, else /shop/
 *   /pages/{slug}             -> /{slug}/ if the page exists, else home
 *   /blogs/{anything}         -> /journal/
 *   /account, /account/login  -> /my-account/
 *   /cart                     -> /cart/
 */

defined( 'ABSPATH' ) || exit;

add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$path = trim( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );
	if ( '' === $path ) {
		return;
	}
	$parts = explode( '/', $path );
	$to    = '';

	switch ( $parts[0] ) {
		case 'products':
			if ( ! empty( $parts[1] ) ) {
				$handle = sanitize_title( $parts[1] );
				$map    = apply_filters( 'blu_shopify_handle_map', array(
					'blackbird-organic-black-tea-tree-bar-soap' => 'blakbird-organic-charcoal-tea-tree-bar-soap',
					'naturally-digital-download'                => 'diy-recipes-for-home-self-care',
					'blubird-naturals-gift-card'                => 'gift-card',
				) );
				$slug = $map[ $handle ] ?? $handle;
				$post = get_page_by_path( $slug, OBJECT, 'product' );
				$to   = $post ? get_permalink( $post ) : wc_get_page_permalink( 'shop' );
			}
			break;
		case 'collections':
			$to = wc_get_page_permalink( 'shop' );
			if ( ! empty( $parts[1] ) && 'all' !== $parts[1] ) {
				$term = get_term_by( 'slug', sanitize_title( $parts[1] ), 'product_cat' );
				if ( $term ) {
					$to = get_term_link( $term );
				}
			}
			break;
		case 'pages':
			if ( ! empty( $parts[1] ) ) {
				$page = get_page_by_path( sanitize_title( $parts[1] ) );
				$to   = $page ? get_permalink( $page ) : home_url( '/' );
			}
			break;
		case 'blogs':
			$to = home_url( '/journal/' );
			break;
		case 'account':
			$to = wc_get_page_permalink( 'myaccount' );
			break;
		case 'cart':
			$to = wc_get_cart_url();
			break;
	}

	if ( $to ) {
		wp_safe_redirect( $to, 301 );
		exit;
	}
} );
