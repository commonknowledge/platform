<?php

register_block_pattern(
    'platformlondon/banner-link',
    array(
        'title'       => "Banner Link",
        'description' => "A full-width section with a title and description that links to another part of the website",
        'content'     => <<<EOF
        <!-- wp:columns -->
        <div class="wp-block-columns banner-link">
            <!-- wp:column -->
            <div class="wp-block-column">
                <!-- wp:heading {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|30"}},"typography":{"lineHeight":"1.5"}},"fontSize":"x-large"} -->
                <h2 class="wp-block-heading has-x-large-font-size" style="margin-bottom:var(--wp--preset--spacing--30);line-height:1.5">TITLE</h2>
                <!-- /wp:heading -->
        
                <!-- wp:paragraph -->
                <p>...description...</p>
                <!-- /wp:paragraph --></div>
            <!-- /wp:column -->
        
            <!-- wp:column -->
            <div class="wp-block-column">
                <!-- wp:paragraph -->
                <p><a href="http://localhost:8082/projects/" data-type="page" data-id="38">Choose a page to link to →</a></p>
                <!-- /wp:paragraph --></div>
            <!-- /wp:column -->
        </div>
        <!-- /wp:columns -->
EOF
    )
);
