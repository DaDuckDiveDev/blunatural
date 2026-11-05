/* Blu Naturals theme: sticky add-to-cart on mobile product pages. */
(function () {
  'use strict';

  function stickyAtc() {
    var form = document.querySelector('.blu-product form.cart');
    var btn = form && form.querySelector('.single_add_to_cart_button');
    var title = document.querySelector('.blu-product__buy .wp-block-post-title');
    var price = document.querySelector('.blu-product__buy .wp-block-woocommerce-product-price');
    if (!form || !btn || !title) { return; }

    var bar = document.createElement('div');
    bar.className = 'blu-sticky-atc';
    bar.innerHTML = '<div><div class="blu-sticky-atc__name"></div><div class="blu-sticky-atc__price"></div></div>' +
      '<button type="button" class="wp-element-button">Add to cart</button>';
    bar.querySelector('.blu-sticky-atc__name').textContent = title.textContent.trim();
    bar.querySelector('.blu-sticky-atc__price').textContent = price ? price.textContent.trim() : '';
    bar.querySelector('button').addEventListener('click', function () {
      if (btn.disabled) { form.scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }
      btn.click();
    });
    document.body.appendChild(bar);

    var io = new IntersectionObserver(function (entries) {
      bar.classList.toggle('is-visible', !entries[0].isIntersecting);
    }, { rootMargin: '0px 0px -40px 0px' });
    io.observe(btn);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', stickyAtc);
  } else {
    stickyAtc();
  }
})();
