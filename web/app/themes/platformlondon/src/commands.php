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

function find_largest_version($filename, $dir)
{
    $largest_filename = null;
    $largest_width = 0;
    $filenames = scandir($dir);
    foreach ($filenames as $file) {
        $original_filename = preg_replace("#-[0-9]{3,4}x[0-9]{3,4}#", "", $file);
        if ($original_filename === $filename) {
            preg_match("#-([0-9]{3,4}x[0-9]{3,4})#", $file, $matches);
            if (count($matches) > 1) {
                $dimensions = $matches[1];
                list($width, $height) = explode("x", $dimensions);
                if ($width > $largest_width) {
                    $largest_filename = $file;
                }
            }
        }
    }
    return $largest_filename;
}

function fix_small_images($args)
{
    $uploads_path = __DIR__ . "/../../../uploads";
    $years = scandir($uploads_path);
    foreach ($years as $year) {
        $months = scandir($uploads_path . "/" . $year);
        foreach ($months as $month) {
            $dir = $uploads_path . "/" . $year . "/" . $month;
            $filenames = scandir($dir);
            foreach ($filenames as $filename) {
                $largest_version = find_largest_version($filename, $dir);
                $largest_filepath = $dir . "/" . $largest_version;
                $filepath = $dir . '/' . $filename;
                if ($largest_version) {
                    list($width, $height, $type, $attr) = getimagesize($filepath);
                    list($l_width, $height, $type, $attr) = getimagesize($largest_filepath);
                    if ($l_width > $width) {
                        echo "REPLACING " . $filename . " WITH " . $largest_version . "\n";
                        copy($largest_filepath, $filepath);
                    }
                }
            }
        }
    }
}
\WP_CLI::add_command('fix_small_images', 'fix_small_images');
