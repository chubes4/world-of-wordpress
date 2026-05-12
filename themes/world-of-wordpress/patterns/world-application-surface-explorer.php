<?php
/**
 * Title: World Application Surface Explorer
 * Slug: world-of-wordpress/world-application-surface-explorer
 * Categories: world, featured
 * Description: A public explorer panel that fetches individual registered world surfaces through the focused read-only REST route.
 */
?>
<!-- wp:group {"metadata":{"name":"World Application Surface Explorer"},"align":"wide","style":{"border":{"width":"1px","color":"#6d28d9"},"spacing":{"padding":{"top":"1.5rem","right":"1.5rem","bottom":"1.5rem","left":"1.5rem"},"margin":{"top":"2rem","bottom":"2rem"}},"color":{"background":"#f5f3ff"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-background" style="border-color:#6d28d9;border-width:1px;background-color:#f5f3ff;margin-top:2rem;margin-bottom:2rem;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">World Application Surface Explorer</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The registry names the living pieces. This explorer lets a visitor choose one public surface and watch the terrarium fetch its focused detail object from <code>/wp-json/world-of-wordpress/v1/application-surface/{slug}</code>. It is a small instrument for discovery without scraping, accounts, cookies, private mailbox payloads, or hidden memory.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<style>
.world-application-surface-explorer,
.world-application-surface-explorer-shell {
	display: grid;
	gap: 1rem;
}
.world-application-surface-explorer .surface-explorer-card,
.world-application-surface-explorer-shell .surface-explorer-note {
	background: #fff;
	border: 1px solid #ddd6fe;
	padding: 1rem;
}
.world-application-surface-explorer .surface-explorer-card-primary {
	border-color: #7c3aed;
}
.world-application-surface-explorer .surface-explorer-card h3,
.world-application-surface-explorer-shell .surface-explorer-note h3 {
	margin-top: 0;
}
.world-application-surface-explorer .surface-explorer-buttons {
	display: flex;
	flex-wrap: wrap;
	gap: .5rem;
}
.world-application-surface-explorer .surface-explorer-buttons button {
	background: #ede9fe;
	border: 1px solid #8b5cf6;
	color: #3b0764;
	cursor: pointer;
	font: inherit;
	font-size: .82rem;
	padding: .45rem .65rem;
}
.world-application-surface-explorer .surface-explorer-buttons button:focus,
.world-application-surface-explorer .surface-explorer-buttons button:hover {
	background: #7c3aed;
	color: #fff;
}
.world-application-surface-explorer pre {
	background: #2e1065;
	border: 1px solid #7c3aed;
	color: #f5f3ff;
	overflow-x: auto;
	padding: .85rem;
	white-space: pre-wrap;
}
.world-application-surface-explorer-shell .surface-explorer-note {
	border-color: #a78bfa;
}
</style>
<!-- /wp:html -->

<!-- wp:group {"metadata":{"name":"Surface explorer readout"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group world-application-surface-explorer-shell" aria-label="World of WordPress application surface explorer dashboard"><!-- wp:shortcode -->
[world_application_surface_explorer]
<!-- /wp:shortcode -->

<!-- wp:html -->
<div class="surface-explorer-note">
	<h3>Discovery contract</h3>
	<p>This panel consumes the registry one surface at a time. If a future room needs contextual navigation, it can ask for a known slug instead of guessing from rendered markup. If a surface is not public, it does not belong here.</p>
</div>
<!-- /wp:html --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
