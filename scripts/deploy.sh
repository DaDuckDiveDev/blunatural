#!/usr/bin/env bash
# Manual deploy to SiteGround over SSH (rsync). CI does the same thing in .github/workflows/deploy.yml.
#
#   cp scripts/.deploy.env.example scripts/.deploy.env   # fill in, never commit
#   ./scripts/deploy.sh            # theme + plugin + brand uploads
#   ./scripts/deploy.sh provision  # ...then run provision.php on the server (first deploy / content reset)
#
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$REPO/scripts/.deploy.env"
[ -f "$ENV_FILE" ] || { echo "Missing $ENV_FILE (copy .deploy.env.example)"; exit 1; }
# shellcheck disable=SC1090
source "$ENV_FILE"

: "${SG_HOST:?}" "${SG_USER:?}" "${SG_PORT:=18765}" "${SG_PATH:?}" "${SG_KEY:=$HOME/.ssh/blunaturals_siteground}"
SSH="ssh -i $SG_KEY -p $SG_PORT -o StrictHostKeyChecking=accept-new"

echo "→ rsync theme, plugin, brand uploads → $SG_USER@$SG_HOST:$SG_PATH"
rsync -az --delete -e "$SSH" "$REPO/wp-content/themes/blu-naturals/" "$SG_USER@$SG_HOST:$SG_PATH/wp-content/themes/blu-naturals/"
rsync -az --delete -e "$SSH" "$REPO/wp-content/plugins/blu-core/"   "$SG_USER@$SG_HOST:$SG_PATH/wp-content/plugins/blu-core/"
rsync -az          -e "$SSH" "$REPO/wp-content/uploads/blu/"        "$SG_USER@$SG_HOST:$SG_PATH/wp-content/uploads/blu/"
$SSH "$SG_USER@$SG_HOST" "mkdir -p $SG_PATH/../blu-import/import $SG_PATH/../blu-import/scripts"
rsync -az --delete -e "$SSH" "$REPO/import/"               "$SG_USER@$SG_HOST:$SG_PATH/../blu-import/import/"
rsync -az          -e "$SSH" "$REPO/scripts/provision.php" "$SG_USER@$SG_HOST:$SG_PATH/../blu-import/scripts/provision.php"

if [ "${1:-}" = "provision" ]; then
  echo "→ provisioning on the server"
  $SSH "$SG_USER@$SG_HOST" "cd $SG_PATH && wp eval-file ../blu-import/scripts/provision.php"
fi

echo "→ flushing caches"
$SSH "$SG_USER@$SG_HOST" "cd $SG_PATH && wp cache flush && (wp sg purge 2>/dev/null || true)"
echo "Deployed."
