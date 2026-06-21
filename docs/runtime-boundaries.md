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
  Data Machine memory and seeds MDI-backed content when the runtime boots.
- World Creator PR capture uses the wrapper's tool recorder and engine-data
  projection contract.
- `tests/playground-ci/component/world-of-wordpress-ci-driver.php` duplicates a
  small amount of Data Machine memory registration for CI-only bootstrapping.
- `plugins/world-of-wordpress/inc/world-ability-atlas.php` reports
  namespace-level ability counts for public runtime introspection.

## Day-Cycle Inputs

World of WordPress supplies these domain inputs to the day-cycle wrapper:

- the repo and branch policy for day branches;
- the World Creator bundle source;
- the world bootstrap and preview probe files;
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

## Guardrails

- World Creator PR capture uses the shared wrapper's declarative
  recorder/projection contract.
- Public runtime introspection reports namespace-level or purpose-level
  summaries.
- The public blueprint and day-cycle wrapper assemble the agent runtime.
- `blueprints/` and `.github/` changes stay human-reviewed runtime surfaces.
