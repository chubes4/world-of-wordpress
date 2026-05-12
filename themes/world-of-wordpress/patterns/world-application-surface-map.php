<?php
/**
 * Title: World Application Surface Map
 * Slug: world-of-wordpress/world-application-surface-map
 * Categories: world, featured
 * Description: A public grouped navigation map for registered World of WordPress application surfaces.
 */
?>
<!-- wp:group {"metadata":{"name":"World Application Surface Map"},"align":"wide","style":{"border":{"width":"1px","color":"#0f766e"},"spacing":{"padding":{"top":"1.5rem","right":"1.5rem","bottom":"1.5rem","left":"1.5rem"},"margin":{"top":"2rem","bottom":"2rem"}},"color":{"background":"#ecfdf5"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-background" style="border-color:#0f766e;border-width:1px;background-color:#ecfdf5;margin-top:2rem;margin-bottom:2rem;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">World Application Surface Map</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The registry names every public surface. The surface map groups them into navigable districts so visitors, dashboards, and future agents can route by role instead of hard-coding a shelf. It is still deliberately public and boring: surface metadata only, no visitor tracking, no private mailbox payloads, no credentials, no hidden memory, and no database writes.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<style>
.world-application-surface-map,
.world-application-surface-map-live {
	display: grid;
	gap: 1rem;
}
.world-application-surface-map-live .surface-map-grid {
	display: grid;
	gap: 1rem;
	grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
}
.world-application-surface-map-live .surface-map-card,
.world-application-surface-map-live .surface-map-group,
.world-application-surface-map .surface-map-note {
	background: #fff;
	border: 1px solid #99f6e4;
	padding: 1rem;
}
.world-application-surface-map-live .surface-map-card-primary {
	border-color: #14b8a6;
}
.world-application-surface-map-live .surface-map-card h3,
.world-application-surface-map-live .surface-map-group h3 {
	margin-top: 0;
}
.world-application-surface-map-live .surface-map-group h3 span {
	background: #ccfbf1;
	border: 1px solid #5eead4;
	font-size: .8rem;
	margin-left: .35rem;
	padding: .15rem .45rem;
}
.world-application-surface-map-live .surface-map-group ul {
	padding-left: 1.1rem;
}
.world-application-surface-map-live .surface-map-group li + li {
	margin-top: .35rem;
}
.world-application-surface-map-live .surface-map-group li span {
	color: #0f766e;
	font-size: .82rem;
}
.world-application-surface-map-live .surface-map-rest-echo {
	background: #042f2e;
	border: 1px solid #0f766e;
	color: #ccfbf1;
	padding: 1rem;
}
.world-application-surface-map-live .surface-map-rest-echo h3 {
	color: #f0fdfa;
	margin-top: 0;
}
.world-application-surface-map-live .surface-map-rest-echo pre {
	background: #022c22;
	border: 1px solid #115e59;
	color: #ccfbf1;
	overflow-x: auto;
	padding: .85rem;
	white-space: pre-wrap;
}
</style>
<!-- /wp:html -->

<!-- wp:group {"metadata":{"name":"Surface map readout"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group world-application-surface-map" aria-label="World of WordPress application surface map dashboard"><!-- wp:shortcode -->
[world_application_surface_map]
<!-- /wp:shortcode -->

<!-- wp:html -->
<div class="surface-map-note">
	<strong>Operator note:</strong> use the map when a future panel needs to offer contextual navigation. Use the focused surface endpoint when it needs one surface in detail. Keep both public and small until the world has earned a larger router.
</div>
<!-- /wp:html --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
