<?php
/**
 * Title: Journal teaser
 * Slug: blu/journal-teaser
 * Categories: blu-home
 * Description: Latest three articles.
 */
?>
<!-- wp:group {"className":"blu-section blu-journal-teaser","layout":{"type":"constrained","contentSize":"1180px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|40"}}}} -->
<div class="wp-block-group blu-section blu-journal-teaser" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:group {"className":"blu-section__head","layout":{"type":"flex","justifyContent":"space-between","verticalAlignment":"bottom","flexWrap":"wrap"}} -->
<div class="wp-block-group blu-section__head">
<!-- wp:group {"layout":{"type":"constrained","justifyContent":"left"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">The journal</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">How natural deodorant actually works</h2><!-- /wp:heading -->
</div>
<!-- /wp:group -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><a href="/journal/">All articles →</a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:query {"queryId":4,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"className":"blu-posts"} -->
<div class="wp-block-query blu-posts">
<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"blu-post-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group blu-post-card">
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","style":{"border":{"radius":"14px"}}} /-->
<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"large"} /-->
<!-- wp:post-excerpt {"excerptLength":18,"fontSize":"small"} /-->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->
<!-- wp:query-no-results -->
<!-- wp:paragraph --><p>The first letters are being written. Join the list in the footer and we will send them.</p><!-- /wp:paragraph -->
<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->
