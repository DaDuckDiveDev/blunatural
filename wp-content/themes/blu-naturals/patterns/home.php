<?php
/**
 * Title: Home page (full composition)
 * Slug: blu/home
 * Categories: blu-home
 * Block Types: core/post-content
 * Post Types: page
 * Description: Hero, trust strip, bestsellers, family, Blakbird, journal, scent quiz, newsletter. Used to seed the Home page.
 */
?>
<!-- wp:pattern {"slug":"blu/hero"} /-->
<!-- wp:pattern {"slug":"blu/trust-strip"} /-->
<!-- wp:pattern {"slug":"blu/bestsellers"} /-->
<!-- wp:pattern {"slug":"blu/family"} /-->
<!-- wp:group {"className":"blu-section","layout":{"type":"constrained","contentSize":"1180px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|20"}}}} -->
<div class="wp-block-group blu-section" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--20)">
<!-- wp:pattern {"slug":"blu/quiz-cta"} /-->
</div>
<!-- /wp:group -->
<!-- wp:pattern {"slug":"blu/blakbird-band"} /-->
<!-- wp:pattern {"slug":"blu/journal-teaser"} /-->
<!-- wp:pattern {"slug":"blu/newsletter"} /-->
