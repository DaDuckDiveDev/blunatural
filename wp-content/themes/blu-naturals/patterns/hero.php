<?php
/**
 * Title: Home hero
 * Slug: blu/hero
 * Categories: blu-home
 * Description: Full-width shoreline hero with the Original deodorant and two calls to action.
 */
$hero = content_url( '/uploads/blu/products/all-natural-deodorant/01-hero.jpg' );
?>
<!-- wp:cover {"url":"<?php echo esc_url( $hero ); ?>","dimRatio":0,"minHeight":560,"minHeightUnit":"px","contentPosition":"center left","isDark":false,"align":"full","className":"blu-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}},"layout":{"type":"constrained","contentSize":"1180px"}} -->
<div class="wp-block-cover alignfull is-light has-custom-content-position is-position-center-left blu-hero" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--30);min-height:560px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( $hero ); ?>" data-object-fit="cover"/><div class="wp-block-cover__inner-container">
<!-- wp:group {"className":"blu-hero__copy","layout":{"type":"constrained","contentSize":"520px","justifyContent":"left"}} -->
<div class="wp-block-group blu-hero__copy">
<!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow">Small batch · Honolulu, Hawaiʻi</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1,"fontSize":"huge"} -->
<h1 class="wp-block-heading has-huge-font-size">Deodorant that works, made by a family in Honolulu.</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"large","textColor":"muted"} -->
<p class="has-muted-color has-text-color has-large-font-size">Coconut oil, arrowroot, clay and essential oils. Nothing you have to look up. Aluminum free, small batch, shipped from Hawaiʻi.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"style":{"spacing":{"blockGap":"0.75rem"}}} -->
<div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/product-category/deodorant/">Shop deodorant</a></div><!-- /wp:button --><!-- wp:button {"className":"is-style-outline-blu"} --><div class="wp-block-button is-style-outline-blu"><a class="wp-block-button__link wp-element-button" href="/find-your-scent/">Find your scent</a></div><!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div></div>
<!-- /wp:cover -->
