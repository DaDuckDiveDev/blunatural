<?php
/**
 * Title: Family story block
 * Slug: blu/family
 * Categories: blu-home, blu-story
 * Description: Photo beside three sentences about the family, linking to the story page.
 */
$img = content_url( '/uploads/blu/brand/family-placeholder.jpg' );
?>
<!-- wp:group {"align":"full","className":"blu-family","backgroundColor":"sand","layout":{"type":"constrained","contentSize":"1180px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}}} -->
<div class="wp-block-group alignfull blu-family has-sand-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
<!-- wp:media-text {"mediaPosition":"left","mediaType":"image","mediaWidth":46,"imageFill":true,"className":"blu-family__mt","style":{"spacing":{"blockGap":"var:preset|spacing|40"}}} -->
<div class="wp-block-media-text alignwide is-stacked-on-mobile is-image-fill blu-family__mt" style="grid-template-columns:46% auto"><figure class="wp-block-media-text__media" style="background-image:url(<?php echo esc_url( $img ); ?>);background-position:50% 50%"><img src="<?php echo esc_url( $img ); ?>" alt="The family at the pour table"/></figure><div class="wp-block-media-text__content">
<!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">Who makes it</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">A family kitchen in Honolulu, not a factory.</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We started mixing deodorant for ourselves because the natural ones we bought did not work and the ones that worked were not natural. Four years and a few thousand sticks later, every batch is still poured by hand, labeled by hand, and packed by the people whose names are on the box.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Every competitor has coconut oil and arrowroot. None of them has a family infusing witch hazel with North Shore vanilla beans for three weeks.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline-blu"} --><div class="wp-block-button is-style-outline-blu"><a class="wp-block-button__link wp-element-button" href="/our-family/">Meet the family</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div></div>
<!-- /wp:media-text -->
</div>
<!-- /wp:group -->
