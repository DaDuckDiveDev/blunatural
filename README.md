# Blu Naturals — WooCommerce rebuild

Shopify → WordPress/WooCommerce for [blunaturals.com](https://blunaturals.com), with a new identity.
Built by Kaiel Systems. Everything the site needs that is not WordPress core or WooCommerce lives in this repo.

```
wp-content/themes/blu-naturals/   block theme: theme.json tokens, templates, patterns, 3 colorways (styles/)
wp-content/plugins/blu-core/      store logic: maker fields, care notes, newsletter, scent quiz, contact form,
                                  free-shipping bar, Shopify URL redirects, order-email notes
wp-content/uploads/blu/           logo set + the composed product images the site uses
import/products.json              the catalog (13 products, 26 variants, copy, weights, stock, coupons)
import/pages/*.html               FAQ, shipping & returns, contact, privacy, terms, do-not-sell
import/blog/                      10 journal article drafts with SEO meta (need the Instagram archive to finish)
scripts/provision.php             idempotent: settings, categories, products, coupons, pages, logo, drafts
scripts/setup-local.sh            one-command local site (SQLite, no MySQL)
scripts/deploy.sh                 rsync to SiteGround (same as the GitHub Action)
design/                           logo SVG/PNG set, transparent cut-outs (WebP), the image/logo build scripts
docs/brand/                       the brand proposal page (logo, colorways, imagery, growth plan)
```

## Run it locally

Needs PHP 8.1+ (with sqlite3 and gd), [wp-cli](https://wp-cli.org), git. No database server.

```bash
git clone https://github.com/DaDuckDiveDev/blunatural.git && cd blunatural
./scripts/setup-local.sh            # downloads WP + WooCommerce, links this repo in, provisions everything
cd ../blunatural-wp && wp server --port=8787
```

Site: http://localhost:8787 · Admin: /wp-admin (admin / admin). Re-run `wp eval-file ../blunatural/scripts/provision.php`
any time to reset content; it updates by slug and does not duplicate.

Using LocalWP instead: create a site, symlink `wp-content/themes/blu-naturals`, `wp-content/plugins/blu-core` and
`wp-content/uploads/blu` from this repo into it, install WooCommerce, then run the provision step from Local's shell.

## Deploy to SiteGround

1. Site Tools → Devs → **SSH Keys Manager** → Import: paste the public key (`blunaturals_siteground.pub`).
2. GitHub → repo → Settings → Secrets and variables → Actions:
   `SG_SSH_KEY` (private key), `SG_HOST` = `ssh.johnw870.sg-host.com`, `SG_USER` = `u988-texgqnc3rkhl`,
   `SG_PATH` = WordPress root (`/home/u988-texgqnc3rkhl/www/<domain>/public_html`). Port 18765 is the default.
3. Install WordPress on SiteGround with their installer (WordPress + WooCommerce option is fine).
4. Actions → **Deploy to SiteGround** → Run workflow with **provision** ticked. After that, every push to `main` deploys.

Manual alternative: `cp scripts/.deploy.env.example scripts/.deploy.env`, fill it in, `./scripts/deploy.sh provision`.

The repo never contains WordPress core, WooCommerce, or any credentials. SiteGround keeps core and plugins updated.

## What the client edits where

| Thing | Where |
|---|---|
| Homepage sections, Our family page | Pages → edit (patterns are editable blocks) |
| Header, footer, announcement bar, menus | Appearance → Editor → Patterns → Template parts |
| Colorway (Tidepool / Kai / Lagoon) | Appearance → Editor → Styles → Browse styles |
| Products, prices, stock, photos | Products (WooCommerce) |
| Who made it / batch week / care note per product | Product edit → "Blu Naturals: who made this" box |
| Free-shipping threshold, coupon codes shown, maker default, Instagram | Settings → Blu Naturals |
| Newsletter sign-ups captured on-site | Products → Subscribers (CSV export on the settings page) |
| Journal articles | Posts (10 drafts are waiting) |
| Scent quiz questions and mapping | `blu-core/inc/quiz.php` (code) |

## Decisions still open (carry-overs from the Shopify audit)

- **Prices**: imported as they were ($12 deodorant, $8 soap). Shopify had Hawaii GET (4.17%) baked into prices;
  WooCommerce is set to *not* calculate tax. Decide tax-inclusive vs exclusive with the bookkeeper before launch
  (`woocommerce_calc_taxes` + Tax settings), and whether to move deodorant to $14 with the new identity.
- **Stock** is the Shopify count on 5 Nov 2026. "The Original" scent was at 0. Update before launch.
- **Shipping**: one US zone, $7 flat, free over $40. Shopify's weight tiers ($5 / $7 / $10) need a table-rate
  plugin (Flexible Shipping free tier) if the client wants them back.
- **Gift cards**: WooCommerce has none built in. PW WooCommerce Gift Cards (free) → create $10/$25/$50/$100; the
  old `/products/blubird-naturals-gift-card` URL already redirects to `/gift-card/`.
- **Digital download**: the DIY recipes product exists as virtual+downloadable; upload the PDF to it.
- **Dry shampoo** was on a 50% sale in Shopify ($15 → $7.50); imported the same way. Confirm it is meant to stay.
- **Email tool**: Klaviyo proposed. The plugin fires `blu_subscriber_added`, `blu_customer_ordered` and
  `blu_order_completed` hooks so flows can be wired without touching the theme.
- **Search engines are blocked** (`blog_public = 0`) until launch. Flip in Settings → Reading.
- **Labels** still carry the old logo in every product photo; label redesign follows the colorway decision.
- **Family content**: names, roles, photos and the origin story slot into the Our family page and the product strips.
- **Instagram archive** (JSON export) is what finishes the ten journal drafts.
