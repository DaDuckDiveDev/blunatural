<?php
/**
 * Product-page strips and the cart shipping bar.
 */

defined( 'ABSPATH' ) || exit;

/**
 * [blu_made_by] — maker name, photo and batch week.
 */
add_shortcode( 'blu_made_by', function () {
	if ( ! is_singular( 'product' ) ) {
		return '';
	}
	$id    = get_the_ID();
	$maker = get_post_meta( $id, '_blu_made_by', true ) ?: blu_core_get( 'blu_default_maker' );
	$photo = get_post_meta( $id, '_blu_maker_photo', true ) ?: blu_core_get( 'blu_default_maker_photo' );
	$batch = get_post_meta( $id, '_blu_batch_week', true );
	$img   = $photo ? wp_get_attachment_image( (int) $photo, 'thumbnail', false, array( 'alt' => '' ) ) : '<img src="' . esc_url( content_url( '/uploads/blu/logo/blu-mark-circle.png' ) ) . '" alt="">';
	$line  = $batch ? sprintf( __( 'This batch: %s', 'blu-core' ), $batch ) : __( 'Poured by hand in Honolulu, Hawaiʻi', 'blu-core' );
	return '<div class="blu-madeby">' . $img . '<div><strong>' . sprintf( esc_html__( 'Made by %s', 'blu-core' ), esc_html( $maker ) ) . '</strong><span>' . esc_html( $line ) . '</span></div></div>';
} );

/**
 * [blu_care_note] — the softens-in-heat note on deodorant pages.
 */
add_shortcode( 'blu_care_note', function () {
	if ( ! is_singular( 'product' ) ) {
		return '';
	}
	$mode = get_post_meta( get_the_ID(), '_blu_care_note', true );
	if ( 'none' === $mode || ( '' === $mode && ! blu_is_deodorant() ) ) {
		return '';
	}
	return '<div class="blu-care"><strong>' . esc_html__( 'Arrives soft? That is the coconut oil.', 'blu-core' ) . '</strong>'
		. esc_html__( 'Natural deodorant reacts to temperature. If the stick is soft after a hot mailbox, stand it upright in the fridge for an hour before the first use. Keep it at room temperature after that.', 'blu-core' ) . '</div>';
} );

/**
 * [blu_trust_row] — short proof points under the buy button.
 */
add_shortcode( 'blu_trust_row', function () {
	$min = (float) blu_core_get( 'blu_free_shipping_min' );
	$items = array(
		__( 'Aluminum free', 'blu-core' ),
		__( 'Small batch, Honolulu', 'blu-core' ),
		$min > 0 ? sprintf( __( 'Free US shipping over $%s', 'blu-core' ), number_format( $min, 0 ) ) : __( 'Ships from Hawaiʻi', 'blu-core' ),
		__( 'Two-week swap promise', 'blu-core' ),
	);
	$out = '<ul class="blu-trustrow">';
	foreach ( $items as $i ) {
		$out .= '<li>' . esc_html( $i ) . '</li>';
	}
	return $out . '</ul>';
} );

/**
 * [blu_subscribe_hint] — the buy-3 offer on deodorant pages.
 */
add_shortcode( 'blu_subscribe_hint', function () {
	if ( ! is_singular( 'product' ) || ! blu_is_deodorant() ) {
		return '';
	}
	$code = blu_core_get( 'blu_three_code' );
	if ( ! $code ) {
		return '';
	}
	return '<p class="blu-subhint"><strong>' . esc_html__( 'Six months of fresh:', 'blu-core' ) . '</strong> '
		. sprintf( esc_html__( 'any three deodorants, 10%% off with code %s at checkout. Mix scents.', 'blu-core' ), '<code>' . esc_html( $code ) . '</code>' ) . '</p>';
} );

/**
 * [blu_shipping_bar] — free-shipping progress (server rendered at page load).
 */
add_shortcode( 'blu_shipping_bar', function () {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}
	$min = (float) blu_core_get( 'blu_free_shipping_min' );
	if ( $min <= 0 ) {
		return '';
	}
	$sub = (float) WC()->cart->get_subtotal();
	$pct = min( 100, round( $sub / $min * 100 ) );
	if ( $sub >= $min ) {
		$msg = __( 'You have free US shipping on this order.', 'blu-core' );
	} else {
		$msg = sprintf( __( 'Add %s more for free US shipping.', 'blu-core' ), wc_price( $min - $sub ) );
	}
	return '<div class="blu-shipbar">' . wp_kses_post( $msg ) . '<div class="blu-shipbar__track"><div class="blu-shipbar__fill" style="width:' . (int) $pct . '%"></div></div></div>';
} );
