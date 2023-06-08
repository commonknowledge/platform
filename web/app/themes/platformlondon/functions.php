<?php

use Carbon_Fields\Block;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('carbon_fields_register_fields', function () {
    /* Page Fields and Blocks */
    Block::make(__('Platform Illustration'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Platform Illustration'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            ?>
            <div class="platform-illustration">
                <img class="platform-illustration__background" src="/app/themes/platformlondon/assets/img/platform-illustration.png" />
                <svg width="671" height="470" viewBox="0 0 671 470" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M402.593 197.593V197.659L393.476 202.933L335.78 236.34L335.714 236.373L268.735 197.626L268.647 197.681L201.812 236.373L134.877 275.12V275.164L67.8984 313.911L67.8874 313.9L10.1358 280.471L1.4043 275.417V274.91L67.8984 236.417L67.9425 236.373L134.877 197.626L201.812 158.89L268.735 120.143H268.79L402.593 197.593Z" fill="#7AD7DB"/>
                    <path d="M469.577 391.65V391.698L402.646 430.441L335.715 469.185H335.667L201.805 391.698V391.65L268.736 352.906L335.715 314.163L402.646 352.906L469.577 391.65Z" fill="#8AE167"/>
                    <path d="M670.419 275.418L536.556 352.906H536.509L469.625 391.649H469.578L402.646 352.906L335.715 314.162L402.646 275.418L469.578 314.162L536.509 275.371H536.556L603.488 236.627L670.419 275.418Z" fill="#FEA9BE"/>
                    <path d="M603.487 236.627L536.556 275.371H536.509L469.577 314.162L402.646 275.419L335.715 236.675V236.627L402.646 197.884L268.783 120.396L335.715 81.6051L402.646 120.349V120.396L469.577 159.14L536.556 197.884L603.487 236.627Z" fill="#FFA81A"/>
                    <path d="M402.646 275.419L335.715 314.162L268.736 352.906L201.805 391.65V391.697L134.873 352.954V352.906L67.8945 314.162L134.873 275.419V275.371L201.805 236.628L268.736 197.884L335.715 236.628V236.675L402.646 275.419Z" fill="#FFEF56"/>
                </svg>
            </div>
            <?php
        });

    Block::make(__('Post Filter'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Post Filter'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $categories = get_categories();
            switch (get_post()->post_name) {
                case "resources":
                    $taxonomy = "content-type";
                    $post_type_label = "Resource";
                    break;
                case "projects":
                    $taxonomy = "pl_project_type";
                    $post_type_label = "Project";
                    break;
                default:
                    $taxonomy = "pl_post_type";
                    $post_type_label = "Post";
            }
            $content_types = get_terms(["taxonomy" => $taxonomy]);

            $active_category = get_query_var("category_name");
            $active_content_type = get_query_var($taxonomy);

            $current_category_param = $active_category ? "?category_name=$active_category" : "?";
            $current_content_type_param = $active_content_type ? "?$taxonomy=$active_content_type" : "?";

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
                        All <?= $post_type_label ?> Types
                    </a>
                </li>
            <?php
            foreach ($content_types as $content_type) {
                $active_class = $active_content_type === $content_type->slug ? "active" : "";
                ?>
                    <li>
                        <a  type="button"
                            class="btn-default <?= $category->slug ?> <?= $active_class ?>"
                            href="<?= $current_category_param . $content_type_query_separator . $taxonomy . '=' . $content_type->slug ?>">
                            <?= $content_type->name ?>
                        </a>
                    </li>
                <?php
            }
            echo '</ul>';
            echo '</div>';
        });

    /* Post Fields and Blocks */
    function render_project_dates($display_active = false)
    {
        $start_year = carbon_get_the_post_meta('start_year');
        $end_year = carbon_get_the_post_meta('end_year') ?: "Ongoing";

        if ($start_year) {
            $content = "$start_year &mdash; $end_year";
        } else {
            $content = $end_year;
        }

        if ($display_active && ($end_year === "Ongoing" || $end_year == date('Y'))) {
            $content = "ACTIVE " . $content;
        }

        echo "<span class=\"text-right mb-2 uppercase\">$content</span>";
    }

    Block::make(__('Post Details'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Post Details'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $post = get_post();
            switch ($post->post_type) {
                case "post":
                    $taxonomy = "pl_post_type";
                    break;
                case "pl_project":
                    $taxonomy = "pl_project_type";
                    break;
                default:
                    $taxonomy = "content-type";
            }
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
                <?php if ($post->post_type === "pl_project") : ?>
                    <?php render_project_dates(display_active: true); ?>
                <?php else : ?>
                    <span><?= $post_date ?></span>
                    <span><?= $author ?></span>
                <?php endif ?>
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
            render_project_dates();
        });
    
    Block::make(__('Project Details'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Project Details'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $post = get_post();
            $content_types = get_the_terms($post, "pl_project_type") ?: [];
            $content_type = implode(" ", array_map(function ($term) {
                return $term->name;
            }, $content_types));

            ?>
            <div class="project-details">
                <?php if ($content_type) :
                    ?><span class="mb-2"><?= $content_type ?></span><?php
                endif;
                ?>
                <div>
                <?php render_project_dates(display_active: true); ?>
                </div>
            </div>
            <?php
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
    // If the search parameter has the special value ":all", don't filter
    // This is used in the Carousel block
    if ($query['s'] ?? '' === ':all') {
        $query['s'] = '';
        return $query;
    }
    $category_name = get_query_var("category_name");
    if ($category_name) {
        $query["category_name"] = $category_name;
    }
    foreach (["content-type", "pl_post_type", "pl_project_type"] as $taxonomy) {
        $content_type = get_query_var($taxonomy);
        if ($content_type) {
            $query["tax_query"] = [
                [
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $content_type,
                ]
            ];
        }
    }
    return $query;
});

add_action('pre_get_posts', function ($query) {
    if ($query->is_search()) {
        $slugs_to_exclude = ["blog", "resources", "projects"];
        $ids_to_exclude = array_map(function ($slug) {
            return url_to_postid($slug);
        }, $slugs_to_exclude);
        $query->set("post__not_in", $ids_to_exclude);
    }
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

    register_taxonomy('pl_project_type', ['pl_project'], [
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'project-type'],
        'labels'            => [
            'name'              => _x('Project Types', 'taxonomy general name'),
            'singular_name'     => _x('Project type', 'taxonomy singular name'),
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
