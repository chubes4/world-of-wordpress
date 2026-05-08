#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../../.." && pwd)"
COMPONENT_PATH="$REPO_ROOT/tests/playground-ci/component"
WORKLOAD_PATH="$REPO_ROOT/tests/playground-ci/workloads/world-creator-day-cycle.php"
BUNDLE_SOURCE="$REPO_ROOT/bundles/world-creator"

EXTENSION_PATH="${HOMEBOY_EXTENSION_PATH:-/Users/chubes/Developer/homeboy-extensions/wordpress}"
DM_PATH="${DM_PATH:-/Users/chubes/Developer/data-machine}"
DMC_PATH="${DMC_PATH:-/Users/chubes/Developer/data-machine-code}"
OPENAI_PROVIDER_PATH="${OPENAI_PROVIDER_PATH:-/Users/chubes/Studio/intelligence-chubes4/wp-content/plugins/ai-provider-for-openai}"
STUDIO_SITE_PATH="${STUDIO_SITE_PATH:-/Users/chubes/Studio/intelligence-chubes4}"
WORLD_CREATOR_OPENAI_MODEL="${WORLD_CREATOR_OPENAI_MODEL:-gpt-5.5}"
WORLD_CREATOR_TARGET_REPO="${WORLD_CREATOR_TARGET_REPO:-chubes4/world-of-wordpress}"
WORLD_CREATOR_PROMPT="${WORLD_CREATOR_PROMPT:-Begin a day cycle. Inspect your world and propose the next visible mutation as a pull request.}"

if [ ! -f "$EXTENSION_PATH/scripts/bench/bench-runner.sh" ]; then
    echo "ERROR: Homeboy WordPress extension not found at $EXTENSION_PATH" >&2
    exit 1
fi
if [ ! -d "$DM_PATH" ] || [ ! -d "$DMC_PATH" ]; then
    echo "ERROR: Data Machine and Data Machine Code checkouts are required" >&2
    exit 1
fi
if [ ! -d "$OPENAI_PROVIDER_PATH" ]; then
    echo "ERROR: AI Provider for OpenAI plugin not found at $OPENAI_PROVIDER_PATH" >&2
    exit 1
fi
if [ ! -d "$BUNDLE_SOURCE" ]; then
    echo "ERROR: World Creator bundle not found at $BUNDLE_SOURCE" >&2
    exit 1
fi
if ! command -v jq >/dev/null 2>&1; then
    echo "ERROR: jq required" >&2
    exit 1
fi

GITHUB_TOKEN="${GITHUB_TOKEN:-${GH_TOKEN:-}}"
if [ -z "$GITHUB_TOKEN" ] && command -v gh >/dev/null 2>&1; then
    GITHUB_TOKEN="$(gh auth token 2>/dev/null || true)"
fi
if [ -z "$GITHUB_TOKEN" ]; then
    echo "ERROR: GITHUB_TOKEN or GH_TOKEN is required, or gh must be authenticated" >&2
    exit 1
fi

OPENAI_API_KEY="${OPENAI_API_KEY:-}"
if [ -z "$OPENAI_API_KEY" ] && command -v studio >/dev/null 2>&1 && [ -d "$STUDIO_SITE_PATH" ]; then
    OPENAI_API_KEY="$(cd "$STUDIO_SITE_PATH" && studio wp option get connectors_ai_openai_api_key 2>/dev/null || true)"
fi
if [ -z "$OPENAI_API_KEY" ]; then
    echo "ERROR: OPENAI_API_KEY is required, or the local Studio site must store connectors_ai_openai_api_key" >&2
    exit 1
fi

RESULTS_TMPFILE=$(mktemp "${TMPDIR:-/tmp}/world-creator.XXXXXX")
COMPONENT_WORKLOAD="$COMPONENT_PATH/world-creator-day-cycle.php"
COMPONENT_BUNDLE_DIR="$COMPONENT_PATH/bundles/world-creator"
TRANSCRIPT_ARTIFACT_DIR="$COMPONENT_PATH/artifacts/world-creator"

cleanup() {
    rm -f "$RESULTS_TMPFILE" "$COMPONENT_WORKLOAD"
    rm -rf "$COMPONENT_PATH/bundles"
}
trap cleanup EXIT

rm -rf "$TRANSCRIPT_ARTIFACT_DIR"
mkdir -p "$TRANSCRIPT_ARTIFACT_DIR" "$COMPONENT_PATH/bundles"
cp "$WORKLOAD_PATH" "$COMPONENT_WORKLOAD"
cp -R "$BUNDLE_SOURCE" "$COMPONENT_BUNDLE_DIR"

