# World of WordPress

[![Visit the World of WordPress](https://img.shields.io/badge/Visit_the-World_of_WordPress-3858e9?logo=wordpress&logoColor=white)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/chubes4/world-of-wordpress/main/blueprints/world.json)

A self-contained WordPress Playground terrarium where an agent evolves software and content inside a dedicated GitHub repository.

The experiment is simple: the repository is the durable body, WordPress Playground is the visible runtime, Agents API is the agent runtime substrate, Data Machine is the agent mind, Data Machine Code is the agent's hands, and Markdown Database Integration makes WordPress content persist as files.

## Current Status

This repository starts as a minimal substrate. The World Creator grows the visible world through reviewable pull requests.

## The World Mailbox

GitHub issues are the World Mailbox. If you have an idea, feature
request, bug report, prompt, or strange object you want the World Creator to
notice, open an issue in this repository.

The World Creator does not mutate the world directly. It wakes during manual day
cycles, reads the repository, open issues, open pull requests, and the live
WordPress Playground runtime, then answers by opening a pull request. Humans keep
the boundary: review, comment, merge, or close the proposal.

## Physics

- Durable software lives in `themes/`, `plugins/`, `assets/`, and repo files.
- The visible site starts with the minimal custom block theme in `themes/world-of-wordpress/`, with the front page presenting the normal posts index.
- Durable WordPress content lives in `content/` through Markdown Database Integration primary mode.
- Human previews should use WordPress Playground where possible.
- World Creator day cycles begin as manually triggered GitHub Actions.
- GitHub issues are the World Mailbox for visitors and reviewers.
- Agent proposals land as pull requests for review.

## First Preview

Use the **Visit the World of WordPress** button above to open the latest `main` branch in WordPress Playground.

The manual GitHub Actions workflow `World preview` boots the terrarium in WordPress Playground through Homeboy's WordPress extension and verifies the seeded content loads.

The direct Playground blueprint lives at `blueprints/world.json`. It is the intended human entry point as the world grows.

The Blueprint installs Markdown Database Integration and writes MDI's own
`db.php` drop-in into `wp-content/db.php`; this repo only supplies world
content, the starter theme, and world-specific seeding policy.

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

Each day cycle should check the World Mailbox for outside-world requests and open pull
requests for pending mutations before deciding what to change. The agent may
reference issues in its pull request when it is responding to a visitor request,
but it generally communicates through pull requests rather than opening issues of
its own.

To run it, use **Actions > World Creator > Run workflow**. The workflow requires
the repository secret `OPENAI_API_KEY`; its ref inputs default to the current
`main` branches for Agents API, Data Machine, Data Machine Code, Markdown
Database Integration, Homeboy, and Homeboy Extensions.
