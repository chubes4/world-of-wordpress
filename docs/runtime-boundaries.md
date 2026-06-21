# Runtime Boundaries

World of WordPress is intentionally small: the repository owns the world plugin,
theme, content, World Creator bundle, and day-cycle policy. The runtime substrate
comes from other projects.

This note records the current boundary map so future day-cycle work can move
toward a terrarium wrapper without growing repo-specific infrastructure APIs.

## Current Coupling

- `blueprints/world.json` assembles the public Playground runtime, installs the
  substrate plugins, writes the Markdown Database Integration `db.php` drop-in,
  and imports the World Creator bundle through `datamachine/import-agent`.
- `.github/workflows/world-creator.yml` selects the `wp-codebox` runtime provider,
  declares Data Machine / Agents API / Data Machine Code dependencies, mounts the
  MDI drop-in for CI, and asks the generic runtime workflow to execute the bundle.
- `plugins/world-of-wordpress/world-of-wordpress.php` registers `WORLD.md` with
  Data Machine memory and seeds MDI-backed content when the runtime boots.
- `tests/playground-ci/workloads/world-creator-bootstrap.php` adapts the day cycle
  by wrapping Data Machine Code's pull request tool so the workflow can recover
  the created World Creator PR URL.
- `tests/playground-ci/component/world-of-wordpress-ci-driver.php` duplicates a
  small amount of Data Machine memory registration for CI-only bootstrapping.
- `plugins/world-of-wordpress/inc/world-ability-atlas.php` exposes only safe,
  namespace-level ability counts. It does not expose ability arguments,
  credentials, user data, memory content, or private runtime state.

## Boundary Direction

The desired seam is a terrarium/day-cycle runtime wrapper owned outside this
repo. World of WordPress should supply domain inputs only:

- the repo and branch policy for day branches;
- the World Creator bundle source;
- the world bootstrap and preview probe files;
- the visible runtime content, plugin, theme, and blueprint;
- completion policy such as whether a PR is required and which outcomes count.

Substrate details should remain behind that wrapper:

- WP Codebox provider selection and mount mechanics;
- Data Machine bundle execution ability names;
- Data Machine Code tool classes and PR-result plumbing;
- Markdown Database Integration drop-in placement;
- Agents API installation order and runtime readiness checks.

Until the wrapper exists, keep direct coupling narrow, visible, and runtime-only.
Prefer one explicit bootstrap/probe file over scattering substrate calls through
the world plugin, theme, content, or bundle memory.

## Guardrails

- Do not add new direct calls to Data Machine Code tool classes from the world
  plugin. Keep them in CI/day-cycle bootstrap code or move them upstream into the
  shared wrapper.
- Do not expose raw ability names, arguments, credentials, or memory through
  public routes. Public runtime introspection should stay at namespace-level or
  purpose-level summaries.
- Do not make the world plugin responsible for installing or validating the full
  agent stack. The blueprint and day-cycle wrapper own runtime assembly.
- Keep `blueprints/` and `.github/` changes human-reviewed; unattended world-day
  PRs should continue to treat them as sealed surfaces.
