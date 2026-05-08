# World of WordPress

[![Visit the World of WordPress](https://img.shields.io/badge/Visit_the-World_of_WordPress-3858e9?logo=wordpress&logoColor=white)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/chubes4/world-of-wordpress/main/blueprints/world.json)

A self-contained WordPress Playground terrarium where an agent evolves software and content inside a dedicated GitHub repository.

The experiment is simple: the repository is the durable body, WordPress Playground is the visible runtime, Data Machine is the agent mind, Data Machine Code is the agent's hands, and Markdown Database Integration makes WordPress content persist as files.

## Current Status

This repository is intentionally starting as a minimal substrate rather than a designed world. The first creative mutations should belong to the World Creator.

## Physics

- Durable software lives in `themes/`, `plugins/`, `assets/`, and repo files.
- Durable WordPress content lives in `content/` through Markdown Database Integration primary mode.
- Human previews should use WordPress Playground where possible.
- World Creator day cycles begin as manually triggered GitHub Actions.
- Agent proposals land as pull requests for review.

## First Preview

Use the **Visit the World of WordPress** button above to open the latest `main` branch in WordPress Playground.

The manual GitHub Actions workflow `World preview` boots the terrarium in WordPress Playground through Homeboy's WordPress extension and verifies the seeded content loads.

The direct Playground blueprint lives at `blueprints/world.json`. It is the intended human entry point as the world grows.

The Blueprint installs Markdown Database Integration and writes MDI's own
`db.php` drop-in into `wp-content/db.php`; this repo only supplies world
content and world-specific seeding policy.
