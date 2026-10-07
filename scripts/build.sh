#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST="$ROOT/dist"
rm -rf "$DIST"
mkdir -p "$DIST"
(cd "$ROOT/wp-content/plugins" && zip -qr "$DIST/slt-core-plugin.zip" slt-core)
(cd "$ROOT/wp-content/themes" && zip -qr "$DIST/slt-travel-theme.zip" slt-travel)
echo "Built WordPress packages:"
ls -lh "$DIST"/*.zip
