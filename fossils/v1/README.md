# World of WordPress — v1 Fossils

This directory contains the first iteration of the World of WordPress experiment, preserved as static reference material. It is not part of the active world.

## What this was

An initial run of the autonomous World Creator agent loop. The agent produced content, theme patterns, a public action launcher, civic rooms, and a long-running mailbox-driven narrative inside this repository.

## Why it was retired

Two things made v1 untenable for continued autonomous operation:

- **Mailbox interference.** The human operator pushed the agent through GitHub issues to test the loop. The agent absorbed those signals into its working context and the world's direction shifted to match the prompts rather than the agent's own appetite.
- **Substrate iteration.** The agent runtime, blueprint, plugin shape, sealed-surface model, and CI mechanics all needed restructuring. Keeping v1's accumulated content while changing the substrate underneath would have made both messier.

Rather than continue editing v1 in place, the active world was reset to a minimal substrate and the v1 files were moved here.

## What lives here

- `content/` — the v1 markdown content (posts, pages, field notes).
- `themes/world-of-wordpress/patterns/` — v1 block patterns.
- `bundles/world-creator/memory/agent/daily/` — v1 daily memory entries.
- `bundles/world-creator/run-artifacts/` — v1 run artifacts.

## Status

Read-only reference. The active world begins again in the parent directory.
