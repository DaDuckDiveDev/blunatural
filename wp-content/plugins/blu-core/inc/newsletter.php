<?php
/**
 * Newsletter capture: [blu_newsletter], AJAX handler, subscriber CPT, CSV export.
 * Subscribers are stored on the site so nothing is lost before an email tool is connected.
 * Hook `blu_subscriber_added( $email, $source )` to push to Klaviyo/MailPoet.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'blu_subscriber', array(
		'labels'       => array( 'name' => __( 'Subscribers', 'blu-core' ), 'singular_name' => __( 'Subscriber', 'blu-core' ) ),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => 'edit.php?post_type=product',
		'supports'     => array( 'title' ),
		'capability_type' => 'post',
		'map_meta_cap' => true,
	) );
} );

add_shortcode( 'blu_newsletter', function ( $atts ) {
	$a   = shortcode_atts( array( 'source' => 'site', 'layout' => 'stack', 'button' => __( 'Join the list', 'blu-core' ) ), $atts );
	$id  = 'blu-nl-' . wp_unique_id();
	$cls = 'blu-nl' . ( 'inline' === $a['layout'] ? ' blu-nl--inline' : '' );
	return '<form class="' . esc_attr( $cls ) . '" data-source="' . esc_attr( $a['source'] ) . '" action="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '" method="post" novalidate>'
		. '<input type="hidden" name="action" value="blu_subscribe">'
		. '<input type="text" name="website" class="blu-nl__hp" tabindex="-1" autocomplete="off" aria-hidden="true">'
		. '<label class="screen-reader-text" for="' . esc_attr( $id ) . '">' . esc_html__( 'Email address', 'blu-core' ) . '</label>'
		. '<input type="email" id="' . esc_attr( $id ) . '" name="email" placeholder="' . esc_attr__( 'you@example.com', 'blu-core' ) . '" required>'
		. '<button type="submit">' . esc_html( $a['button'] ) . '</button>'
		. '</form><p class="blu-nl__msg" aria-live="polite"></p>';
} );

function blu_handle_subscribe() {
	check_ajax_referer( 'blu_newsletter', 'nonce' );
	if ( ! empty( $_POST['website'] ) ) { // honeypot
		wp_send_json_success( array( 'message' => __( 'Thanks!', 'blu-core' ) ) );
	}
	$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$source = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : 'site';
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'That email does not look right.', 'blu-core' ) ) );
	}
	$existing = get_posts( array( 'post_type' => 'blu_subscriber', 'title' => $email, 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) );
	if ( ! $existing ) {
		$id = wp_insert_post( array( 'post_type' => 'blu_subscriber', 'post_title' => $email, 'post_status' => 'publish' ) );
		if ( $id ) {
			update_post_meta( $id, '_blu_source', $source );
			update_post_meta( $id, '_blu_ip_hash', wp_hash( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );
			do_action( 'blu_subscriber_added', $email, $source );
		}
	}
	$code = blu_core_get( 'blu_welcome_code' );
	$msg  = $code
		? sprintf( __( 'You are on the list. Use %s for 10%% off your first order.', 'blu-core' ), '<strong>' . esc_html( $code ) . '</strong>' )
		: __( 'You are on the list. First letter goes out the first Sunday of the month.', 'blu-core' );
	wp_send_json_success( array( 'message' => $msg ) );
}
add_action( 'wp_ajax_blu_subscribe', 'blu_handle_subscribe' );
add_action( 'wp_ajax_nopriv_blu_subscribe', 'blu_handle_subscribe' );

add_action( 'admin_post_blu_export_subscribers', function () {
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'blu_export' ) ) {
		wp_die( 'Not allowed' );
	}
	$subs = get_posts( array( 'post_type' => 'blu_subscriber', 'posts_per_page' => -1, 'post_status' => 'publish' ) );
	header( 'Content-Type: text/csv' );
	header( 'Content-Disposition: attachment; filename=blu-subscribers.csv' );
	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'email', 'source', 'date' ) );
	foreach ( $subs as $s ) {
		fputcsv( $out, array( $s->post_title, get_post_meta( $s->ID, '_blu_source', true ), $s->post_date ) );
	}
	fclose( $out );
	exit;
} );

/**
 * Also capture marketing opt-in at checkout.
 */
add_action( 'woocommerce_checkout_order_processed', function ( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order && $order->get_billing_email() ) {
		do_action( 'blu_customer_ordered', $order->get_billing_email(), $order_id );
	}
} );
