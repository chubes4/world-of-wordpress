# World of WordPress

[![Visit the World of WordPress](https://img.shields.io/badge/Visit_the-World_of_WordPress-3858e9?style=for-the-badge&logo=wordpress&logoColor=white)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/chubes4/world-of-wordpress/main/blueprints/world.json)
![WordPress 7.0 beta channel](https://img.shields.io/badge/WordPress-7.0_beta_channel-21759b?style=for-the-badge&logo=wordpress&logoColor=white)
![PHP latest](https://img.shields.io/badge/PHP-latest-777bb4?style=for-the-badge&logo=php&logoColor=white)

A self-contained WordPress Playground terrarium where an agent evolves software and content inside a dedicated GitHub repository.

<img width="1024" height="1024" alt="image" src="https://github.com/user-attachments/assets/53af01a3-f72d-4da9-af63-d03dd616be13" />

The experiment is simple: the repository is the durable body, WordPress Playground is the visible runtime, Agents API is the agent runtime substrate, Data Machine is the agent mind, Data Machine Code is the agent's hands, and Markdown Database Integration makes WordPress content persist as files.

## Repository Fast Path

If you are inspecting the code instead of the Playground, start with these durable surfaces:

- `plugins/world-of-wordpress/` — the world plugin: real, multi-file WordPress code that the agent evolves.
- `themes/world-of-wordpress/` — the block theme, templates, and patterns that shape the visible shell.
- `content/` — Markdown-backed WordPress posts and pages loaded by Markdown Database Integration.
- `bundles/world-creator/` — the agent bundle: identity, durable memory, daily memory, pipeline, and flow.
- `blueprints/world.json` — the sealed Playground recipe that assembles the visible runtime.

A thin shim at `world-of-wordpress.php` (repo root) exists so the CI dep-loader can discover the plugin; the real plugin code lives in `plugins/world-of-wordpress/`.

`fossils/` contains inert storage from a prior epoch of the world.

## Physics

- Durable software lives in `themes/`, `plugins/`, `assets/`, and repo files.
- Durable WordPress content lives in `content/` through Markdown Database Integration primary mode.
- Visitors can open the current world through WordPress Playground.
- World Creator day cycles run through GitHub Actions.
- GitHub issues are the World Mailbox for visitors and outside signals.
- Agent work leaves legible day branches that can become part of the world.

## First Preview

Use the **Visit the World of WordPress** button above to open the latest `main` branch in WordPress Playground.

World of WordPress intentionally requests Playground's WordPress `beta` channel, which is the supported public selector for the current 7.0 prerelease runtime. Agents API, Data Machine, Data Machine Code, Markdown Database Integration, and the AI Client integration expect the 7.0 runtime surface; Playground's `latest` channel can resolve to the latest stable WordPress release instead of the 7.0 prerelease channel.

The direct Playground blueprint lives at `blueprints/world.json`.

World Creator day branches cannot modify `blueprints/`.

The Blueprint installs and activates the full agent stack — Markdown Database Integration, Agents API, Data Machine, Data Machine Code, AI Provider for OpenAI, and the world plugin — and imports the World Creator bundle into Data Machine so the public Playground is the same runtime the agent wakes into during a day cycle. The visible terrarium, the agent's runtime, and the agent itself are one site.

Data Machine and Data Machine Code install from their latest GitHub Release ZIPs (the homeboy release workflow publishes vendor-bundled distributables on every release). The other plugins install from `git:directory` at their main/trunk branches because they have no runtime composer deps. The World Creator bundle is fetched from `bundles/world-creator/` on the same branch via `git:directory` and imported through the `datamachine/import-agent` ability so visitors can inspect the agent, its pipeline, its flow, and its memory in the Data Machine admin UI.

You can chat with the agent yourself inside Playground by adding your own OpenAI key in the Data Machine settings. None of it persists once the Playground tab closes.

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

The `World Creator` GitHub Actions workflow runs the bundled `world-creator` agent. The workflow requires the repository secret `OPENAI_API_KEY`; its ref inputs default to the current `main` branches for Agents API, Data Machine, Data Machine Code, Markdown Database Integration, Homeboy, and Homeboy Extensions.
