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
if [ ! -f "$EXTENSION_PATH/scripts/validation/validate-playground-blueprint.sh" ]; then
    echo "ERROR: Homeboy Playground Blueprint validator not found at $EXTENSION_PATH" >&2
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

BLUEPRINT_ARTIFACT_DIR="${HOMEBOY_ARTIFACT_DIR:-${RUNNER_TEMP:-${TMPDIR:-/tmp}}/world-blueprint-validation}"
"$EXTENSION_PATH/scripts/validation/validate-playground-blueprint.sh" \
    "$REPO_ROOT/blueprints/world.json" \
    --wp "${PLAYGROUND_BLUEPRINT_WORDPRESS_VERSION:-latest}" \
    --php "${PLAYGROUND_BLUEPRINT_PHP_VERSION:-8.3}" \
    --artifact-dir "$BLUEPRINT_ARTIFACT_DIR"

SETTINGS_JSON=$(jq -nc \
    --arg mdi "$MDI_PATH" \
    '{
        validation_dependencies: [$mdi],
        playground_wordpress_version: "7.0",
        wp_config_defines: {
            MARKDOWN_DB_MODE: "primary",
            MARKDOWN_DB_CONTENT_DIR: "/wordpress/wp-content/plugins/world-of-wordpress/content"
        },
        playground_file_mounts: [
            {
                from_dependency: "markdown-database-integration",
                from: "db.php",
                to: "/wordpress/wp-content/db.php"
            }
        ],
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
theme=$(jq -r "$scenario | .metrics.world_theme_active_mean // .metrics.world_theme_active // 0" "$RESULTS_TMPFILE")
posts_front=$(jq -r "$scenario | .metrics.posts_front_page_mean // .metrics.posts_front_page // 0" "$RESULTS_TMPFILE")
site_title=$(jq -r "$scenario | .metrics.site_title_seeded_mean // .metrics.site_title_seeded // 0" "$RESULTS_TMPFILE")
tagline=$(jq -r "$scenario | .metrics.tagline_seeded_mean // .metrics.tagline_seeded // 0" "$RESULTS_TMPFILE")
sample_removed=$(jq -r "$scenario | .metrics.sample_content_removed_mean // .metrics.sample_content_removed // 0" "$RESULTS_TMPFILE")
comments_removed=$(jq -r "$scenario | .metrics.default_comments_removed_mean // .metrics.default_comments_removed // 0" "$RESULTS_TMPFILE")

if [ "$dropin" = "1" ] && [ "$primary" = "1" ] && [ "$theme" = "1" ] && [ "$posts_front" = "1" ] && [ "$site_title" = "1" ] && [ "$tagline" = "1" ] && [ "$sample_removed" = "1" ] && [ "$comments_removed" = "1" ]; then
    echo "World preview PASSED"
    exit 0
fi

echo "World preview FAILED" >&2
exit 1
