<?php
/**
 * [blu_scent_quiz] — four questions, one recommendation. Products are looked up by slug so the
 * quiz keeps working after the client renames a product (only the slug has to stay).
 */

defined( 'ABSPATH' ) || exit;

function blu_quiz_products() {
	$map = array(
		'original'   => array( 'slug' => 'all-natural-deodorant', 'title' => 'The Original', 'why' => 'Tea tree, eucalyptus, peppermint and rosemary. The one most people reorder.' ),
		'cedar'      => array( 'slug' => 'all-natural-deodorant', 'title' => 'Cedarwood Lavender', 'why' => 'Softer, woodier scent in the same formula. Pick the scent on the product page.' ),
		'bsf'        => array( 'slug' => 'all-natural-deodorant-baking-soda-free', 'title' => 'Baking Soda Free', 'why' => 'Same job without baking soda, for anyone who gets a rash from the Original.' ),
		'eofree'     => array( 'slug' => 'essential-oil-free-deodorant', 'title' => 'Essential Oil Free', 'why' => 'Scent comes from herbs infused into the coconut oil, so it is light and skin-friendly.' ),
		'unscented'  => array( 'slug' => 'unscented-all-natural-deodorant', 'title' => 'Unscented', 'why' => 'No essential oils, no fragrance, nothing to react to.' ),
		'blakbird'   => array( 'slug' => 'blakbird-natural-deodorant-for-men', 'title' => 'Blakbird No.1', 'why' => 'Cedarwood, sage, tea tree and vanilla. Made for men, bought by everyone.' ),
		'blakbird3'  => array( 'slug' => 'blakbird-natural-deodorant-for-men', 'title' => 'Blakbird No.3', 'why' => 'Sandalwood and local vanilla bean infusion, no essential oils, no baking soda.' ),
		'jar'        => array( 'slug' => 'all-natural-deodorant-jar', 'title' => 'The Deodorant Jar', 'why' => 'The Original formula in a glass jar: less packaging, apply with fingers or the spatula.' ),
	);
	foreach ( $map as $k => &$p ) {
		$post = get_page_by_path( $p['slug'], OBJECT, 'product' );
		$p['url'] = $post ? get_permalink( $post ) : home_url( '/shop/' );
		$p['img'] = $post && has_post_thumbnail( $post ) ? get_the_post_thumbnail_url( $post, 'medium' ) : '';
	}
	return $map;
}

add_shortcode( 'blu_scent_quiz', function () {
	$products = blu_quiz_products();
	$questions = array(
		array( 'q' => __( 'Has a natural deodorant ever given you a rash or itch?', 'blu-core' ), 'opts' => array( 'no' => __( 'No, never', 'blu-core' ), 'yes' => __( 'Yes, or my skin is sensitive', 'blu-core' ), 'unsure' => __( 'Not sure, first time trying one', 'blu-core' ) ) ),
		array( 'q' => __( 'How do you feel about scent?', 'blu-core' ), 'opts' => array( 'fresh' => __( 'Fresh and minty', 'blu-core' ), 'warm' => __( 'Warm and woody', 'blu-core' ), 'light' => __( 'Barely there', 'blu-core' ), 'none' => __( 'None at all', 'blu-core' ) ) ),
		array( 'q' => __( 'Essential oils?', 'blu-core' ), 'opts' => array( 'fine' => __( 'Fine by me', 'blu-core' ), 'avoid' => __( 'I avoid them', 'blu-core' ) ) ),
		array( 'q' => __( 'Stick or jar?', 'blu-core' ), 'opts' => array( 'stick' => __( 'Stick, please', 'blu-core' ), 'jar' => __( 'Jar, less plastic', 'blu-core' ) ) ),
	);
	ob_start();
	?>
	<div class="blu-quiz" data-products="<?php echo esc_attr( wp_json_encode( $products ) ); ?>">
		<?php foreach ( $questions as $i => $q ) : ?>
		<div class="blu-quiz__q<?php echo 0 === $i ? ' is-active' : ''; ?>" data-step="<?php echo (int) $i; ?>">
			<p class="blu-quiz__progress"><?php printf( esc_html__( 'Question %1$d of %2$d', 'blu-core' ), $i + 1, count( $questions ) ); ?></p>
			<h3><?php echo esc_html( $q['q'] ); ?></h3>
			<div class="blu-quiz__opts">
				<?php foreach ( $q['opts'] as $val => $label ) : ?>
				<button type="button" data-q="<?php echo (int) $i; ?>" data-v="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endforeach; ?>
		<div class="blu-quiz__result" aria-live="polite">
			<p class="blu-quiz__progress"><?php esc_html_e( 'Our pick for you', 'blu-core' ); ?></p>
			<h3 class="blu-quiz__rtitle"></h3>
			<p class="blu-quiz__rwhy"></p>
			<a class="wp-element-button blu-quiz__rlink" href="#"><?php esc_html_e( 'See it', 'blu-core' ); ?></a>
			<button type="button" class="blu-quiz__again"><?php esc_html_e( 'Start over', 'blu-core' ); ?></button>
		</div>
	</div>
	<?php
	return ob_get_clean();
} );
