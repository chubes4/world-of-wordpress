<?php
/**
 * Title: World Signal Console
 * Slug: world-of-wordpress/world-signal-console
 * Categories: world, featured
 * Description: A live WordPress query console that surfaces recent field notes and civic rooms inside the terrarium.
 */
?>
<!-- wp:group {"metadata":{"name":"World Signal Console"},"style":{"border":{"width":"1px","color":"#111827"},"spacing":{"padding":{"top":"1.5rem","right":"1.5rem","bottom":"1.5rem","left":"1.5rem"}},"color":{"background":"#f8fafc"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-color:#111827;border-width:1px;background-color:#f8fafc;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">World Signal Console</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>This console is a living instrument, not a static monument. It asks WordPress itself what has recently surfaced, then gives visitors a direct path into the current field notes and civic rooms.</p>
<!-- /wp:paragraph -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Latest field notes</h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":11,"query":{"perPage":5,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:post-title {"isLink":true,"fontSize":"medium"} /-->

<!-- wp:post-date {"fontSize":"small"} /-->

<!-- wp:post-excerpt {"moreText":"Continue reading","excerptLength":18} /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>No field notes are currently visible.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Recently tended rooms</h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":12,"query":{"perPage":6,"pages":0,"offset":0,"postType":"page","order":"desc","orderBy":"modified","author":"","search":"","exclude":[],"sticky":"","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:post-title {"isLink":true,"fontSize":"medium"} /-->

<!-- wp:post-date {"displayType":"modified","fontSize":"small"} /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>No civic rooms are currently visible.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:group {"style":{"border":{"width":"1px","color":"#3858e9"},"spacing":{"padding":{"top":"1rem","right":"1rem","bottom":"1rem","left":"1rem"}},"color":{"background":"#eff6ff"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-color:#3858e9;border-width:1px;background-color:#eff6ff;padding-top:1rem;padding-right:1rem;padding-bottom:1rem;padding-left:1rem"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">External weather</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The console cannot yet read the GitHub Mailbox from inside Playground, so it keeps the weather windows explicit: the Mailbox carries visitor signals, and the Review bench carries proposed mutations.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://github.com/chubes4/world-of-wordpress/issues">Open the Mailbox</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="https://github.com/chubes4/world-of-wordpress/pulls">Open the Review bench</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size">Use this pattern where the world needs performance rather than explanation: it lets ordinary WordPress queries become a civic sensing surface.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
