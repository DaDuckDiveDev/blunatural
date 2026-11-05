<?php
/**
 * Product fields: who made it, batch week, care note toggle.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'blu_product_meta', __( 'Blu Naturals: who made this', 'blu-core' ), 'blu_product_meta_box', 'product', 'side', 'default' );
} );

function blu_product_meta_box( $post ) {
	wp_nonce_field( 'blu_product_meta', 'blu_product_meta_nonce' );
	$maker = get_post_meta( $post->ID, '_blu_made_by', true );
	$photo = get_post_meta( $post->ID, '_blu_maker_photo', true );
	$batch = get_post_meta( $post->ID, '_blu_batch_week', true );
	$care  = get_post_meta( $post->ID, '_blu_care_note', true );
	?>
	<p><label for="blu_made_by"><strong><?php esc_html_e( 'Made by', 'blu-core' ); ?></strong></label><br>
	<input type="text" class="widefat" id="blu_made_by" name="blu_made_by" value="<?php echo esc_attr( $maker ); ?>" placeholder="<?php echo esc_attr( blu_core_get( 'blu_default_maker' ) ); ?>"></p>
	<p><label for="blu_maker_photo"><strong><?php esc_html_e( 'Maker photo (attachment ID)', 'blu-core' ); ?></strong></label><br>
	<input type="number" class="widefat" id="blu_maker_photo" name="blu_maker_photo" value="<?php echo esc_attr( $photo ); ?>"></p>
	<p><label for="blu_batch_week"><strong><?php esc_html_e( 'Current batch', 'blu-core' ); ?></strong></label><br>
	<input type="text" class="widefat" id="blu_batch_week" name="blu_batch_week" value="<?php echo esc_attr( $batch ); ?>" placeholder="<?php esc_attr_e( 'e.g. poured the week of Nov 3', 'blu-core' ); ?>"></p>
	<p><label for="blu_care_note"><strong><?php esc_html_e( 'Care note', 'blu-core' ); ?></strong></label><br>
	<select class="widefat" id="blu_care_note" name="blu_care_note">
		<option value="" <?php selected( $care, '' ); ?>><?php esc_html_e( 'Auto (deodorant categories)', 'blu-core' ); ?></option>
		<option value="heat" <?php selected( $care, 'heat' ); ?>><?php esc_html_e( 'Softens in heat note', 'blu-core' ); ?></option>
		<option value="none" <?php selected( $care, 'none' ); ?>><?php esc_html_e( 'None', 'blu-core' ); ?></option>
	</select></p>
	<?php
}

add_action( 'save_post_product', function ( $post_id ) {
	if ( ! isset( $_POST['blu_product_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['blu_product_meta_nonce'] ) ), 'blu_product_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array( 'blu_made_by' => '_blu_made_by', 'blu_maker_photo' => '_blu_maker_photo', 'blu_batch_week' => '_blu_batch_week', 'blu_care_note' => '_blu_care_note' ) as $field => $meta ) {
		$val = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		if ( '' === $val ) {
			delete_post_meta( $post_id, $meta );
		} else {
			update_post_meta( $post_id, $meta, $val );
		}
	}
} );

/**
 * Is this product a deodorant (for the care note and buy-3 hint)?
 */
function blu_is_deodorant( $product_id = 0 ) {
	$product_id = $product_id ?: get_the_ID();
	return has_term( array( 'deodorant', 'blakbird' ), 'product_cat', $product_id );
}
