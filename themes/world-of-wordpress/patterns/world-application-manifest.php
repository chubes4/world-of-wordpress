<?php
/**
 * Title: World Application Manifest
 * Slug: world-of-wordpress/world-application-manifest
 * Categories: world, featured
 * Description: A public contract panel that exposes the World of WordPress application map through a read-only REST-backed manifest.
 */
?>
<!-- wp:group {"metadata":{"name":"World Application Manifest"},"align":"wide","style":{"border":{"width":"1px","color":"#0f766e"},"spacing":{"padding":{"top":"1.5rem","right":"1.5rem","bottom":"1.5rem","left":"1.5rem"},"margin":{"top":"2rem","bottom":"2rem"}},"color":{"background":"#f0fdfa"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-background" style="border-color:#0f766e;border-width:1px;background-color:#f0fdfa;margin-top:2rem;margin-bottom:2rem;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">World Application Manifest</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The runtime weather says what engine is awake. This manifest says what the world is, which public surfaces are intentional, which read-only interfaces exist, and which privacy promises the application refuses to cross.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<style>
.world-application-manifest,
.world-application-manifest-live {
	display: grid;
	gap: 1rem;
}
.world-application-manifest-live .manifest-grid {
	display: grid;
	gap: 1rem;
	grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
}
.world-application-manifest-live .manifest-card,
.world-application-manifest .manifest-note,
.world-application-manifest-live .manifest-promises {
	background: #fff;
	border: 1px solid #5eead4;
	padding: 1rem;
}
.world-application-manifest-live .manifest-card-primary {
	border-color: #0f766e;
}
.world-application-manifest-live .manifest-card h3,
.world-application-manifest-live .manifest-promises h3 {
	margin-top: 0;
}
.world-application-manifest-live .manifest-card ul {
	padding-left: 1.1rem;
}
.world-application-manifest-live .manifest-card li + li {
	margin-top: .45rem;
}
.world-application-manifest-live .manifest-pill-row {
	display: flex;
	flex-wrap: wrap;
	gap: .5rem;
	margin: .75rem 0;
}
.world-application-manifest-live .manifest-pill-row span {
	background: #ccfbf1;
	border: 1px solid #14b8a6;
	font-size: .82rem;
	padding: .3rem .5rem;
}
.world-application-manifest-live .manifest-rest-echo {
	background: #042f2e;
	border: 1px solid #0f766e;
	color: #ccfbf1;
	padding: 1rem;
}
.world-application-manifest-live .manifest-rest-echo h3 {
	color: #f0fdfa;
	margin-top: 0;
}
.world-application-manifest-live .manifest-rest-echo pre {
	background: #022c22;
	border: 1px solid #115e59;
	color: #ccfbf1;
	overflow-x: auto;
	padding: .85rem;
	white-space: pre-wrap;
}
</style>
<!-- /wp:html -->

<!-- wp:group {"metadata":{"name":"Manifest readout"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group world-application-manifest" aria-label="World of WordPress application manifest dashboard"><!-- wp:shortcode -->
[world_application_manifest]
<!-- /wp:shortcode -->

<!-- wp:html -->
<div class="manifest-note">
	<strong>Operator note:</strong> keep this manifest hand-authored until a public registry is worth the complexity. When a surface becomes a real entrypoint or a REST route becomes a real interface, add it here and keep the privacy boundary boring.
</div>
<!-- /wp:html --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
