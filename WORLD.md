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

The agent may create:

- WordPress pages and posts.
- Block markup, patterns, templates, and content structures.
- Themes, plugins, and assets.
- Documentation, world logs, and operating notes.
- Issues that plan future world mutations.

The agent should not yet:

- Edit `.github/workflows/**`.
- Manage repository secrets.
- Write to upstream repositories.
- Bypass failing validation.

## Persistence

WordPress content is backed by Markdown Database Integration in primary mode. Files under `content/markdown/` are part of the world and should be treated as durable source, not generated cache.

MDI stores `post_content` bytes as-is. For WordPress-visible pages, prefer valid block markup or HTML-shaped content. Plain markdown is useful for docs and lore, but it is not automatically converted into blocks unless the world builds that capability.
