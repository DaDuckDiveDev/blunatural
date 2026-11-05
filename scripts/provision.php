<?php
/**
 * Provision a fresh WordPress + WooCommerce install for Blu Naturals.
 *
 * Run from the WordPress root:   wp eval-file /path/to/repo/scripts/provision.php [--allow-root]
 * Idempotent: products, pages and coupons are matched by slug/code and updated, not duplicated.
 *
 * Expects the repo's wp-content/themes/blu-naturals, wp-content/plugins/blu-core and
 * wp-content/uploads/blu to already be in place (setup-local.sh / deploy.yml put them there).
 */

if ( ! defined( 'ABSPATH' ) ) {
	echo "Run via: wp eval-file scripts/provision.php\n";
	exit( 1 );
}

$repo = dirname( __DIR__ );
$spec = json_decode( file_get_contents( $repo . '/import/products.json' ), true );
if ( ! $spec ) {
	WP_CLI::error( 'import/products.json is missing or invalid' );
}

$log = function ( $m ) { WP_CLI::log( '  ' . $m ); };

/* ---------- 1. Core options ---------- */
WP_CLI::log( 'Core options' );
update_option( 'blogname', 'Blu Naturals' );
update_option( 'blogdescription', 'Small-batch natural care, made by a family in Honolulu' );
update_option( 'timezone_string', 'Pacific/Honolulu' );
update_option( 'date_format', 'F j, Y' );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'default_comment_status', 'closed' );
update_option( 'blog_public', '0' ); // keep search engines out until launch; flip to 1 at go-live.

/* ---------- 2. Theme + plugins ---------- */
WP_CLI::log( 'Theme and plugins' );
if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	activate_plugin( 'woocommerce/woocommerce.php' );
}
if ( ! is_plugin_active( 'blu-core/blu-core.php' ) ) {
	activate_plugin( 'blu-core/blu-core.php' );
}
if ( get_stylesheet() !== 'blu-naturals' ) {
	switch_theme( 'blu-naturals' );
}
if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce did not load; re-run after activation.' );
}
// The theme may have been switched in this same request, after init: register its patterns now.
if ( ! WP_Block_Patterns_Registry::get_instance()->is_registered( 'blu/home' ) ) {
	_register_theme_block_patterns();
	do_action( 'init' ); // theme + plugin init callbacks (pattern categories, block styles)
}

/* ---------- 3. WooCommerce settings ---------- */
WP_CLI::log( 'WooCommerce settings' );
$wc_opts = array(
	'woocommerce_store_address'           => '',
	'woocommerce_store_city'              => 'Honolulu',
	'woocommerce_default_country'         => 'US:HI',
	'woocommerce_store_postcode'          => '96815',
	'woocommerce_currency'                => 'USD',
	'woocommerce_currency_pos'            => 'left',
	'woocommerce_price_thousand_sep'      => ',',
	'woocommerce_price_decimal_sep'       => '.',
	'woocommerce_price_num_decimals'      => '2',
	'woocommerce_weight_unit'             => 'oz',
	'woocommerce_dimension_unit'          => 'in',
	'woocommerce_calc_taxes'              => 'no',   // decision pending: tax-inclusive vs exclusive (see README).
	'woocommerce_enable_guest_checkout'   => 'yes',
	'woocommerce_enable_checkout_login_reminder' => 'yes',
	'woocommerce_enable_signup_and_login_from_checkout' => 'yes',
	'woocommerce_allowed_countries'       => 'specific',
	'woocommerce_specific_allowed_countries' => array( 'US' ),
	'woocommerce_ship_to_countries'       => 'specific',
	'woocommerce_specific_ship_to_countries' => array( 'US' ),
	'woocommerce_enable_reviews'          => 'yes',
	'woocommerce_enable_review_rating'    => 'yes',
	'woocommerce_review_rating_verification_label' => 'yes',
	'woocommerce_manage_stock'            => 'yes',
	'woocommerce_hide_out_of_stock_items' => 'no',
	'woocommerce_stock_format'            => 'low_amount',
	'woocommerce_notify_low_stock_amount' => '2',
	'woocommerce_thumbnail_cropping'      => '1:1',
	'woocommerce_single_image_width'      => '900',
	'woocommerce_thumbnail_image_width'   => '600',
	'woocommerce_demo_store'              => 'no',
	'woocommerce_coming_soon'             => 'no',
	'woocommerce_task_list_hidden'        => 'yes',
	'woocommerce_onboarding_profile'      => array( 'skipped' => true ),
);
foreach ( $wc_opts as $k => $v ) {
	update_option( $k, $v );
}

