#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../../.." && pwd)"

EXTENSION_PATH="${HOMEBOY_EXTENSION_PATH:-/Users/chubes/Developer/homeboy-extensions/wordpress}"
MDI_PATH="${MDI_PATH:-/Users/chubes/Developer/markdown-database-integration}"

if [ ! -f "$EXTENSION_PATH/scripts/bench/bench-runner.sh" ]; then
    echo "ERROR: Homeboy WordPress extension not found at $EXTENSION_PATH" >&2
    exit 1
fi
if [ ! -d "$MDI_PATH" ]; then
    echo "ERROR: Markdown Database Integration not found at $MDI_PATH" >&2
    exit 1
fi
if ! command -v jq >/dev/null 2>&1; then
    echo "ERROR: jq required" >&2
    exit 1
fi

RESULTS_TMPFILE=$(mktemp "${TMPDIR:-/tmp}/world-preview.XXXXXX")
cleanup() {
    rm -f "$RESULTS_TMPFILE"
}
trap cleanup EXIT

SETTINGS_JSON=$(jq -nc \
    --arg mdi "$MDI_PATH" \
    '{
        validation_dependencies: [$mdi],
        playground_wordpress_version: "7.0",
        wp_config_defines: {
            MARKDOWN_DB_MODE: "primary",
            MARKDOWN_DB_CONTENT_DIR: "/wordpress/wp-content/plugins/world-of-wordpress/content/markdown"
        },
        playground_workloads: [
            {
                id: "world-preview",
                label: "World of WordPress preview probe",
                run: [
                    { type: "php", file: "tests/playground-ci/workloads/world-preview-probe.php" }
                ]
            }
        ]
    }')

HOMEBOY_BENCH_RESULTS_FILE="$RESULTS_TMPFILE" \
HOMEBOY_BENCH_ITERATIONS=1 \
HOMEBOY_BENCH_WARMUP_ITERATIONS=0 \
HOMEBOY_COMPONENT_ID=world-of-wordpress \
HOMEBOY_COMPONENT_PATH="$REPO_ROOT" \
HOMEBOY_EXTENSION_PATH="$EXTENSION_PATH" \
HOMEBOY_SETTINGS_JSON="$SETTINGS_JSON" \
    bash "$EXTENSION_PATH/scripts/bench/bench-runner.sh"

if [ ! -s "$RESULTS_TMPFILE" ]; then
    echo "ERROR: results file empty or missing at $RESULTS_TMPFILE" >&2
    exit 1
fi

cat "$RESULTS_TMPFILE"

scenario='.scenarios[] | select(.id == "world-preview")'
dropin=$(jq -r "$scenario | .metrics.markdown_db_dropin_loaded_mean // .metrics.markdown_db_dropin_loaded // 0" "$RESULTS_TMPFILE")
primary=$(jq -r "$scenario | .metrics.markdown_db_primary_mode_mean // .metrics.markdown_db_primary_mode // 0" "$RESULTS_TMPFILE")
home=$(jq -r "$scenario | .metrics.home_page_found_mean // .metrics.home_page_found // 0" "$RESULTS_TMPFILE")
seed=$(jq -r "$scenario | .metrics.home_content_seeded_mean // .metrics.home_content_seeded // 0" "$RESULTS_TMPFILE")
sample_removed=$(jq -r "$scenario | .metrics.sample_content_removed_mean // .metrics.sample_content_removed // 0" "$RESULTS_TMPFILE")

if [ "$dropin" = "1" ] && [ "$primary" = "1" ] && [ "$home" = "1" ] && [ "$seed" = "1" ] && [ "$sample_removed" = "1" ]; then
    echo "World preview PASSED"
    exit 0
fi

echo "World preview FAILED" >&2
exit 1
