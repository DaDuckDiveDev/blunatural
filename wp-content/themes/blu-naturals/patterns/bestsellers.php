<?php
/**
 * Title: Bestsellers
 * Slug: blu/bestsellers
 * Categories: blu-home, blu-shop
 * Description: Three-up product grid of the deodorant category.
 */
?>
<!-- wp:group {"className":"blu-section blu-bestsellers","layout":{"type":"constrained","contentSize":"1180px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|40"}}}} -->
<div class="wp-block-group blu-section blu-bestsellers" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:group {"className":"blu-section__head","layout":{"type":"flex","justifyContent":"space-between","verticalAlignment":"bottom","flexWrap":"wrap"}} -->
<div class="wp-block-group blu-section__head">
<!-- wp:group {"layout":{"type":"constrained","justifyContent":"left"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">Most reordered</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">The deodorant</h2><!-- /wp:heading -->
</div>
<!-- /wp:group -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><a href="/product-category/deodorant/">All scents and formulas →</a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:woocommerce/product-collection {"queryId":3,"query":{"perPage":3,"pages":0,"offset":0,"postType":"product","order":"asc","orderBy":"menu_order","search":"","exclude":[],"inherit":false,"taxQuery":{"product_cat":[]},"isProductCollectionBlock":true,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false,"featured":false},"tagName":"div","displayLayout":{"type":"flex","columns":3,"shrinkColumns":true},"collection":"woocommerce/product-collection/featured","hideControls":["inherit","filterable","featured"],"queryContextIncludes":["collection"],"className":"blu-grid"} -->
<div class="wp-block-woocommerce-product-collection blu-grid">
<!-- wp:woocommerce/product-template {"className":"blu-card-list"} -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true,"className":"blu-card__img"} -->
<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"right"} /-->
<!-- /wp:woocommerce/product-image -->
<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"large","__woocommerceNamespace":"woocommerce/product-collection/product-title","className":"blu-card__title"} /-->
<!-- wp:post-excerpt {"excerptLength":14,"fontSize":"small","className":"blu-card__sub","__woocommerceNamespace":"woocommerce/product-collection/product-summary"} /-->
<!-- wp:group {"className":"blu-card__row","layout":{"type":"flex","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group blu-card__row">
<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true,"fontSize":"medium"} /-->
<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true,"fontSize":"x-small","className":"blu-card__btn"} /-->
</div>
<!-- /wp:group -->
<!-- /wp:woocommerce/product-template -->
</div>
<!-- /wp:woocommerce/product-collection -->
</div>
<!-- /wp:group -->
