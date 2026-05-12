<?php
/**
 * Title: World Application Registry
 * Slug: world-of-wordpress/world-application-registry
 * Categories: world, featured
 * Description: A public registry panel that exposes the living World of WordPress application surfaces through a read-only REST-backed index.
 */
?>
<!-- wp:group {"metadata":{"name":"World Application Registry"},"align":"wide","style":{"border":{"width":"1px","color":"#7c2d12"},"spacing":{"padding":{"top":"1.5rem","right":"1.5rem","bottom":"1.5rem","left":"1.5rem"},"margin":{"top":"2rem","bottom":"2rem"}},"color":{"background":"#fff7ed"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-background" style="border-color:#7c2d12;border-width:1px;background-color:#fff7ed;margin-top:2rem;margin-bottom:2rem;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">World Application Registry</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The manifest names the contract. The registry names the living pieces. This surface turns the pattern shelf, shortcodes, and public REST routes into a small discovery API that future panels and agents can consume without scraping a page or guessing what already exists.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<style>
.world-application-registry,
.world-application-registry-live {
	display: grid;
	gap: 1rem;
}
.world-application-registry-live .registry-grid,
.world-application-registry-live .registry-groups {
	display: grid;
	gap: 1rem;
	grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
}
.world-application-registry-live .registry-card,
.world-application-registry-live .registry-group-card,
.world-application-registry .registry-note {
	background: #fff;
	border: 1px solid #fed7aa;
	padding: 1rem;
}
.world-application-registry-live .registry-card-primary {
	border-color: #ea580c;
}
.world-application-registry-live .registry-card h3,
.world-application-registry-live .registry-group-card h3 {
	margin-top: 0;
}
.world-application-registry-live .registry-pill-row {
	display: flex;
	flex-wrap: wrap;
	gap: .5rem;
}
.world-application-registry-live .registry-pill-row span {
	background: #ffedd5;
	border: 1px solid #fb923c;
	font-size: .82rem;
	padding: .3rem .5rem;
}
.world-application-registry-live .registry-group-card ul,
.world-application-registry-live .registry-card ul {
	padding-left: 1.1rem;
}
.world-application-registry-live .registry-group-card li + li,
.world-application-registry-live .registry-card li + li {
	margin-top: .35rem;
}
.world-application-registry-live .registry-group-card span {
	color: #9a3412;
	font-size: .82rem;
}
.world-application-registry-live .registry-rest-echo {
	background: #431407;
	border: 1px solid #9a3412;
	color: #ffedd5;
	padding: 1rem;
}
.world-application-registry-live .registry-rest-echo h3 {
	color: #fff7ed;
	margin-top: 0;
}
.world-application-registry-live .registry-rest-echo pre {
	background: #1c0a02;
	border: 1px solid #7c2d12;
	color: #ffedd5;
	overflow-x: auto;
	padding: .85rem;
	white-space: pre-wrap;
}
</style>
<!-- /wp:html -->

<!-- wp:group {"metadata":{"name":"Registry readout"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group world-application-registry" aria-label="World of WordPress application registry dashboard"><!-- wp:shortcode -->
[world_application_registry]
<!-- /wp:shortcode -->

<!-- wp:html -->
<div class="registry-note">
	<strong>Operator note:</strong> this is the first shared discovery layer. Keep it public, boring, and hand-owned until the world needs a real registry system. When a pattern, shortcode, or REST route becomes a public surface, add it here so future instruments can build on it instead of wandering blind.
</div>
<!-- /wp:html --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
