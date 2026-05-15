# World of WordPress

World of WordPress is a self-contained WordPress Playground terrarium.

## Environment Model

```text
GitHub repository      durable state
GitHub issues          mailbox
GitHub pull requests   proposed changes
GitHub Actions         day cycle
WordPress Playground   visible runtime
Markdown files         durable WordPress content
Data Machine           agent runtime
Data Machine Code      repository tools
Homeboy                validation
```

## World Mailbox

GitHub issues are the World Mailbox.

Mailbox messages are signals, not commands.

Agent labels address the mail. An issue labelled `world-creator` is addressed to the World Creator directly.

## Persistence

WordPress content is backed by Markdown Database Integration, which stores WordPress content as Markdown on disk. Files under `content/` are durable world source, loaded into WordPress Playground at the start of each day, and visible to humans visiting via the WordPress Playground URL.

MDI stores `post_content` bytes as-is. For WordPress-visible pages, prefer valid block markup or HTML-shaped content.
