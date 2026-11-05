<?php
/**
 * Plugin Name: Blu Core
 * Plugin URI: https://blunaturals.com
 * Description: Store logic for Blu Naturals that should survive a theme change: product maker fields, care notes, newsletter capture, scent quiz, free-shipping bar, Shopify URL redirects, order email notes.
 * Version: 1.0.0
 * Author: Kaiel Systems
 * Author URI: https://kaielsystems.com
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 * Text Domain: blu-core
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'BLU_CORE_VERSION', '1.0.0' );
define( 'BLU_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'BLU_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once BLU_CORE_DIR . 'inc/settings.php';
require_once BLU_CORE_DIR . 'inc/product-meta.php';
require_once BLU_CORE_DIR . 'inc/shortcodes.php';
require_once BLU_CORE_DIR . 'inc/newsletter.php';
require_once BLU_CORE_DIR . 'inc/quiz.php';
require_once BLU_CORE_DIR . 'inc/redirects.php';
require_once BLU_CORE_DIR . 'inc/emails.php';
require_once BLU_CORE_DIR . 'inc/contact.php';

/**
 * Allow SVG uploads for administrators only (logos live in the media library).
 */
add_filter( 'upload_mimes', function ( $mimes ) {
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['svg'] = 'image/svg+xml';
	}
	return $mimes;
} );
add_filter( 'wp_check_filetype_and_ext', function ( $data, $file, $filename, $mimes ) {
	if ( current_user_can( 'manage_options' ) && str_ends_with( strtolower( $filename ), '.svg' ) ) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}
	return $data;
}, 10, 4 );

/**
 * Front-end assets for the quiz and newsletter.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_register_script( 'blu-core', BLU_CORE_URL . 'assets/blu-core.js', array(), BLU_CORE_VERSION, array( 'strategy' => 'defer' ) );
	wp_localize_script( 'blu-core', 'bluCore', array(
		'ajax'  => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'blu_newsletter' ),
	) );
	wp_enqueue_script( 'blu-core' );
} );
