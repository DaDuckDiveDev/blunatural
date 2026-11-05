<?php
/**
 * [blu_contact subject="..."] — a plain contact form that emails the store address. No plugin needed.
 */

defined( 'ABSPATH' ) || exit;

add_shortcode( 'blu_contact', function ( $atts ) {
	$a = shortcode_atts( array( 'subject' => __( 'Message from blunaturals.com', 'blu-core' ) ), $atts );
	$sent = isset( $_GET['blu_sent'] ) ? sanitize_key( wp_unslash( $_GET['blu_sent'] ) ) : '';
	$out  = '';
	if ( 'ok' === $sent ) {
		$out .= '<p class="blu-nl__msg is-ok">' . esc_html__( 'Sent. We will reply to the address you gave.', 'blu-core' ) . '</p>';
	} elseif ( 'err' === $sent ) {
		$out .= '<p class="blu-nl__msg is-err">' . esc_html__( 'Something was missing. Please check the fields and try again.', 'blu-core' ) . '</p>';
	}
	$out .= '<form class="blu-contact" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
		. '<input type="hidden" name="action" value="blu_contact">'
		. wp_nonce_field( 'blu_contact', '_blu_nonce', true, false )
		. '<input type="hidden" name="subject" value="' . esc_attr( $a['subject'] ) . '">'
		. '<input type="hidden" name="return" value="' . esc_url( get_permalink() ) . '">'
		. '<input type="text" name="website" class="blu-nl__hp" tabindex="-1" autocomplete="off" aria-hidden="true">'
		. '<p><label for="blu_c_name">' . esc_html__( 'Name', 'blu-core' ) . '</label><br><input id="blu_c_name" type="text" name="name" required></p>'
		. '<p><label for="blu_c_email">' . esc_html__( 'Email', 'blu-core' ) . '</label><br><input id="blu_c_email" type="email" name="email" required></p>'
		. '<p><label for="blu_c_order">' . esc_html__( 'Order number (if it is about an order)', 'blu-core' ) . '</label><br><input id="blu_c_order" type="text" name="order"></p>'
		. '<p><label for="blu_c_msg">' . esc_html__( 'Message', 'blu-core' ) . '</label><br><textarea id="blu_c_msg" name="message" rows="6" required></textarea></p>'
		. '<p><button type="submit" class="wp-element-button">' . esc_html__( 'Send', 'blu-core' ) . '</button></p>'
		. '</form>';
	return $out;
} );

function blu_handle_contact() {
	$return = isset( $_POST['return'] ) ? esc_url_raw( wp_unslash( $_POST['return'] ) ) : home_url( '/contact/' );
	if ( ! isset( $_POST['_blu_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_blu_nonce'] ) ), 'blu_contact' ) || ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'blu_sent', 'err', $return ) );
		exit;
	}
	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$order = sanitize_text_field( wp_unslash( $_POST['order'] ?? '' ) );
	$msg   = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
	$subj  = sanitize_text_field( wp_unslash( $_POST['subject'] ?? 'Message' ) );
	if ( ! $name || ! is_email( $email ) || ! $msg ) {
		wp_safe_redirect( add_query_arg( 'blu_sent', 'err', $return ) );
		exit;
	}
	$to   = get_option( 'woocommerce_email_from_address' ) ?: get_option( 'admin_email' );
	$body = "From: $name <$email>\n" . ( $order ? "Order: $order\n" : '' ) . "\n$msg\n";
	wp_mail( $to, "[Blu Naturals] $subj", $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
	wp_safe_redirect( add_query_arg( 'blu_sent', 'ok', $return ) );
	exit;
}
add_action( 'admin_post_blu_contact', 'blu_handle_contact' );
add_action( 'admin_post_nopriv_blu_contact', 'blu_handle_contact' );
