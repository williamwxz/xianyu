#!/usr/bin/env bash
# Deploy the baijia_tools addon to the niucloud server.
# Usage (as root, from the unpacked bundle directory):
#   bash deploy-baijia.sh [/www/wwwroot/niucloud] [--layout]
# First deploy only: JUTUIKE_APIKEY=xxxx writes .env [JUTUIKE] APIKEY.
# --layout re-applies start page + bottom navigation (overwrites admin edits).
set -euo pipefail
ROOT="${1:-/www/wwwroot/niucloud}"
LAYOUT="${2:-}"
HERE="$(cd "$(dirname "$0")" && pwd)"
[ -f "$ROOT/install.lock" ] || { echo "not a niucloud root: $ROOT" >&2; exit 1; }
[ -d "$HERE/baijia_tools" ] || { echo "bundle dir baijia_tools missing next to this script" >&2; exit 1; }

BACKUP="/root/baijia-backup-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP"
cp -a "$ROOT/addon/baijia_tools" "$BACKUP/addon"
[ -d "$ROOT/public/addon/baijia_tools" ] && cp -a "$ROOT/public/addon/baijia_tools" "$BACKUP/public"
cp -a "$ROOT/.env" "$BACKUP/env"
chmod -R go-rwx "$BACKUP"
echo "backup: $BACKUP"

rsync -a --delete --exclude tests --exclude '._*' "$HERE/baijia_tools/" "$ROOT/addon/baijia_tools/"
mkdir -p "$ROOT/public/addon/baijia_tools"
rsync -a --exclude '._*' "$HERE/baijia_tools/resource/" "$ROOT/public/addon/baijia_tools/"

if ! grep -q '^\[JUTUIKE\]' "$ROOT/.env"; then
  [ -n "${JUTUIKE_APIKEY:-}" ] || { echo "first deploy: set JUTUIKE_APIKEY" >&2; exit 1; }
  printf '\n[JUTUIKE]\nAPIKEY = %s\n' "$JUTUIKE_APIKEY" >> "$ROOT/.env"
  echo ".env: added [JUTUIKE]"
fi

chown -R www:www "$ROOT/addon/baijia_tools" "$ROOT/public/addon/baijia_tools"
run_php() { cp "$HERE/$1" "/tmp/$1" && chown www "/tmp/$1" && sudo -u www php "/tmp/$1" "$ROOT"; rm -f "/tmp/$1"; }
run_php baijia-post-deploy.php
[ "$LAYOUT" = "--layout" ] && run_php update-baijia-layout.php

# the schedule worker loads task classes and the task list at start
systemctl restart niucloud-worker && sleep 2 && systemctl is-active niucloud-worker
echo "rollback: rsync -a --delete $BACKUP/addon/ $ROOT/addon/baijia_tools/ && cp -a $BACKUP/env $ROOT/.env && systemctl restart niucloud-worker"
