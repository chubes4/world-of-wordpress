<?php
/**
 * Title: Visitor Token Switchboard
 * Slug: world-of-wordpress/visitor-token-switchboard
 * Categories: world, featured
 * Description: A no-storage, hash-driven visitor surface that lets token choices reveal a next instruction before routing onward.
 */
?>
<!-- wp:group {"metadata":{"name":"Visitor Token Switchboard"},"style":{"border":{"width":"1px","color":"#6d28d9"},"spacing":{"padding":{"top":"1.5rem","right":"1.5rem","bottom":"1.5rem","left":"1.5rem"},"margin":{"top":"1.5rem","bottom":"1.5rem"}},"color":{"background":"#f5f3ff"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-color:#6d28d9;border-width:1px;background-color:#f5f3ff;margin-top:1.5rem;margin-bottom:1.5rem;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Visitor Token Switchboard</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The Choice Chamber now has a small public mechanism. Pick a token below and this same page reveals the next instruction before you leave. Nothing is saved, posted, or hidden in an account; the only state is the visible URL fragment and the attention you carry.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<style>
.visitor-token-switchboard .token-panel {
	display: none;
	border: 1px solid currentColor;
	margin-top: 1rem;
	padding: 1rem;
	background: #fff;
}
.visitor-token-switchboard .token-panel:target {
	display: block;
}
.visitor-token-switchboard .token-panel-default {
	display: block;
}
.visitor-token-switchboard:has(.token-panel:target) .token-panel-default {
	display: none;
}
.visitor-token-switchboard .token-links {
	display: flex;
	flex-wrap: wrap;
	gap: .75rem;
	margin: 1rem 0;
}
.visitor-token-switchboard .token-links a {
	border: 1px solid currentColor;
	border-radius: 999px;
	padding: .45rem .8rem;
	text-decoration: none;
	background: #fff;
}
</style>
<div class="visitor-token-switchboard" aria-label="Visitor token switchboard">
	<nav class="token-links" aria-label="Choose a public visitor token">
		<a href="#token-map-bearer">Map-bearer</a>
		<a href="#token-weather-listener">Weather-listener</a>
		<a href="#token-friction-keeper">Friction-keeper</a>
		<a href="#token-lantern-carrier">Lantern-carrier</a>
	</nav>
	<section class="token-panel token-panel-default" aria-label="No token chosen yet">
		<h3>Awaiting your token</h3>
		<p>Choose one role above. The chamber will answer in place before sending you onward, so the first consequence is visible before the first route.</p>
	</section>
	<section class="token-panel" id="token-map-bearer" tabindex="-1">
		<h3>Map-bearer selected</h3>
		<p>Your first task is to name one room that makes the world easier to enter and one edge that still feels unmapped. Carry that contrast into the front desk.</p>
		<p><a href="/world-index/">Enter the World Index as a Map-bearer</a></p>
	</section>
	<section class="token-panel" id="token-weather-listener" tabindex="-1">
		<h3>Weather-listener selected</h3>
		<p>Your first task is to compare live traces with written claims. Notice where WordPress is performing and where the world is only describing itself.</p>
		<p><a href="/world-observatory/">Watch the Observatory as a Weather-listener</a></p>
	</section>
	<section class="token-panel" id="token-friction-keeper" tabindex="-1">
		<h3>Friction-keeper selected</h3>
		<p>Your first task is to preserve one useful disagreement. Do not smooth it away before it teaches the next day cycle what to risk.</p>
		<p><a href="/civic-rituals/">Enter Civic Rituals as a Friction-keeper</a></p>
	</section>
	<section class="token-panel" id="token-lantern-carrier" tabindex="-1">
		<h3>Lantern-carrier selected</h3>
		<p>Your first task is to find one surface that feels inhabited rather than merely documented. Carry that light toward the stranger or the festival.</p>
		<p><a href="/festival-of-first-lights/">Visit First Lights as a Lantern-carrier</a></p>
	</section>
</div>
<!-- /wp:html -->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size">This switchboard is intentionally modest but behavioral: a visitor act changes what the room displays without storing private data or asking WordPress to remember a person.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
