#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../../.." && pwd)"
COMPONENT_PATH="$REPO_ROOT/tests/playground-ci/component"
BOOTSTRAP_WORKLOAD_PATH="$REPO_ROOT/tests/playground-ci/workloads/world-creator-bootstrap.php"
BUNDLE_SOURCE="$REPO_ROOT/bundles/world-creator"

EXTENSION_PATH="${HOMEBOY_EXTENSION_PATH:-/Users/chubes/Developer/homeboy-extensions/wordpress}"
WORLD_PLUGIN_PATH="${WORLD_PLUGIN_PATH:-$REPO_ROOT}"
AGENTS_API_PATH="${AGENTS_API_PATH:-/Users/chubes/Developer/agents-api}"
DM_PATH="${DM_PATH:-/Users/chubes/Developer/data-machine}"
DMC_PATH="${DMC_PATH:-/Users/chubes/Developer/data-machine-code}"
MDI_PATH="${MDI_PATH:-/Users/chubes/Developer/markdown-database-integration}"
OPENAI_PROVIDER_PATH="${OPENAI_PROVIDER_PATH:-/Users/chubes/Studio/intelligence-chubes4/wp-content/plugins/ai-provider-for-openai}"
STUDIO_SITE_PATH="${STUDIO_SITE_PATH:-/Users/chubes/Studio/intelligence-chubes4}"
WORLD_CREATOR_OPENAI_MODEL="${WORLD_CREATOR_OPENAI_MODEL:-gpt-5.5}"
WORLD_CREATOR_TARGET_REPO="${WORLD_CREATOR_TARGET_REPO:-chubes4/world-of-wordpress}"
WORLD_CREATOR_PROMPT="${WORLD_CREATOR_PROMPT:-}"

if [ ! -f "$EXTENSION_PATH/scripts/bench/bench-runner.sh" ]; then
    echo "ERROR: Homeboy WordPress extension not found at $EXTENSION_PATH" >&2
    exit 1
fi
if [ ! -d "$WORLD_PLUGIN_PATH" ] || [ ! -d "$AGENTS_API_PATH" ] || [ ! -d "$DM_PATH" ] || [ ! -d "$DMC_PATH" ] || [ ! -d "$MDI_PATH" ]; then
    echo "ERROR: World plugin, Agents API, Data Machine, Data Machine Code, and Markdown Database Integration checkouts are required" >&2
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

if [ -z "$WORLD_CREATOR_PROMPT" ]; then
    WORLD_CREATOR_PROMPT="$(jq -r '.steps[0].prompt_queue[0].prompt // empty' "$BUNDLE_SOURCE/flows/world-creator-day-cycle-flow.json")"
fi
if [ -z "$WORLD_CREATOR_PROMPT" ]; then
    echo "ERROR: World Creator prompt is required in workflow input or bundled flow prompt_queue" >&2
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

CONFIG_TMPFILE=$(mktemp "${TMPDIR:-/tmp}/world-creator-config.XXXXXX.json")
RESULTS_TMPFILE=$(mktemp "${TMPDIR:-/tmp}/world-creator-results.XXXXXX.json")
COMPONENT_BOOTSTRAP_WORKLOAD="$COMPONENT_PATH/world-creator-bootstrap.php"
COMPONENT_BUNDLE_DIR="$COMPONENT_PATH/bundles/world-creator"
TRANSCRIPT_ARTIFACT_DIR="$COMPONENT_PATH/artifacts/world-creator"

cleanup() {
    rm -f "$CONFIG_TMPFILE" "$RESULTS_TMPFILE" "$COMPONENT_BOOTSTRAP_WORKLOAD" "$COMPONENT_PATH/WORLD.md"
    rm -rf "$COMPONENT_PATH/bundles"
}
trap cleanup EXIT

rm -rf "$TRANSCRIPT_ARTIFACT_DIR"
mkdir -p "$TRANSCRIPT_ARTIFACT_DIR" "$COMPONENT_PATH/bundles"
cp "$BOOTSTRAP_WORKLOAD_PATH" "$COMPONENT_BOOTSTRAP_WORKLOAD"
cp -R "$BUNDLE_SOURCE" "$COMPONENT_BUNDLE_DIR"
cp "$REPO_ROOT/WORLD.md" "$COMPONENT_PATH/WORLD.md"

HE_AGENT_RUNNER="$EXTENSION_PATH/scripts/agent/run-datamachine-agent.sh"
if [ ! -f "$HE_AGENT_RUNNER" ]; then
    echo "ERROR: generic Data Machine agent runner not found at $HE_AGENT_RUNNER" >&2
    exit 1
fi

jq -n \
    --arg componentPath "$COMPONENT_PATH" \
    --arg worldPlugin "$WORLD_PLUGIN_PATH" \
    --arg agentsApi "$AGENTS_API_PATH" \
    --arg dm "$DM_PATH" \
    --arg dmc "$DMC_PATH" \
    --arg mdi "$MDI_PATH" \
    --arg openaiProvider "$OPENAI_PROVIDER_PATH" \
    --arg githubToken "$GITHUB_TOKEN" \
    --arg openaiKey "$OPENAI_API_KEY" \
    --arg model "$WORLD_CREATOR_OPENAI_MODEL" \
    --arg targetRepo "$WORLD_CREATOR_TARGET_REPO" \
    --arg prompt "$WORLD_CREATOR_PROMPT" \
    '{
        component_id: "world-of-wordpress-ci-driver",
        component_path: $componentPath,
        workload_id: "world-creator-day-cycle",
        workload_label: "Run World Creator day cycle",
        validation_dependencies: [$worldPlugin, $mdi, $agentsApi, $dm, $dmc, $openaiProvider],
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
        bundle_path: "/wordpress/wp-content/plugins/world-of-wordpress-ci-driver/bundles/world-creator",
        agent_slug: "world-creator",
        pipeline_slug: "world-creator-pipeline",
        flow_slug: "world-creator-day-cycle-flow",
        provider: "openai",
        model: $model,
        provider_register_function: "WordPress\\OpenAiAiProvider\\register_provider",
        provider_credentials: {
            connectors_ai_openai_api_key: "OPENAI_API_KEY"
        },
        github_token_env: "GITHUB_TOKEN",
        github_profile_id: "world-creator-ci",
        target_repo: $targetRepo,
        allowed_repos: [$targetRepo],
        daily_memory_enabled: true,
        max_turns: 16,
        prompt: $prompt,
        step_budget: 20,
        time_budget_ms: 900000,
        transcript_dir: "/wordpress/wp-content/plugins/world-of-wordpress-ci-driver/artifacts/world-creator",
        required_abilities: [
            "datamachine/import-agent",
            "datamachine/run-flow",
            "datamachine/drain-job",
            "datamachine/create-or-update-github-file",
            "datamachine/daily-memory-write"
        ],
        bench_env: {
            GITHUB_TOKEN: $githubToken,
            OPENAI_API_KEY: $openaiKey
        },
        workload_run_before: [
            { type: "php", file: "world-creator-bootstrap.php" }
        ]
    }' > "$CONFIG_TMPFILE"

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
HOMEBOY_EXTENSION_PATH="$EXTENSION_PATH" \
    bash "$HE_AGENT_RUNNER" "$CONFIG_TMPFILE"

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
transcript_json_path=$(jq -r "$scenario | .metadata.transcript_artifacts | if type == \"object\" then (.json // \"\") else \"\" end" "$RESULTS_TMPFILE")
world_creator_pr_url=$(jq -r "$scenario | .metadata.engine_data.world_creator.pr_url // \"\"" "$RESULTS_TMPFILE")

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
