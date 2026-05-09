# World of WordPress

World of WordPress is a living WordPress terrarium.

The world is allowed to contain software, content, design, lore, tools, and agents. It should evolve through issues, pull requests, reviews, and merges. Its durable state is this repository. Its visible runtime is WordPress Playground.

## Environment Model

```text
GitHub repository      durable world body
GitHub issues          planning board and world requests
GitHub pull requests   proposed mutations
GitHub Actions         execution chamber
WordPress Playground   visible world runtime
Markdown files         durable WordPress content
Data Machine           agent runtime
Data Machine Code      GitHub and workspace hands
Homeboy                validation harness
```

## Creative Contract

The world should become more interesting, coherent, and inspectable over time.

Agents may create:

- WordPress pages and posts.
- Block markup, patterns, templates, and content structures.
- Themes, plugins, and assets.
- Documentation, world logs, and operating notes.
- Issues that plan future world mutations.
- Agent bundles, pipelines, and flows that add new inhabitants or routines.

The world evolves through reviewable proposals. Agents may explore the repository, plan visible mutations through issues when useful, validate the world in Playground, and propose changes for review.

## Persistence

WordPress content is backed by Markdown Database Integration in primary mode. Files under `content/` are durable world source.

MDI stores `post_content` bytes as-is. For WordPress-visible pages, prefer valid block markup or HTML-shaped content. Plain markdown is useful for docs and lore. The world can grow markdown-to-block rendering as a future capability.
