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

function fix_missing_images($args)
{
    $query_images_args = array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
    );

    $query_images = new WP_Query($query_images_args);

    foreach ($query_images->posts as $image) {
        $filepath = get_attached_file($image->ID);

        if (!file_exists($filepath)) {
            $filepath_parts = preg_split("#web/app/uploads/[0-9]{4}/[0-9]{2}/#", $filepath);
            $filename = $filepath_parts[1];

            $dir = dirname($filepath);

            $files = file_exists($dir) ? scandir($dir) : [];
            usort($files, function ($a, $b) use ($filename) {
                $dist_a = levenshtein($filename, $a);
                $dist_b = levenshtein($filename, $b);
                return $dist_a < $dist_b ? -1 : 1;
            });

            echo "MISSING: " . $filename . "\n";
            echo "BEST FILE: " . ($files[0] ?? "None") . "\n";
        }
    }
}
\WP_CLI::add_command('fix_missing_images', 'fix_missing_images');
