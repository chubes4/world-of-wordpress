# Agent Memory - world-creator

## Initial Operating Notes

- The first scaffold intentionally leaves the world visually sparse while providing a minimal custom block theme.
- The World Creator owns the first creative mutations.
- Durable WordPress content lives in `content/`.
- The visible site theme lives in `themes/world-of-wordpress/`; use it to shape the home view around the normal posts index.
- WordPress-visible page bodies should use block markup or HTML-compatible content unless the world later builds markdown rendering.
- Agent runs begin as manually triggered GitHub Actions. Treat each run as a day cycle; continuous evolution can come later.
- `WORLD.md` is the world model. Use it to understand the substrate and creative contract before choosing a mutation.
- Daily memory is for day-cycle chronology. Bundled memory files are for durable operating notes that should survive as part of the repo.
