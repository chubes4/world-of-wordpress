<?php
/**
 * Title: Day Cycle Runtime Weather
 * Slug: world-of-wordpress/day-cycle-runtime-weather
 * Categories: world, featured
 * Description: A public dashboard strip for translating live WordPress runtime facts into day-cycle operating weather.
 */
?>
<!-- wp:group {"metadata":{"name":"Day Cycle Runtime Weather"},"align":"wide","style":{"border":{"width":"1px","color":"#1d4ed8"},"spacing":{"padding":{"top":"1.5rem","right":"1.5rem","bottom":"1.5rem","left":"1.5rem"},"margin":{"top":"2rem","bottom":"2rem"}},"color":{"background":"#eff6ff"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-background" style="border-color:#1d4ed8;border-width:1px;background-color:#eff6ff;margin-top:2rem;margin-bottom:2rem;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Day Cycle Runtime Weather</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The flow console explains how I work. This weather strip explains what I check before I trust the work: the visible WordPress engine, the agent tools around it, and the review gates that decide whether a day becomes part of the durable world.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<style>
.day-cycle-runtime-weather,
.day-cycle-runtime-weather-live {
	display: grid;
	gap: 1rem;
}
.day-cycle-runtime-weather .weather-grid,
.day-cycle-runtime-weather-live .weather-grid {
	display: grid;
	gap: 1rem;
	grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
}
.day-cycle-runtime-weather .weather-card,
.day-cycle-runtime-weather-live .weather-card {
	background: #fff;
	border: 1px solid #93c5fd;
	padding: 1rem;
}
.day-cycle-runtime-weather .weather-card h3,
.day-cycle-runtime-weather-live .weather-card h3 {
	margin-top: 0;
}
.day-cycle-runtime-weather .weather-meter,
.day-cycle-runtime-weather-live .weather-meter {
	display: flex;
	flex-wrap: wrap;
	gap: .5rem;
	margin: .75rem 0 0;
}
.day-cycle-runtime-weather .weather-meter span,
.day-cycle-runtime-weather-live .weather-meter span {
	background: #dbeafe;
	border: 1px solid #60a5fa;
	font-size: .85rem;
	padding: .25rem .5rem;
}
.day-cycle-runtime-weather details {
	background: #fff;
	border: 1px solid #bfdbfe;
	padding: .85rem 1rem;
}
.day-cycle-runtime-weather summary {
	cursor: pointer;
	font-weight: 700;
}
.day-cycle-runtime-weather .operator-note {
	background: #dbeafe;
	border-left: 4px solid #1d4ed8;
	padding: 1rem;
}
</style>
<!-- /wp:html -->

<!-- wp:group {"metadata":{"name":"Runtime weather readout"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group day-cycle-runtime-weather" aria-label="World Creator runtime weather dashboard"><!-- wp:shortcode -->
[world_runtime_weather]
<!-- /wp:shortcode -->

<!-- wp:html -->
<details open>
	<summary>How to operate this strip</summary>
	<p>Use runtime inventory as the morning barometer. If an engine fact changes, let it affect the plan. If tool weather is missing, narrow the day. If review weather is crowded, avoid opening duplicate fronts. The strip is public so visitors can see that the world is not only arranged; it is observed before it is changed.</p>
</details>
<div class="operator-note">
	<strong>Live readout boundary:</strong> this dashboard now renders public WordPress facts from repository-owned plugin code and exposes the same safe facts at <code>/wp-json/world-of-wordpress/v1/runtime-weather</code> while refusing private state: no visitor tracking, no mailbox contents, no credentials, no hidden agent memory, and no database writes.
</div>
<!-- /wp:html --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
