<?php
/**
 * Blu Naturals block theme.
 *
 * @package blu-naturals
 */

defined( 'ABSPATH' ) || exit;

define( 'BLU_THEME_VERSION', '1.0.0' );

/**
 * Theme supports.
 */
function blu_theme_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/theme.css' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'blu-card', 900, 900, true );
	add_image_size( 'blu-portrait', 900, 1125, true );
	add_image_size( 'blu-hero', 2000, 1333, true );
	load_theme_textdomain( 'blu-naturals', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'blu_theme_setup' );

/**
 * Front-end assets.
 */
function blu_theme_assets() {
	wp_enqueue_style( 'blu-theme', get_template_directory_uri() . '/assets/css/theme.css', array(), BLU_THEME_VERSION );
	wp_enqueue_script( 'blu-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), BLU_THEME_VERSION, array( 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'blu_theme_assets' );

/**
 * Pattern categories.
 */
function blu_pattern_categories() {
	register_block_pattern_category( 'blu-home', array( 'label' => __( 'Blu Naturals · Home', 'blu-naturals' ) ) );
	register_block_pattern_category( 'blu-shop', array( 'label' => __( 'Blu Naturals · Shop', 'blu-naturals' ) ) );
	register_block_pattern_category( 'blu-story', array( 'label' => __( 'Blu Naturals · Story & CTA', 'blu-naturals' ) ) );
}
add_action( 'init', 'blu_pattern_categories' );

/**
 * Block styles.
 */
function blu_block_styles() {
	register_block_style( 'core/button', array( 'name' => 'outline-blu', 'label' => __( 'Outline', 'blu-naturals' ) ) );
	register_block_style( 'core/button', array( 'name' => 'accent', 'label' => __( 'Accent', 'blu-naturals' ) ) );
	register_block_style( 'core/group', array( 'name' => 'shoreline', 'label' => __( 'Shoreline', 'blu-naturals' ) ) );
	register_block_style( 'core/group', array( 'name' => 'card', 'label' => __( 'Card', 'blu-naturals' ) ) );
	register_block_style( 'core/image', array( 'name' => 'dissolve', 'label' => __( 'Dissolve into page', 'blu-naturals' ) ) );
	register_block_style( 'core/paragraph', array( 'name' => 'eyebrow', 'label' => __( 'Eyebrow label', 'blu-naturals' ) ) );
}
add_action( 'init', 'blu_block_styles' );

/**
 * Body classes: product category context (Blakbird band styling) and page templates.
 */
function blu_body_class( $classes ) {
	if ( function_exists( 'is_product' ) && is_product() ) {
		$terms = get_the_terms( get_the_ID(), 'product_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$classes[] = 'blu-cat-' . sanitize_html_class( $t->slug );
			}
		}
	}
	return $classes;
}
add_filter( 'body_class', 'blu_body_class' );

/**
 * WooCommerce auto-inserts its account and mini-cart blocks into the header; we place them ourselves.
 */
add_filter( 'hooked_block_types', function ( $types, $relative_position, $anchor_block ) {
	if ( 'core/navigation' === $anchor_block || 'core/template-part' === $anchor_block ) {
		$types = array_values( array_diff( $types, array( 'woocommerce/customer-account', 'woocommerce/mini-cart' ) ) );
	}
	return $types;
}, 10, 3 );

/**
 * WooCommerce: products per page, placeholder image, breadcrumb home label.
 */
add_filter( 'loop_shop_per_page', fn() => 12 );
add_filter( 'woocommerce_breadcrumb_home_url', fn() => home_url( '/' ) );
add_filter( 'woocommerce_breadcrumb_defaults', function ( $d ) { $d['home'] = __( 'Home', 'blu-naturals' ); return $d; } );

/**
 * Default placeholder: the bird mark on mist.
 */
add_filter( 'woocommerce_placeholder_img_src', fn() => get_template_directory_uri() . '/assets/img/blu-mark-circle.svg' );

/**
 * Remove the "Showing x results" + ordering clutter on small shops.
 */
add_action( 'init', function () {
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
} );

/**
 * Add the brand's organization schema once per page (product schema is WooCommerce's own).
 */
function blu_org_schema() {
	if ( ! is_front_page() ) {
		return;
	}
	$logo = content_url( '/uploads/blu/logo/blu-mark-circle.png' );
	$data = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => 'Blu Naturals',
		'url'      => home_url( '/' ),
		'logo'     => $logo,
		'address'  => array( '@type' => 'PostalAddress', 'addressLocality' => 'Honolulu', 'addressRegion' => 'HI', 'addressCountry' => 'US' ),
		'sameAs'   => array_filter( array( get_option( 'blu_instagram_url', '' ) ) ),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}
add_action( 'wp_head', 'blu_org_schema', 20 );
