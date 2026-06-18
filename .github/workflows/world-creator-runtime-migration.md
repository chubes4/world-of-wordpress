# World Creator Runtime CI Migration

Status: blocked on an upstream full-run reusable workflow.

`World Creator` currently calls `Extra-Chill/homeboy-extensions/.github/workflows/datamachine-agent-ci.yml@main`. That workflow is still the only merged Homeboy Extensions reusable workflow that runs the complete agent lifecycle used here:

- MDI `db.php` and bootstrap/probe runtime mounts.
- Before/after PHP workload hooks.
- Daily memory support.
- Success completion outcomes without requiring a PR.
- Runtime callback/output data consumed by the merge step.
- Runner workspace PR creation and host-side PR summary/update lifecycle.
- Auto-merge policy after the World Creator PR is reported.

`Extra-Chill/homeboy-extensions/.github/workflows/runtime-agent-ci.yml@main` exists, but it only prepares and uploads a runtime config envelope. It does not run the agent, project callback/output data, assert success, update the runner workspace PR, or expose the `engine_data_json`/runtime callback data required by the merge step.

The downstream migration should proceed when Homeboy Extensions exposes a merged full-run reusable workflow with generic runtime vocabulary. At that point, preserve the current behavior in `.github/workflows/world-creator.yml` while replacing the remaining legacy caller fields:

- `wp_codebox_wordpress_version` -> `runtime_wordpress_version`
- `extra_wp_codebox_mounts` -> `runtime_mounts`
- `engine_data_outputs` -> `runtime_output_projections` or the generic runtime callback/output data contract exposed by the full-run workflow

Do not add a local shim in this repository. The reusable full-run primitive belongs upstream in Homeboy Extensions.
