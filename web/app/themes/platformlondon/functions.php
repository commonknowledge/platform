<?php

use Carbon_Fields\Block;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('carbon_fields_register_fields', function () {
    /* Page Fields and Blocks */
    Block::make(__('Post Filter'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Post Filter'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $categories = get_categories();
            $taxonomy = get_post()->post_name === "resources" ? "content-type" : "pl_post_type";
            $content_types = get_terms(["taxonomy" => $taxonomy]);

            $active_category = get_query_var("category_name");
            $active_content_type = get_query_var("content-type");

            $current_category_param = $active_category ? "?category_name=$active_category" : "?";
            $current_content_type_param = $active_content_type ? "?content-type=$active_content_type" : "?";

            $category_query_separator = $active_content_type ? '&' : '';
            $content_type_query_separator = $active_category ? '&' : '';
            ?>
            <div class="post-filter">
                <ul class="post-filter__categories">
                <li>
                    <a  type="button" 
                        class="btn-default <?= !$active_category ? 'active' : '' ?>" 
                        href="<?= $current_content_type_param ?>">
                        All Focus Areas
                    </a>
                </li>
            <?php
            foreach ($categories as $category) {
                if ($category->name !== "Uncategorized") {
                    $active_class = $active_category === $category->slug ? "active" : "";
                    ?>
                    <li>
                        <a  type="button" 
                            class="btn-default <?= $category->slug ?> <?= $active_class ?>" 
                            href="<?= $current_content_type_param . $category_query_separator . 'category_name=' . $category->slug ?>">
                            <?= $category->name ?>
                        </a>
                    </li>
                    <?php
                }
            }
            ?>
            </ul>
            <ul class="post-filter__categories">
                <li>
                    <a  type="button" 
                        class="btn-default <?= !$active_content_type ? 'active' : '' ?>" 
                        href="<?= $current_category_param ?>">
                        All <?php echo ($taxonomy === "pl_post_type" ? "Post" : "Resource") ?> Types
                    </a>
                </li>
            <?php
            foreach ($content_types as $content_type) {
                $active_class = $active_content_type === $content_type->slug ? "active" : "";
                ?>
                    <li>
                        <a  type="button"
                            class="btn-default <?= $category->slug ?> <?= $active_class ?>"
                            href="<?= $current_category_param . $content_type_query_separator . 'content-type=' . $content_type->slug ?>">
                            <?= $content_type->name ?>
                        </a>
                    </li>
                <?php
            }
            echo '</ul>';
            echo '</div>';
        });

    /* Post Fields and Blocks */
    Block::make(__('Post Details'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Post Details'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $post = get_post();
            $taxonomy = $post->post_type === "post" ? "pl_post_type" : "content-type";
            $content_types = get_the_terms($post, $taxonomy) ?: [];
            $content_type = implode(", ", array_map(function ($term) {
                return $term->name;
            }, $content_types));
            $post_date = get_the_date('j M Y');
            $author = get_the_author();
            $author = $author ? explode(" ", $author)[0] : "";

            ?>
            <div class="post-details">
                <?php if ($content_type) :
                    ?><span><?= $content_type ?></span><?php
                endif; ?>
                <span><?= $post_date ?></span>
                <span><?= $author ?></span>
            </div>
            <?php
        });

    /* Project Fields and Blocks */
    Container::make('post_meta', 'Extra Fields')
        ->where('post_type', '=', 'pl_project')
        ->add_fields(array(
            Field::make('text', 'start_year')->set_attribute('type', 'number'),
            Field::make('text', 'end_year')->set_attribute('type', 'number'),
        ));

    Block::make(__('Project Dates'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Project Dates'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $start_year = carbon_get_the_post_meta('start_year');
            $end_year = carbon_get_the_post_meta('end_year') ?: "Ongoing";

            if ($start_year) {
                $content = "$start_year &mdash; $end_year";
            } else {
                $content = $end_year;
            }

            echo "<p class=\"text-right mb-2\">$content</p>";
        });

    /* Member Fields and Blocks */
    Container::make('post_meta', 'Extra Fields')
        ->where('post_type', '=', 'pl_member')
        ->add_fields(array(
            Field::make('text', 'position'),
        ));

    Block::make(__('Member Position'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Position'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $position = carbon_get_the_post_meta('position');

            if ($position) {
                echo "<p class=\"uppercase mb-2\">$position</p>";
            }
        });

    /* Resource Fields and Blocks */
    Container::make('post_meta', 'Extra Fields')
        ->where('post_type', '=', 'pl_resource')
        ->add_fields(array(
            Field::make('text', 'position'),
        ));
});
add_filter("query_loop_block_query_vars", function ($query) {
    $category_name = get_query_var("category_name");
    if ($category_name) {
        $query["category_name"] = $category_name;
    }
    $content_type = get_query_var("content-type");
    if ($content_type) {
        $query["tax_query"] = [
            [
                'taxonomy' => 'content-type',
                'field' => 'slug',
                'terms' => $content_type,
            ]
        ];
    }
    return $query;
});

add_action('after_setup_theme', function () {
    \Carbon_Fields\Carbon_Fields::boot();
});

// Minor edits to Carbon Fields blocks in backend
add_action('after_setup_theme', function () {
    add_theme_support('editor-styles');
    add_editor_style('style-editor.css');
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('platformlondon', get_template_directory_uri() . '/style.css');
    wp_enqueue_script(
        'platformlondon-pre',
        get_template_directory_uri() . '/pre-script.js'
    );
    wp_enqueue_script(
        'platformlondon-post',
        get_template_directory_uri() . '/script.js',
        ver: false,
        in_footer: true
    );
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
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
            'taxonomies' => array('category', 'post_tag')
        )
    );

    register_post_type(
        'pl_member',
        array(
            'labels'      => array(
                'name'          => 'Members',
                'singular_name' => 'Member',
            ),
            'public'      => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-admin-users',
            'rewrite' => array('slug' => 'member'),
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
            'taxonomies' => array()
        )
    );

    register_post_type(
        'pl_resource',
        array(
            'labels'      => array(
                'name'          => 'Resources',
                'singular_name' => 'Resource',
            ),
            'public'      => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-book',
            'rewrite' => array('slug' => 'resource'),
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
            'taxonomies' => array("category", "content-type")
        )
    );

    register_taxonomy('content-type', ['pl_resource'], [
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'content-type'],
        'labels'            => [
            'name'              => _x('Content types', 'taxonomy general name'),
            'singular_name'     => _x('Content type', 'taxonomy singular name'),
        ]
    ]);

    register_taxonomy('pl_post_type', ['post'], [
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'post-type'],
        'labels'            => [
            'name'              => _x('Post Types', 'taxonomy general name'),
            'singular_name'     => _x('Post type', 'taxonomy singular name'),
        ]
    ]);
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
