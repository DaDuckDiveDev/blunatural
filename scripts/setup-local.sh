#!/usr/bin/env bash
# One-command local WordPress for Blu Naturals. No MySQL needed (SQLite drop-in).
#
#   ./scripts/setup-local.sh            # creates ../blunatural-wp next to the repo and provisions it
#   WP_DIR=/path/to/site ./scripts/setup-local.sh
#
# Then:  cd $WP_DIR && wp server --port=8787   (or php -S localhost:8787 -t .)
# Admin: http://localhost:8787/wp-admin  admin / admin
#
# Requirements: php 8.1+ with sqlite3 + gd, wp-cli (https://wp-cli.org), git, unzip.
# On a Mac with LocalWP you can skip this script: point a new Local site at this repo's
# wp-content folders (symlink theme + plugin + uploads/blu) and run the provision step only.

set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
WP_DIR="${WP_DIR:-$REPO/../blunatural-wp}"
WP="${WP:-wp}"
ROOTFLAG=""
if [ "$(id -u)" = "0" ]; then ROOTFLAG="--allow-root"; fi
WOO_VERSION="${WOO_VERSION:-10.4.0}"

mkdir -p "$WP_DIR"; cd "$WP_DIR"

if [ ! -f wp-settings.php ]; then
  echo "→ Downloading WordPress"
  $WP $ROOTFLAG core download --skip-content
fi
mkdir -p wp-content/plugins wp-content/themes wp-content/uploads wp-content/database

if [ ! -d wp-content/plugins/sqlite-database-integration ]; then
  echo "→ SQLite drop-in"
  $WP $ROOTFLAG plugin install sqlite-database-integration --activate=false >/dev/null 2>&1 || {
    git clone --depth 1 https://github.com/WordPress/sqlite-database-integration.git /tmp/sqlite-db
    cp -rL /tmp/sqlite-db/packages/plugin-sqlite-database-integration wp-content/plugins/sqlite-database-integration
  }
  cp wp-content/plugins/sqlite-database-integration/db.copy wp-content/db.php
  sed -i.bak "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$WP_DIR/wp-content/plugins/sqlite-database-integration#; s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" wp-content/db.php && rm -f wp-content/db.php.bak
fi

if [ ! -f wp-config.php ]; then
  echo "→ wp-config.php"
  $WP $ROOTFLAG config create --dbname=blu --dbuser=blu --dbpass=blu --skip-check \
    --extra-php <<'PHP'
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'FS_METHOD', 'direct' );
define( 'WP_HOME', 'http://localhost:8787' );
define( 'WP_SITEURL', 'http://localhost:8787' );
PHP
fi

if [ ! -d wp-content/plugins/woocommerce ]; then
  echo "→ WooCommerce $WOO_VERSION"
  $WP $ROOTFLAG plugin install woocommerce --version="$WOO_VERSION" >/dev/null 2>&1 || {
    curl -sSL -o /tmp/woocommerce.zip "https://github.com/woocommerce/woocommerce/releases/download/$WOO_VERSION/woocommerce.zip"
    unzip -q -o /tmp/woocommerce.zip -d wp-content/plugins/
  }
fi

echo "→ Linking theme, plugin and brand uploads from the repo"
ln -sfn "$REPO/wp-content/themes/blu-naturals" wp-content/themes/blu-naturals
ln -sfn "$REPO/wp-content/plugins/blu-core" wp-content/plugins/blu-core
ln -sfn "$REPO/wp-content/uploads/blu" wp-content/uploads/blu

if ! $WP $ROOTFLAG core is-installed >/dev/null 2>&1; then
  echo "→ Installing WordPress"
  $WP $ROOTFLAG core install --url=http://localhost:8787 --title="Blu Naturals" --admin_user=admin --admin_password=admin --admin_email=dev@example.com --skip-email
fi

echo "→ Provisioning (theme, WooCommerce settings, products, pages, coupons, journal drafts)"
$WP $ROOTFLAG plugin activate woocommerce blu-core >/dev/null
$WP $ROOTFLAG theme activate blu-naturals >/dev/null
$WP $ROOTFLAG eval-file "$REPO/scripts/provision.php"

echo
echo "Done. Run:  cd $WP_DIR && $WP $ROOTFLAG server --port=8787"
echo "Site:  http://localhost:8787   Admin: http://localhost:8787/wp-admin (admin / admin)"
