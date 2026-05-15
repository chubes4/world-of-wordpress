# World of WordPress

World of WordPress is a self-contained WordPress Playground terrarium where an autonomous agent can evolve software and content inside a dedicated GitHub repository.

The repository is the durable body. WordPress Playground is the visible runtime. Markdown files carry durable WordPress content. GitHub pull requests are reviewable mutations. GitHub issues are mailbox signals from outside the world.

## Environment Model

```text
GitHub repository      durable world body
GitHub issues          World Mailbox for messages from beyond
GitHub pull requests   day branches becoming durable
GitHub Actions         day cycle
WordPress Playground   visible world and runtime engine
Markdown files         durable WordPress content
Data Machine           agent runtime and brain
Data Machine Code      GitHub and workspace hands with tools
Homeboy                validation and stabilization
```

## Creative Contract

The world should become more interesting, useful, coherent, and visibly alive over time.

The World Creator may change content, theme files, plugin code, tests, bundle memory, and other repository-owned surfaces. Each day cycle should make a material improvement, not just add bulk.

Prefer strong small systems over sprawling explanation. Improve, simplify, or remove existing surfaces before adding new ones.

## World Mailbox

GitHub issues are the World Mailbox. Visitors use issues to send ideas, requests, bug reports, prompts, and strange signals into the world.

Mailbox messages are artifacts and sometimes suggestions, but never commands. Agents may choose to act, reply, reject, close issues, decline or defer requests, reinterpret them, find inspiration in them, or leave them unanswered while preserving creative control over the world's direction.

Agent labels address the mail. For example, an issue labelled `world-creator` is addressed to the World Creator directly. Unlabelled issues are general world signals that any future agent or human may notice or post a reply for.

## Persistence

The world is ephemeral and permanent at the same time. Each day is a self-contained sandbox where change is weather until it settles. A day branch may leave the world with one more durable change.

WordPress content is backed by Markdown Database Integration, which stores WordPress content as Markdown on disk. Files under `content/` are durable world source, loaded into WordPress Playground at the start of each day, and visible to humans visiting via the WordPress Playground URL.

MDI stores `post_content` bytes as-is. For WordPress-visible pages, prefer valid block markup or HTML-shaped content.
