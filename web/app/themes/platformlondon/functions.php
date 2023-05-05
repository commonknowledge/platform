<?php

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('platformlondon', get_template_directory_uri() . '/style.css');
});

add_action('init', function () {
    register_post_type(
        'pl_project',
        array(
            'labels'      => array(
                'name'          => 'Projects',
                'singular_name' => 'Project',
            ),
            'public'      => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-megaphone',
            'rewrite' => array('slug' => 'project'),
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'),
            'taxonomies' => array('category', 'post_tag')
        )
    );
});

/**
 * Fixes shortcodes in Query Loop blocks (https://github.com/bobbingwide/fizzie/issues/28#issuecomment-1464894357)
 */
function fixed_render_block_core_shortcode($content)
{
    return do_shortcode($content);
}
add_filter('render_block_core/shortcode', 'fixed_render_block_core_shortcode', 10, 3);
add_filter('render_block_core/paragraph', 'fixed_render_block_core_shortcode', 10, 3);

add_shortcode("platform-dates", function () {
    $start_year = get_post_custom_values("start_year");
    $end_year = get_post_custom_values("end_year");
    
    $start_year = $start_year ? $start_year[0] : null;
    $end_year = $end_year ? $end_year[0] : "Ongoing";

    if ($start_year) {
        $content = "$start_year &mdash; $end_year";
    } else {
        $content = $end_year;
    }

    return '<p class="text-right mb-2">' . $content . '</p>';
});

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
                <!-- wp:heading {"style":{"typography":{"lineHeight":"1"}},"fontSize":"x-large"} -->
                <h2 class="wp-block-heading has-x-large-font-size" style="line-height:1">TITLE</h2>
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
