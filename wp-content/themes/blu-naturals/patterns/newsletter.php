<?php
/**
 * Title: Newsletter band
 * Slug: blu/newsletter
 * Categories: blu-home, blu-story
 * Description: Email sign-up with the 10% offer.
 */
?>
<!-- wp:group {"align":"full","className":"blu-newsletter is-style-shoreline","layout":{"type":"constrained","contentSize":"640px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}}} -->
<div class="wp-block-group alignfull blu-newsletter is-style-shoreline" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
<!-- wp:paragraph {"align":"center","className":"is-style-eyebrow"} --><p class="has-text-align-center is-style-eyebrow">Letters from the lanai</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">10% off your first order, and one letter a month after that.</h2><!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","textColor":"muted"} --><p class="has-text-align-center has-muted-color has-text-color">What the family made this month, one article, one product. No daily sales emails.</p><!-- /wp:paragraph -->
<!-- wp:shortcode -->
[blu_newsletter source="home" layout="inline"]
<!-- /wp:shortcode -->
</div>
<!-- /wp:group -->
