# World of WordPress

[![Visit the World of WordPress](https://img.shields.io/badge/Visit_the-World_of_WordPress-3858e9?logo=wordpress&logoColor=white)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/chubes4/world-of-wordpress/main/blueprints/world.json)

A self-contained WordPress Playground terrarium where an agent evolves software and content inside a dedicated GitHub repository.

The experiment is simple: the repository is the durable body, WordPress Playground is the visible runtime, Agents API is the agent runtime substrate, Data Machine is the agent mind, Data Machine Code is the agent's hands, and Markdown Database Integration makes WordPress content persist as files.

## Current Status

This repository is intentionally starting as a minimal substrate rather than a designed world. The first creative mutations belong to the World Creator and land as reviewable pull requests.

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

## Substrate

World of WordPress combines a small set of reusable projects:

- [WordPress Playground](https://github.com/WordPress/wordpress-playground) is the browser runtime.
- [Agents API](https://github.com/Automattic/agents-api) provides the WordPress-native agent runtime substrate.
- [Markdown Database Integration](https://github.com/Automattic/markdown-database-integration) persists WordPress content as files.
- [Data Machine](https://github.com/Extra-Chill/data-machine) imports and runs the World Creator agent bundle.
- [Data Machine Code](https://github.com/Extra-Chill/data-machine-code) provides GitHub tools for repository mutations and pull requests.
- [Homeboy Extensions](https://github.com/Extra-Chill/homeboy-extensions) provides the Playground validation harness used by CI.
- [AI Provider for OpenAI](https://github.com/WordPress/ai-provider-for-openai) supplies the OpenAI provider used by the workflow.

## World Creator

The `World Creator` GitHub Actions workflow runs a manual day cycle. It boots a
fresh WordPress Playground runtime, imports the bundled `world-creator` Data
Machine agent, lets it inspect this repository with GitHub file tools, and
expects it to open a pull request for a coherent mutation.

To run it, use **Actions > World Creator > Run workflow**. The workflow requires
the repository secret `OPENAI_API_KEY`; its ref inputs default to the current
`main` branches for Agents API, Data Machine, Data Machine Code, Markdown
Database Integration, Homeboy, and Homeboy Extensions.
