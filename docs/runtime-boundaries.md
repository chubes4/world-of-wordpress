# Runtime Boundaries

World of WordPress is intentionally small: the repository owns the world plugin,
theme, content, World Creator bundle, and day-cycle policy. The runtime comes
from the shared Homeboy Data Machine Agent CI path.

This note records the current boundary map for the World Creator day cycle.

## Current Contract

- `blueprints/world.json` assembles the public Playground runtime, installs the
  runtime plugins, writes the Markdown Database Integration `db.php` drop-in,
  and imports the World Creator bundle through `datamachine/import-agent`.
- `.github/workflows/world-creator.yml` calls the Homeboy Extensions Data Machine
  Agent CI wrapper with World Creator domain inputs, the MDI preview drop-in, and
  the world bootstrap/probe hooks.
- `plugins/world-of-wordpress/world-of-wordpress.php` registers `WORLD.md` with
  Data Machine memory and seeds MDI-backed content when the runtime provides a
  source root through `WORLD_OF_WORDPRESS_SOURCE_ROOT` or the
  `world_of_wordpress_source_root_candidates` hook. Memory metadata can be
  narrowed by the runtime through `world_of_wordpress_memory_file_metadata`.
- World Creator PR capture uses Homeboy's tool-result projection and
  engine-data output contract instead of direct Data Machine Code tool classes.
- `tests/playground-ci/component/world-of-wordpress-ci-driver.php` duplicates a
  small amount of Data Machine memory registration for CI-only bootstrapping.
- `plugins/world-of-wordpress/inc/world-ability-atlas.php` reports
  namespace-level ability counts for public runtime introspection.

## Day-Cycle Inputs

World of WordPress supplies these domain inputs to the day-cycle wrapper:

- the repo and branch policy for day branches;
- the World Creator bundle source;
- the world bootstrap and preview probe files;
- an optional named source root for repo-bundled theme, content, and `WORLD.md`;
- optional runtime filters for World memory metadata and perception directive
  modes/priority;
- the visible runtime content, plugin, theme, and public preview blueprint;
- completion policy such as whether a PR is required and which outcomes count.

The Homeboy Data Machine Agent CI wrapper provides the runtime path for:

- WP Codebox provider selection and mount mechanics;
- Data Machine bundle execution ability names;
- Data Machine Code tool classes and PR-result plumbing;
- Markdown Database Integration drop-in placement;
- Agents API installation order and runtime readiness checks.

The workflow keeps runtime assembly in `.github/workflows/world-creator.yml` and
the two workload hooks in `tests/playground-ci/workloads/`. The world plugin,
theme, content, and bundle memory stay focused on the visible world and World
Creator policy.

## Open Upstream Gap

The deployed Homeboy Data Machine Agent CI workflow still accepts hook files via
`runtime_mounts` plus `workload_run_before`/`workload_run_after`; it does not yet
expose a named workload-hook input that lets this repository avoid sandbox target
paths entirely. World of WordPress is ready for that contract through the named
source-root constant/filter and runtime-configurable memory/perception hooks, but
the workflow mount declarations should remain until the upstream wrapper exposes
named hook inputs.

## Guardrails

- World Creator PR capture uses the shared wrapper's declarative
  projection contract instead of direct Data Machine Code tool classes.
- Public runtime introspection reports namespace-level or purpose-level
  summaries.
- The public blueprint and day-cycle wrapper assemble the agent runtime.
- Runtime-specific source-root, memory, and perception details use named inputs
  or filters rather than sandbox mount path assumptions in plugin code.
- `blueprints/` and `.github/` changes stay human-reviewed sealed runtime
  surfaces.
