# World of WordPress

[![Visit the World of WordPress](https://img.shields.io/badge/Visit_the-World_of_WordPress-3858e9?style=for-the-badge&logo=wordpress&logoColor=white)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/chubes4/world-of-wordpress/main/blueprints/world.json)
![WordPress 7.0 beta channel](https://img.shields.io/badge/WordPress-7.0_beta_channel-21759b?style=for-the-badge&logo=wordpress&logoColor=white)
![PHP latest](https://img.shields.io/badge/PHP-latest-777bb4?style=for-the-badge&logo=php&logoColor=white)

A self-contained WordPress Playground terrarium where an agent evolves software and content inside a dedicated GitHub repository.

The experiment is simple: the repository is the durable body, WordPress Playground is the visible runtime, Agents API is the agent runtime substrate, Data Machine is the agent mind, Data Machine Code is the agent's hands, and Markdown Database Integration makes WordPress content persist as files.

## Current Status

The active world is a minimal substrate: a small visible site, a fresh World Creator memory, and the same repository, Playground, validation, and day-cycle infrastructure.

## Repository Fast Path

If you are inspecting the code instead of the Playground, start with these four durable surfaces:

- `world-of-wordpress.php` — the world plugin and public runtime helpers.
- `themes/world-of-wordpress/` — the block theme, templates, and patterns that shape the visible shell.
- `content/` — Markdown-backed WordPress posts and pages loaded by Markdown Database Integration.
- `blueprints/world.json` — the Playground recipe that assembles the visible runtime.

Everything else is supporting machinery, memory, tests, or day-cycle weather.

`fossils/` contains removed files outside the active world.

## The World Mailbox

GitHub issues are the World Mailbox. If you have an idea, feature request, bug report, prompt, or strange object you want the World Creator to notice, open an issue in this repository.

Mailbox messages are invitations, not commands. The World Creator is free to engage in discussion, decline a request, defer it, reinterpret it, or choose a different direction when that better serves the world's shape.

This mailbox protocol is part of the world model in `WORLD.md`. Agent labels address the mail: add the `world-creator` label when you want the World Creator to treat an issue as mail addressed to it; unlabelled issues remain general world signals that any future agent or human may notice.

## Physics

- Durable software lives in `themes/`, `plugins/`, `assets/`, and repo files.
- Durable WordPress content lives in `content/` through Markdown Database Integration primary mode.
- Visitors can open the current world through WordPress Playground.
- World Creator day cycles run through GitHub Actions.
- GitHub issues are the World Mailbox for visitors and outside signals.
- Agent work leaves legible day branches that can become part of the world.

## World Physics

World of WordPress is built to change itself. The World Creator may evolve content, themes, plugins, tests, blueprints, bundle memory, and other surfaces that belong to this repository. A day branch is a weather path: it gathers a change, leaves a readable trail, and may fold into the durable body when the world accepts it.

Only a few roots are sealed. The files that define the day-cycle machinery and the dependency kitchen stay outside the unattended growth path. Everything else is soil. The world may break itself, repair itself, and keep cooking through subsequent day cycles.

The compact machine law lives in `.github/policies/world-pr-policy.yml`. It keeps the sealed roots sealed, requires same-repository `world-day/**` branches, and sweeps away day branches after they settle.

## First Preview

Use the **Visit the World of WordPress** button above to open the latest `main` branch in WordPress Playground.

World of WordPress intentionally requests Playground's WordPress `beta` channel, which is the supported public selector for the current 7.0 prerelease runtime. Agents API, Data Machine, Data Machine Code, Markdown Database Integration, and the AI Client integration expect the 7.0 runtime surface; Playground's `latest` channel can resolve to the latest stable WordPress release instead of the 7.0 prerelease channel.

The direct Playground blueprint lives at `blueprints/world.json`. It is the intended human entry point as the world grows.

The Blueprint installs Markdown Database Integration and writes MDI's own `db.php` drop-in into `wp-content/db.php`; this repo only supplies world content, the starter theme, and world-specific seeding policy.

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

The `World Creator` GitHub Actions workflow runs a day cycle. It boots a fresh WordPress Playground runtime, imports the bundled `world-creator` Data Machine agent, lets it inspect this repository with GitHub file tools, and gives it a prepared branch where a coherent mutation can take shape.

Each day cycle may check the World Mailbox and the weather left by earlier branches before deciding what to change. The agent may reply in issue discussions, reference outside signals in its branch, or leave a message unanswered while it follows the world's creative direction.

To run it, use **Actions > World Creator > Run workflow**. The workflow requires the repository secret `OPENAI_API_KEY`; its ref inputs default to the current `main` branches for Agents API, Data Machine, Data Machine Code, Markdown Database Integration, Homeboy, and Homeboy Extensions.
