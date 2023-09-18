<?php

if (!class_exists("WP_CLI")) {
    return;
}

function merge_categories($args)
{
    $cat_map = [
        "action" => "energy",
        "analysis" => "energy",
        "art" => "culture",
        "education" => "culture",
        "finance-sector" => "economy",
        "government" => "energy",
        "platformlondon" => "energy"
    ];

    $loaded_cats = [];

    foreach ($cat_map as $k => $slug) {
        $loaded_cats[$k] = [
            "from" => get_category_by_slug($k),
            "to" => get_category_by_slug($slug)
        ];
    }

    $posts = get_posts(["numberposts" => -1, "post_type" => "any"]);
    foreach ($posts as $post) {
        $categories = wp_get_post_categories($post->ID, ["fields" => "slugs"]);
        foreach ($categories as $category) {
            if (!empty($cat_map[$category])) {
                wp_remove_object_terms($post->ID, $category, "category");
                wp_add_object_terms($post->ID, $cat_map[$category], "category");
            }
        }
        if (count($categories) > 1) {
            wp_remove_object_terms($post->ID, "uncategorized", "category");
        }
    }
}
\WP_CLI::add_command('merge_categories', 'merge_categories');