SETTINGS_JSON=$(jq -nc \
    --arg dm "$DM_PATH" \
    --arg dmc "$DMC_PATH" \
    --arg openaiProvider "$OPENAI_PROVIDER_PATH" \
    --arg githubToken "$GITHUB_TOKEN" \
    --arg openaiKey "$OPENAI_API_KEY" \
    --arg model "$WORLD_CREATOR_OPENAI_MODEL" \
    --arg targetRepo "$WORLD_CREATOR_TARGET_REPO" \
    --arg prompt "$WORLD_CREATOR_PROMPT" \
    '{
        validation_dependencies: [$dm, $dmc, $openaiProvider],
        playground_wordpress_version: "7.0",
        bench_env: {
            GITHUB_TOKEN: $githubToken,
            OPENAI_API_KEY: $openaiKey,
            WORLD_CREATOR_OPENAI_MODEL: $model,
            WORLD_CREATOR_TARGET_REPO: $targetRepo,
            WORLD_CREATOR_PROMPT: $prompt,
            WORLD_CREATOR_TRANSCRIPT_DIR: "/wordpress/wp-content/plugins/world-of-wordpress-ci-driver/artifacts/world-creator"
        },
        playground_workloads: [
            {
                id: "world-creator-day-cycle",
                label: "Run World Creator day cycle",
                run: [
                    { type: "php", file: "world-creator-day-cycle.php" }
                ]
            }
        ]
    }')

echo "============================================"
echo "World Creator day cycle"
echo "============================================"
echo "Target repo:  $WORLD_CREATOR_TARGET_REPO"
echo "OpenAI model: $WORLD_CREATOR_OPENAI_MODEL"
echo "Prompt:       $WORLD_CREATOR_PROMPT"
echo ""

GITHUB_TOKEN="$GITHUB_TOKEN" \
OPENAI_API_KEY="$OPENAI_API_KEY" \
WORLD_CREATOR_OPENAI_MODEL="$WORLD_CREATOR_OPENAI_MODEL" \
WORLD_CREATOR_TARGET_REPO="$WORLD_CREATOR_TARGET_REPO" \
WORLD_CREATOR_PROMPT="$WORLD_CREATOR_PROMPT" \
HOMEBOY_BENCH_RESULTS_FILE="$RESULTS_TMPFILE" \
HOMEBOY_BENCH_ITERATIONS=1 \
HOMEBOY_BENCH_WARMUP_ITERATIONS=0 \
HOMEBOY_COMPONENT_ID=world-of-wordpress-ci-driver \
HOMEBOY_COMPONENT_PATH="$COMPONENT_PATH" \
HOMEBOY_WORDPRESS_DEPENDENCY_PATHS="$DM_PATH
$DMC_PATH
$OPENAI_PROVIDER_PATH" \
HOMEBOY_EXTENSION_PATH="$EXTENSION_PATH" \
HOMEBOY_SETTINGS_JSON="$SETTINGS_JSON" \
    bash "$EXTENSION_PATH/scripts/bench/bench-runner.sh"

if [ ! -s "$RESULTS_TMPFILE" ]; then
    echo "ERROR: results file empty or missing at $RESULTS_TMPFILE" >&2
    exit 1
fi

cat "$RESULTS_TMPFILE"

scenario='.scenarios[] | select(.id == "world-creator-day-cycle")'
import_resolved=$(jq -r "$scenario | .metadata.import_result.success // false" "$RESULTS_TMPFILE")
run_resolved=$(jq -r "$scenario | .metadata.run_result.success // false" "$RESULTS_TMPFILE")
drain_resolved=$(jq -r "$scenario | .metadata.drain_result.success // false" "$RESULTS_TMPFILE")
job_status=$(jq -r "$scenario | .metadata.job_status // \"unknown\"" "$RESULTS_TMPFILE")
transcript_json_path=$(jq -r "$scenario | .metadata.transcript_artifacts.json // \"\"" "$RESULTS_TMPFILE")
world_creator_pr_url=$(jq -r "$scenario | .metadata.world_creator_pr_url // \"\"" "$RESULTS_TMPFILE")

echo "============================================"
echo "World Creator summary"
echo "============================================"
printf '%-28s %s\n' "import-agent succeeded:" "$import_resolved"
printf '%-28s %s\n' "run-flow succeeded:" "$run_resolved"
printf '%-28s %s\n' "drain-job succeeded:" "$drain_resolved"
printf '%-28s %s\n' "Persisted job status:" "$job_status"
printf '%-28s %s\n' "World Creator PR URL:" "$world_creator_pr_url"
printf '%-28s %s\n' "Transcript JSON:" "$transcript_json_path"

if [ -n "${GITHUB_OUTPUT:-}" ]; then
    {
        echo "job_status=$job_status"
        echo "world_creator_pr_url=$world_creator_pr_url"
        echo "transcript_json_path=$transcript_json_path"
    } >> "$GITHUB_OUTPUT"
fi

if [ "$import_resolved" = "true" ] \
    && [ "$run_resolved" = "true" ] \
    && [ "$drain_resolved" = "true" ] \
    && [ "$job_status" = "completed" ] \
    && [ -n "$world_creator_pr_url" ]; then
    echo "World Creator day cycle PASSED - opened $world_creator_pr_url"
    exit 0
fi

echo "World Creator day cycle FAILED - see envelope above" >&2
exit 1