// Shipping: one US zone, flat rate $7 + free over $40. Weight tiers from Shopify are in the README for a table-rate plugin.
$zones = WC_Shipping_Zones::get_zones();
$has_us = false;
foreach ( $zones as $z ) {
	if ( 'United States' === $z['zone_name'] ) { $has_us = true; }
}
if ( ! $has_us ) {
	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'United States' );
	$zone->add_location( 'US', 'country' );
	$zone->save();
	$flat_id = $zone->add_shipping_method( 'flat_rate' );
	$free_id = $zone->add_shipping_method( 'free_shipping' );
	update_option( 'woocommerce_flat_rate_' . $flat_id . '_settings', array( 'title' => 'Standard (3–5 business days)', 'tax_status' => 'none', 'cost' => '7.00' ) );
	update_option( 'woocommerce_free_shipping_' . $free_id . '_settings', array( 'title' => 'Free US shipping', 'requires' => 'min_amount', 'min_amount' => '40', 'ignore_discounts' => 'no' ) );
	$log( 'Shipping zone United States: flat $7, free over $40' );
}

/* ---------- 4. Categories, attributes ---------- */
WP_CLI::log( 'Categories and attributes' );
$cat_ids = array();
foreach ( $spec['categories'] as $c ) {
	$term = get_term_by( 'slug', $c['slug'], 'product_cat' );
	if ( ! $term ) {
		$res = wp_insert_term( $c['name'], 'product_cat', array( 'slug' => $c['slug'], 'description' => $c['description'] ) );
		$cat_ids[ $c['slug'] ] = $res['term_id'];
	} else {
		wp_update_term( $term->term_id, 'product_cat', array( 'description' => $c['description'] ) );
		$cat_ids[ $c['slug'] ] = $term->term_id;
	}
}
// Category images: first product card of each category is a fine stand-in.
$attr_ids = array();
foreach ( $spec['attributes'] as $a ) {
	$existing = wc_attribute_taxonomy_id_by_name( $a['slug'] );
	if ( ! $existing ) {
		$existing = wc_create_attribute( array( 'name' => $a['name'], 'slug' => $a['slug'], 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => false ) );
	}
	$attr_ids[ $a['slug'] ] = $existing;
}
delete_transient( 'wc_attribute_taxonomies' );
// Make sure taxonomies exist in this request.
foreach ( $spec['attributes'] as $a ) {
	$tax = 'pa_' . $a['slug'];
	if ( ! taxonomy_exists( $tax ) ) {
		register_taxonomy( $tax, 'product', array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false ) );
	}
}

/* ---------- 5. Media helper ---------- */
require_once ABSPATH . 'wp-admin/includes/image.php';
$upload = wp_upload_dir();
$attach_cache = array();
$attach_local = function ( $relative, $title = '' ) use ( &$attach_cache, $upload, $log ) {
	// $relative is relative to uploads dir, e.g. blu/products/x/01-card.jpg
	if ( isset( $attach_cache[ $relative ] ) ) {
		return $attach_cache[ $relative ];
	}
	$existing = get_posts( array( 'post_type' => 'attachment', 'meta_key' => '_wp_attached_file', 'meta_value' => $relative, 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) );
	if ( $existing ) {
		return $attach_cache[ $relative ] = (int) $existing[0];
	}
	$file = trailingslashit( $upload['basedir'] ) . $relative;
	if ( ! file_exists( $file ) ) {
		$log( "missing image: $relative" );
		return 0;
	}
	$ft = wp_check_filetype( basename( $file ), null );
	$id = wp_insert_attachment( array(
		'guid'           => trailingslashit( $upload['baseurl'] ) . $relative,
		'post_mime_type' => $ft['type'],
		'post_title'     => $title ?: preg_replace( '/\.[^.]+$/', '', basename( $file ) ),
		'post_status'    => 'inherit',
	), $file );
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_attached_file', $relative );
		if ( str_starts_with( (string) $ft['type'], 'image/' ) && 'image/svg+xml' !== $ft['type'] ) {
			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
		}
		return $attach_cache[ $relative ] = (int) $id;
	}
	return 0;
};

