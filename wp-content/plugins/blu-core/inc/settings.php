<?php
/**
 * Settings → Blu Naturals.
 */

defined( 'ABSPATH' ) || exit;

function blu_core_settings_fields() {
	return array(
		'blu_free_shipping_min' => array( 'label' => __( 'Free shipping threshold (USD)', 'blu-core' ), 'type' => 'number', 'default' => '40', 'help' => __( 'Used by the cart progress bar. Must match the Free Shipping method minimum in WooCommerce → Shipping.', 'blu-core' ) ),
		'blu_welcome_code'      => array( 'label' => __( 'Welcome coupon code', 'blu-core' ), 'type' => 'text', 'default' => 'WELCOME10', 'help' => __( 'Shown after a successful newsletter sign-up. Create the coupon in Marketing → Coupons.', 'blu-core' ) ),
		'blu_three_code'        => array( 'label' => __( 'Buy-3 coupon code', 'blu-core' ), 'type' => 'text', 'default' => 'THREE', 'help' => __( 'Shown on deodorant product pages.', 'blu-core' ) ),
		'blu_instagram_url'     => array( 'label' => __( 'Instagram URL', 'blu-core' ), 'type' => 'url', 'default' => 'https://instagram.com/blunaturals', 'help' => '' ),
		'blu_default_maker'     => array( 'label' => __( 'Default maker name', 'blu-core' ), 'type' => 'text', 'default' => 'The Blu Naturals family', 'help' => __( 'Shown on product pages when a product has no maker set.', 'blu-core' ) ),
		'blu_default_maker_photo' => array( 'label' => __( 'Default maker photo (attachment ID)', 'blu-core' ), 'type' => 'number', 'default' => '', 'help' => __( 'Upload a photo in Media and paste its ID here.', 'blu-core' ) ),
		'blu_klaviyo_list'      => array( 'label' => __( 'Klaviyo list ID (optional)', 'blu-core' ), 'type' => 'text', 'default' => '', 'help' => __( 'When set together with the Klaviyo plugin, new subscribers are pushed to this list via the blu_subscriber_added hook.', 'blu-core' ) ),
	);
}

function blu_core_get( $key ) {
	$fields = blu_core_settings_fields();
	$default = isset( $fields[ $key ] ) ? $fields[ $key ]['default'] : '';
	return get_option( $key, $default );
}

add_action( 'admin_menu', function () {
	add_options_page( __( 'Blu Naturals', 'blu-core' ), __( 'Blu Naturals', 'blu-core' ), 'manage_options', 'blu-core', 'blu_core_settings_page' );
} );

add_action( 'admin_init', function () {
	foreach ( blu_core_settings_fields() as $key => $f ) {
		register_setting( 'blu_core', $key, array( 'sanitize_callback' => 'sanitize_text_field' ) );
	}
} );

function blu_core_settings_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Blu Naturals settings', 'blu-core' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'blu_core' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( blu_core_settings_fields() as $key => $f ) : ?>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $f['label'] ); ?></label></th>
					<td>
						<input name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" type="<?php echo esc_attr( $f['type'] ); ?>" class="regular-text" value="<?php echo esc_attr( blu_core_get( $key ) ); ?>">
						<?php if ( $f['help'] ) : ?><p class="description"><?php echo esc_html( $f['help'] ); ?></p><?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
		<h2><?php esc_html_e( 'Newsletter subscribers', 'blu-core' ); ?></h2>
		<p><?php
			$count = wp_count_posts( 'blu_subscriber' );
			printf( esc_html__( '%d subscribers captured on the site. Export as CSV to import into Klaviyo.', 'blu-core' ), (int) ( $count->publish ?? 0 ) );
		?></p>
		<p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=blu_export_subscribers' ), 'blu_export' ) ); ?>"><?php esc_html_e( 'Export subscribers (CSV)', 'blu-core' ); ?></a></p>
	</div>
	<?php
}
