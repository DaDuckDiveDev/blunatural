<?php
/**
 * Order email touches: the fridge tip on deodorant orders, a signed footer, review ask hook.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'woocommerce_email_after_order_table', function ( $order, $sent_to_admin, $plain_text ) {
	if ( $sent_to_admin || ! $order instanceof WC_Order ) {
		return;
	}
	$has_deo = false;
	foreach ( $order->get_items() as $item ) {
		$pid = $item->get_product_id();
		if ( $pid && has_term( array( 'deodorant', 'blakbird' ), 'product_cat', $pid ) ) {
			$has_deo = true;
			break;
		}
	}
	if ( ! $has_deo ) {
		return;
	}
	$tip = __( 'One thing before it arrives: natural deodorant is mostly coconut oil, so a hot mailbox can soften it. If the stick feels soft, stand it upright in the fridge for an hour before the first use. Nothing in it spoils.', 'blu-core' );
	if ( $plain_text ) {
		echo "\n" . esc_html( $tip ) . "\n";
	} else {
		echo '<p style="margin:16px 0;padding:12px 16px;background:#EAF3F6;border-left:3px solid #8FC7D6;border-radius:0 8px 8px 0;font-size:14px;line-height:1.5">' . esc_html( $tip ) . '</p>';
	}
}, 10, 3 );

add_filter( 'woocommerce_email_footer_text', function ( $text ) {
	return __( 'Blu Naturals · Small-batch natural care, made by a family in Honolulu, Hawaiʻi. Reply to this email and a person answers.', 'blu-core' );
} );

/**
 * Hook point for the day-20 review request and day-50 replenishment flows (Klaviyo/MailPoet).
 * Fires when an order is completed with the product categories ordered, so the email tool can branch.
 */
add_action( 'woocommerce_order_status_completed', function ( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	$cats = array();
	foreach ( $order->get_items() as $item ) {
		$terms = get_the_terms( $item->get_product_id(), 'product_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$cats[ $t->slug ] = true;
			}
		}
	}
	do_action( 'blu_order_completed', $order->get_billing_email(), array_keys( $cats ), $order_id );
} );