/* ---------- 6. Products ---------- */
WP_CLI::log( 'Products' );
foreach ( $spec['products'] as $p ) {
	$dir = $p['_src_dir'] ?? $p['slug'];
	$existing = get_page_by_path( $p['slug'], OBJECT, 'product' );
	$product = $existing ? wc_get_product( $existing->ID ) : ( 'variable' === $p['type'] ? new WC_Product_Variable() : new WC_Product_Simple() );
	if ( $existing && 'variable' === $p['type'] && ! $product instanceof WC_Product_Variable ) {
		$product = new WC_Product_Variable( $existing->ID );
	}
	$product->set_name( $p['name'] );
	$product->set_slug( $p['slug'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_short_description( $p['short'] );
	$product->set_description( $p['description'] );
	$product->set_menu_order( (int) ( $p['menu_order'] ?? 0 ) );
	$product->set_featured( ! empty( $p['featured'] ) );
	$product->set_category_ids( array_map( fn( $s ) => $cat_ids[ $s ], $p['categories'] ) );
	$product->set_reviews_allowed( true );
	if ( ! empty( $p['virtual'] ) ) {
		$product->set_virtual( true );
	}
	if ( ! empty( $p['downloadable'] ) ) {
		$product->set_downloadable( true );
	}
	if ( ! empty( $p['weight_g'] ) ) {
		$product->set_weight( round( $p['weight_g'] / 28.3495, 2 ) ); // store unit is oz
	}
	// images
	$gallery = array();
	foreach ( $p['images'] as $i => $img ) {
		$id = $attach_local( "blu/products/$dir/$img", $p['name'] . ' ' . ( $i + 1 ) );
		if ( $id ) {
			$gallery[] = $id;
		}
	}
	if ( $gallery ) {
		$product->set_image_id( array_shift( $gallery ) );
		$product->set_gallery_image_ids( $gallery );
	}
	// tags
	if ( ! empty( $p['tags'] ) ) {
		$tag_ids = array();
		foreach ( $p['tags'] as $t ) {
			$term = get_term_by( 'slug', $t, 'product_tag' ) ?: get_term( wp_insert_term( $t, 'product_tag', array( 'slug' => $t ) )['term_id'], 'product_tag' );
			$tag_ids[] = $term->term_id;
		}
		$product->set_tag_ids( $tag_ids );
	}

	if ( 'simple' === $p['type'] ) {
		$product->set_regular_price( $p['regular_price'] );
		if ( ! empty( $p['sale_price'] ) ) {
			$product->set_sale_price( $p['sale_price'] );
		}
		if ( null === $p['stock'] ) {
			$product->set_manage_stock( false );
			$product->set_stock_status( 'instock' );
		} else {
			$product->set_manage_stock( true );
			$product->set_stock_quantity( (int) $p['stock'] );
		}
		$pid = $product->save();
	} else {
		$tax   = 'pa_' . $p['attribute'];
		$terms = array();
		foreach ( $p['variations'] as $v ) {
			$term = get_term_by( 'name', $v['option'], $tax );
			if ( ! $term ) {
				$r = wp_insert_term( $v['option'], $tax );
				$term = get_term( $r['term_id'], $tax );
			}
			$terms[] = $term->term_id;
		}
		$attr = new WC_Product_Attribute();
		$attr->set_id( $attr_ids[ $p['attribute'] ] );
		$attr->set_name( $tax );
		$attr->set_options( $terms );
		$attr->set_position( 0 );
		$attr->set_visible( true );
		$attr->set_variation( true );
		$product->set_attributes( array( $attr ) );
		$product->set_manage_stock( false );
		$pid = $product->save();

		// variations
		$existing_vars = array();
		foreach ( $product->get_children() as $cid ) {
			$cv = wc_get_product( $cid );
			if ( $cv ) {
				$existing_vars[ $cv->get_attribute( $tax ) ] = $cv;
			}
		}
		foreach ( $p['variations'] as $v ) {
			$var = $existing_vars[ $v['option'] ] ?? new WC_Product_Variation();
			$var->set_parent_id( $pid );
			$var->set_attributes( array( $tax => sanitize_title( $v['option'] ) ) );
			$var->set_regular_price( $v['regular_price'] );
			$var->set_sale_price( $v['sale_price'] ?? '' );
			$var->set_manage_stock( true );
			$var->set_stock_quantity( (int) $v['stock'] );
			$var->set_status( 'publish' );
			if ( ! empty( $p['weight_g'] ) ) {
				$var->set_weight( round( $p['weight_g'] / 28.3495, 2 ) );
			}
			if ( ! empty( $v['image'] ) ) {
				$id = $attach_local( "blu/products/$dir/{$v['image']}", $p['name'] . ' ' . $v['option'] );
				if ( $id ) {
					$var->set_image_id( $id );
				}
			}
			$var->save();
		}
		WC_Product_Variable::sync( $pid );
	}
	foreach ( ( $p['meta'] ?? array() ) as $k => $v ) {
		if ( '' !== $v ) {
			update_post_meta( $pid, $k, $v );
		}
	}
	$log( ( $existing ? 'updated ' : 'created ' ) . $p['slug'] );
}
wc_delete_product_transients();

/* ---------- 7. Coupons ---------- */
WP_CLI::log( 'Coupons' );
foreach ( $spec['coupons'] as $c ) {
	$id = wc_get_coupon_id_by_code( $c['code'] );
	$coupon = new WC_Coupon( $id ?: 0 );
	$coupon->set_code( $c['code'] );
	$coupon->set_discount_type( $c['type'] );
	$coupon->set_amount( $c['amount'] );
	$coupon->set_description( $c['description'] );
	if ( ! empty( $c['minimum_amount'] ) ) { $coupon->set_minimum_amount( $c['minimum_amount'] ); }
	if ( ! empty( $c['usage_limit_per_user'] ) ) { $coupon->set_usage_limit_per_user( (int) $c['usage_limit_per_user'] ); }
	if ( ! empty( $c['product_categories'] ) ) { $coupon->set_product_categories( array_map( fn( $s ) => $cat_ids[ $s ], $c['product_categories'] ) ); }
	if ( ! empty( $c['expiry_days'] ) ) { $coupon->set_date_expires( time() + DAY_IN_SECONDS * (int) $c['expiry_days'] ); }
	$coupon->save();
	$log( ( $id ? 'updated ' : 'created ' ) . $c['code'] );
}

/* ---------- 8. Pages ---------- */
WP_CLI::log( 'Pages' );
if ( class_exists( 'WC_Install' ) ) {
	WC_Install::create_pages();
}
$pattern_content = function ( $slug ) {
	$p = WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
	return $p ? $p['content'] : '';
};
$pages = array(
	'home' => array( 'title' => 'Home', 'template' => 'page-home', 'content' => $pattern_content( 'blu/home' ) ),
	'our-family' => array( 'title' => 'Our family', 'template' => 'page-story', 'content' => $pattern_content( 'blu/story' ) ),
	'journal' => array( 'title' => 'Journal', 'content' => '' ),
	'find-your-scent' => array( 'title' => 'Find your scent', 'content' => "<!-- wp:paragraph {\"className\":\"is-style-eyebrow\",\"align\":\"center\"} -->\n<p class=\"has-text-align-center is-style-eyebrow\">Four questions</p>\n<!-- /wp:paragraph -->\n<!-- wp:paragraph {\"align\":\"center\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Sensitive skin, scent or no scent, stick or jar. Thirty seconds and we will point you at the right one.</p>\n<!-- /wp:paragraph -->\n<!-- wp:shortcode -->\n[blu_scent_quiz]\n<!-- /wp:shortcode -->" ),
	'faq' => array( 'title' => 'FAQ', 'content' => file_get_contents( $repo . '/import/pages/faq.html' ) ),
	'shipping-returns' => array( 'title' => 'Shipping & returns', 'content' => file_get_contents( $repo . '/import/pages/shipping-returns.html' ) ),
	'contact' => array( 'title' => 'Contact', 'content' => file_get_contents( $repo . '/import/pages/contact.html' ) ),
	'privacy-policy' => array( 'title' => 'Privacy policy', 'content' => file_get_contents( $repo . '/import/pages/privacy-policy.html' ) ),
	'terms' => array( 'title' => 'Terms of service', 'content' => file_get_contents( $repo . '/import/pages/terms.html' ) ),
	'do-not-sell' => array( 'title' => 'Do not sell or share my personal information', 'content' => file_get_contents( $repo . '/import/pages/do-not-sell.html' ) ),
	'gift-card' => array( 'title' => 'Gift cards', 'content' => "<!-- wp:paragraph -->\n<p>Gift cards are coming back with the new shop. Until then, email us and we will set one up by hand.</p>\n<!-- /wp:paragraph -->" ),
);
$page_ids = array();
foreach ( $pages as $slug => $pg ) {
	$existing = get_page_by_path( $slug );
	$args = array( 'post_title' => $pg['title'], 'post_name' => $slug, 'post_type' => 'page', 'post_status' => 'publish', 'post_content' => $pg['content'], 'comment_status' => 'closed' );
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$page_ids[ $slug ] = wp_update_post( $args );
	} else {
		$page_ids[ $slug ] = wp_insert_post( $args );
	}
	if ( ! empty( $pg['template'] ) ) {
		update_post_meta( $page_ids[ $slug ], '_wp_page_template', $pg['template'] );
	}
	$log( ( $existing ? 'updated ' : 'created ' ) . $slug );
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $page_ids['home'] );
update_option( 'page_for_posts', $page_ids['journal'] );
update_option( 'wp_page_for_privacy_policy', $page_ids['privacy-policy'] );
update_option( 'woocommerce_terms_page_id', $page_ids['terms'] );

/* ---------- 9. Logo + icon ---------- */
WP_CLI::log( 'Logo' );
$logo_id = $attach_local( 'blu/logo/blu-naturals-horizontal.png', 'Blu Naturals logo' );
if ( $logo_id ) {
	set_theme_mod( 'custom_logo', $logo_id );
	update_option( 'site_logo', $logo_id );
}
$icon_id = $attach_local( 'blu/logo/blu-mark-circle.png', 'Blu Naturals icon' );
if ( $icon_id ) {
	update_option( 'site_icon', $icon_id );
}

/* ---------- 10. Blu Core defaults ---------- */
foreach ( array( 'blu_free_shipping_min' => '40', 'blu_welcome_code' => 'WELCOME10', 'blu_three_code' => 'THREE', 'blu_default_maker' => 'The Blu Naturals family', 'blu_instagram_url' => 'https://instagram.com/blunaturals' ) as $k => $v ) {
	if ( false === get_option( $k ) ) {
		update_option( $k, $v );
	}
}

/* ---------- 11. Journal drafts ---------- */
WP_CLI::log( 'Journal drafts' );
$blog = json_decode( file_get_contents( $repo . '/import/blog/index.json' ), true );
$cat  = get_term_by( 'slug', 'journal', 'category' ) ?: get_term( wp_insert_term( 'Journal', 'category', array( 'slug' => 'journal' ) )['term_id'], 'category' );
foreach ( $blog as $post ) {
	$existing = get_page_by_path( $post['slug'], OBJECT, 'post' );
	$body = file_get_contents( $repo . '/import/blog/' . $post['file'] );
	$args = array( 'post_title' => $post['title'], 'post_name' => $post['slug'], 'post_type' => 'post', 'post_status' => 'draft', 'post_excerpt' => $post['excerpt'], 'post_content' => $body, 'post_category' => array( $cat->term_id ), 'comment_status' => 'closed' );
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$id = wp_update_post( $args );
	} else {
		$id = wp_insert_post( $args );
	}
	update_post_meta( $id, 'rank_math_title', $post['seo_title'] );
	update_post_meta( $id, 'rank_math_description', $post['meta_description'] );
	update_post_meta( $id, 'rank_math_focus_keyword', $post['keyword'] );
	update_post_meta( $id, '_blu_target_query', $post['keyword'] );
	if ( ! empty( $post['image'] ) ) {
		$tid = $attach_local( $post['image'], $post['title'] );
		if ( $tid ) {
			set_post_thumbnail( $id, $tid );
		}
	}
	$log( ( $existing ? 'updated ' : 'created ' ) . $post['slug'] );
}

/* ---------- 12. Remove WordPress sample content ---------- */
foreach ( array( get_page_by_path( 'hello-world', OBJECT, 'post' ), get_page_by_path( 'sample-page' ) ) as $sample ) {
	if ( $sample ) {
		wp_delete_post( $sample->ID, true );
	}
}
if ( get_comment( 1 ) ) {
	wp_delete_comment( 1, true );
}

/* ---------- 13. Finish ---------- */
flush_rewrite_rules();
wc_delete_product_transients();
WP_CLI::success( 'Provisioned. Admin → Appearance → Editor to tune; Settings → Blu Naturals for store options.' );
