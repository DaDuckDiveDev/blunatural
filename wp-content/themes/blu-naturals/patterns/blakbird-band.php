<?php
/**
 * Title: Blakbird band
 * Slug: blu/blakbird-band
 * Categories: blu-home
 * Description: Dark band for the men's line.
 */
$img = content_url( '/uploads/blu/products/blakbird-natural-deodorant-for-men/01-portrait.jpg' );
$logo = content_url( '/uploads/blu/logo/blakbird-horizontal-reversed.svg' );
?>
<!-- wp:group {"align":"full","className":"blu-blakbird","backgroundColor":"black","textColor":"paper","layout":{"type":"constrained","contentSize":"1180px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}}} -->
<div class="wp-block-group alignfull blu-blakbird has-paper-color has-black-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
<!-- wp:media-text {"mediaPosition":"right","mediaType":"image","mediaWidth":42,"className":"blu-blakbird__mt","style":{"spacing":{"blockGap":"var:preset|spacing|40"}}} -->
<div class="wp-block-media-text alignwide has-media-on-the-right is-stacked-on-mobile blu-blakbird__mt" style="grid-template-columns:auto 42%"><div class="wp-block-media-text__content">
<!-- wp:image {"width":"200px","className":"blu-blakbird__logo"} --><figure class="wp-block-image is-resized blu-blakbird__logo"><img src="<?php echo esc_url( $logo ); ?>" alt="Blakbird by Blu Naturals" style="width:200px"/></figure><!-- /wp:image -->
<!-- wp:heading {"level":2,"textColor":"paper"} --><h2 class="wp-block-heading has-paper-color has-text-color">Deodorant for men that does not smell like a candle.</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Same formula as the Original. Cedarwood, sage, tea tree and vanilla in No.1; a sandalwood and local vanilla bean infusion in No.3 for anyone who wants scent without essential oils.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"paper","textColor":"black"} --><div class="wp-block-button"><a class="wp-block-button__link has-black-color has-paper-background-color has-text-color has-background wp-element-button" href="/product-category/blakbird/">Shop Blakbird</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><figure class="wp-block-media-text__media"><img src="<?php echo esc_url( $img ); ?>" alt="Blakbird No.1 deodorant" style="border-radius:14px"/></figure></div>
<!-- /wp:media-text -->
</div>
<!-- /wp:group -->
