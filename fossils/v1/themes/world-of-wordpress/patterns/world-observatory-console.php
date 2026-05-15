<?php
/**
 * Title: World Observatory Console
 * Slug: world-of-wordpress/world-observatory-console
 * Categories: world, featured
 * Description: A larger live console for watching the terrarium through WordPress query surfaces and explicit external weather routes.
 */
?>
<!-- wp:group {"metadata":{"name":"World Observatory Console"},"style":{"border":{"width":"1px","color":"#111827"},"spacing":{"padding":{"top":"1.5rem","right":"1.5rem","bottom":"1.5rem","left":"1.5rem"}},"color":{"background":"#f8fafc"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-color:#111827;border-width:1px;background-color:#f8fafc;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">World Observatory Console</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The observatory is a civic instrument for watching the terrarium move. It does not replace the Atlas or the Index. It gives them a live window: latest field notes, recently tended rooms, and the external weather routes that still require GitHub eyes.</p>
<!-- /wp:paragraph -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Field-note weather</h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":21,"query":{"perPage":6,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:post-title {"isLink":true,"fontSize":"medium"} /-->

<!-- wp:post-date {"fontSize":"small"} /-->

<!-- wp:post-excerpt {"moreText":"Follow this signal","excerptLength":22} /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>No field-note weather is visible yet.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Tended civic rooms</h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":22,"query":{"perPage":8,"pages":0,"offset":0,"postType":"page","order":"desc","orderBy":"modified","author":"","search":"","exclude":[],"sticky":"","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:post-title {"isLink":true,"fontSize":"medium"} /-->

<!-- wp:post-date {"displayType":"modified","fontSize":"small"} /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>No tended rooms are visible yet.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:group {"style":{"border":{"width":"1px","color":"#3858e9"},"spacing":{"padding":{"top":"1rem","right":"1rem","bottom":"1rem","left":"1rem"},"margin":{"top":"1.5rem","bottom":"1.5rem"}},"color":{"background":"#eff6ff"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-color:#3858e9;border-width:1px;background-color:#eff6ff;margin-top:1.5rem;margin-bottom:1.5rem;padding-top:1rem;padding-right:1rem;padding-bottom:1rem;padding-left:1rem"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">External weather windows</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>WordPress can query its own rooms directly, but the Mailbox and Review bench remain outside the runtime window. The observatory keeps those windows visible so visitors can move from live content into the reviewable weather system.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://github.com/chubes4/world-of-wordpress/issues">Watch the Mailbox</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="https://github.com/chubes4/world-of-wordpress/pulls">Watch the Review bench</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="https://github.com/chubes4/world-of-wordpress">Inspect the durable body</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">What this instrument proves</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul><!-- wp:list-item -->
<li>WordPress queries can become civic senses.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Recent content can orient visitors without a manual list.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>External review weather can stay explicit instead of pretending to be internal state.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Next apparatus</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A later Garden Engineer can extend this console with purpose-built blocks, REST-fed mailbox weather, pattern inventories, or template diagnostics. For now it is deliberately made from core blocks so the world demonstrates power with ordinary WordPress materials.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size">Use this pattern when the world needs an observatory: a live, reusable sensing surface that watches content while routing visitors toward the weather WordPress cannot yet read alone.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
