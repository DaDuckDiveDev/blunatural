<?php
/**
 * Title: Our family page
 * Slug: blu/story
 * Categories: blu-story
 * Block Types: core/post-content
 * Post Types: page
 * Description: The story page: origin, how it is made, who does what, the promise.
 */
$img1 = content_url( '/uploads/blu/brand/family-placeholder.jpg' );
$img2 = content_url( '/uploads/blu/products/organic-citrus-lavender-bar-soap/09-card.jpg' );
$img3 = content_url( '/uploads/blu/products/all-natural-deodorant-jar/01-card.jpg' );
?>
<!-- wp:cover {"url":"<?php echo esc_url( $img1 ); ?>","dimRatio":35,"overlayColor":"night","minHeight":420,"align":"full","className":"blu-story__hero","layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-cover alignfull blu-story__hero" style="min-height:420px"><span aria-hidden="true" class="wp-block-cover__background has-night-background-color has-background-dim-35 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( $img1 ); ?>" data-object-fit="cover"/><div class="wp-block-cover__inner-container">
<!-- wp:paragraph {"align":"center","className":"is-style-eyebrow","textColor":"glass"} --><p class="has-text-align-center is-style-eyebrow has-glass-color has-text-color">Our family</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":1,"textAlign":"center","textColor":"paper"} --><h1 class="wp-block-heading has-text-align-center has-paper-color has-text-color">We make it in our kitchen in Honolulu. Here is who "we" is.</h1><!-- /wp:heading -->
</div></div>
<!-- /wp:cover -->

<!-- wp:group {"className":"blu-story__body","layout":{"type":"constrained","contentSize":"720px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|40"}}}} -->
<div class="wp-block-group blu-story__body" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">How it started</h2><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"large"} --><p class="has-large-font-size">[Origin story in the family's own words. Two paragraphs: why the first batch got made, what did not work about store-bought natural deodorant, the first person outside the family who asked to buy one.]</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>[Second paragraph: the move from kitchen to small batches, the farmers' market or first shop, the first mainland order.]</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">How it is made</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Every stick starts with coconut oil, organic beeswax and shea butter melted together, then arrowroot, clay and diatomaceous earth for the dry feel, and either essential oils or a slow infusion of dried herbs for scent. Batches are small enough to pour by hand into the tubes, which is why a stick can soften if it sits in a hot mailbox. An hour upright in the fridge fixes it; nothing in it spoils.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>The soap is a certified organic cold-process bar, cut and stamped with a monstera leaf, boxed in recycled card. The bug spray is witch hazel infused for three weeks with peppermint leaf, lavender and vanilla beans from Oʻahu's North Shore.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","backgroundColor":"mist","className":"blu-story__people","layout":{"type":"constrained","contentSize":"1180px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}}} -->
<div class="wp-block-group alignfull blu-story__people has-mist-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">Who does what</h2><!-- /wp:heading -->
<!-- wp:columns {"className":"blu-people"} -->
<div class="wp-block-columns blu-people">
<!-- wp:column --><div class="wp-block-column"><!-- wp:image {"aspectRatio":"1","scale":"cover","style":{"border":{"radius":"14px"}}} --><figure class="wp-block-image has-custom-border"><img src="<?php echo esc_url( $img1 ); ?>" alt="" style="border-radius:14px;aspect-ratio:1;object-fit:cover"/></figure><!-- /wp:image --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">[Name]</h3><!-- /wp:heading --><!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">[Role: makes the deodorant and tests every new scent on the family first.]</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:image {"aspectRatio":"1","scale":"cover","style":{"border":{"radius":"14px"}}} --><figure class="wp-block-image has-custom-border"><img src="<?php echo esc_url( $img2 ); ?>" alt="" style="border-radius:14px;aspect-ratio:1;object-fit:cover"/></figure><!-- /wp:image --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">[Name]</h3><!-- /wp:heading --><!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">[Role: labels, packs and writes the note in your box.]</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:image {"aspectRatio":"1","scale":"cover","style":{"border":{"radius":"14px"}}} --><figure class="wp-block-image has-custom-border"><img src="<?php echo esc_url( $img3 ); ?>" alt="" style="border-radius:14px;aspect-ratio:1;object-fit:cover"/></figure><!-- /wp:image --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">[Name]</h3><!-- /wp:heading --><!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">[Role: runs the post office trips and answers your emails.]</p><!-- /wp:paragraph --></div><!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"blu-story__promise","layout":{"type":"constrained","contentSize":"720px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}}} -->
<div class="wp-block-group blu-story__promise" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">What we promise</h2><!-- /wp:heading -->
<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li><strong>Every ingredient is on the label</strong>, in plain words, and there are never more than nine of them.</li><!-- /wp:list-item --><!-- wp:list-item --><li><strong>It is not an antiperspirant.</strong> It handles odor; your sweat glands keep doing their job.</li><!-- /wp:list-item --><!-- wp:list-item --><li><strong>If it does not work for you, tell us.</strong> Natural deodorant takes about two weeks to settle in; if it has not by then, we will swap it for a different formula or refund it.</li><!-- /wp:list-item --><!-- wp:list-item --><li><strong>Made this week.</strong> Nothing sits in a warehouse. If a scent is out of stock, it is because the next batch is still setting.</li><!-- /wp:list-item --></ul><!-- /wp:list -->
</div>
<!-- /wp:group -->
<!-- wp:pattern {"slug":"blu/quiz-cta"} /-->
<!-- wp:pattern {"slug":"blu/newsletter"} /-->
