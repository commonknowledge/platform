<?php

require_once("src/utils.php");

add_action('init', function () {
    require_once("src/post_types.php");
    require_once("src/taxonomies.php");
});

add_action('carbon_fields_register_fields', function () {
    require_once("src/fields/background_images.php");
    require_once("src/fields/member.php");
    require_once("src/fields/project.php");
    require_once("src/fields/related_content.php");
    require_once("src/fields/resource.php");
    require_once("src/fields/timeline_item.php");

    require_once("src/blocks/background_images.php");
    require_once("src/blocks/banner_link.php");
    require_once("src/blocks/image_pair.php");
    require_once("src/blocks/member_position.php");
    require_once("src/blocks/platform_illustration.php");
    require_once("src/blocks/post_details.php");
    require_once("src/blocks/post_details_footer.php");
    require_once("src/blocks/post_download_link.php");
    require_once("src/blocks/post_filter.php");
    require_once("src/blocks/post_header.php");
    require_once("src/blocks/project_dates.php");
    require_once("src/blocks/project_header.php");
    require_once("src/blocks/search.php");
    require_once("src/blocks/timeline_entries.php");
});

add_action('pre_get_posts', function ($query) {
    // Ignore special parameters in block editor
    if ($query->get("s") === ':all' || $query->get("s") === ':related') {
        $query->set("s", "");
        return $query;
    }
    if ($query->is_search()) {
        $slugs_to_exclude = ["blog", "resources", "projects", "timeline"];
        $ids_to_exclude = array_map(function ($slug) {
            return url_to_postid($slug);
        }, $slugs_to_exclude);
        $query->set("post__not_in", $ids_to_exclude);
        $sort_order = $_GET["sort"] ?? "";
        $query->set("orderby", [
            'date' => strtoupper($sort_order),
        ]);
        $member_param = $_GET["member"] ?? null;
        if ($member_param) {
            $member_ids = $member_param ? explode(",", $member_param) : [];
            $meta_query = $query->get("meta_query") ?: [];
            $meta_query[] = [
                "key" => "members",
                "compare" => "IN",
                "value" => array_map(function ($id) {
                    return "post:pl_member:$id";
                }, $member_ids)
            ];
            $query->set("meta_query", $meta_query);
        }
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

add_filter("the_permalink", function ($termlink, $term, $taxonomy) {
    $taxonomy_param = $taxonomy === "category" ? "category_name" : $taxonomy;
    return "/?s=&$taxonomy_param={$term->slug}";
}, 10, 3);

add_filter('post_type_link', function ($post_link, $post) {
    if ($post->post_type == 'pl_member') {
        $post_link = '/?s=&member=' . $post->ID;
    }
    return $post_link;
}, 10, 2);

add_filter("term_link", function ($termlink, $term, $taxonomy) {
    $taxonomy_param = $taxonomy === "category" ? "category_name" : $taxonomy;
    return "/?s=&$taxonomy_param={$term->slug}";
}, 10, 3);

add_filter("query_loop_block_query_vars", function ($query) {
    // If the search parameter has the special value ":all", don't filter
    // This is used in the Carousel block
    if (($query['s'] ?? '') === ':all') {
        $query['s'] = '';
        return $query;
    }
    // Special value ":related" means prioritise posts with categories
    // that the current post has
    if (($query['s'] ?? '') === ':related') {
        $current_post = get_post();
        $query["exclude"] = [$current_post->ID];

        $explicitly_related = carbon_get_the_post_meta("related");
        $explicitly_related_ids = array_map(function ($post) {
            return $post['id'];
        }, $explicitly_related);

        $categories = wp_get_post_categories($current_post->ID);
        $related_query = $query;
        if ($categories) {
            $related_query["category__in"] = $categories;
        }
        $related_posts = get_posts($related_query);

        $other_posts = get_posts($query);
        $prioritised_posts = array_merge($related_posts, $other_posts);
        $prioritised_post_ids = array_map(function ($post) {
            return $post->ID;
        }, $prioritised_posts);

        $query["post__in"] = array_merge($explicitly_related_ids, $prioritised_post_ids);
        $query["orderby"] = "post__in";
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

// Make medium large image size available for insertion
// normally default and left out as option
// https://wordpress.stackexchange.com/questions/290259/make-medium-large-images-available-to-insert-into-post
// https://github.com/WordPress/gutenberg/issues/33010
add_filter('image_size_names_choose', function () {
    return [
        'thumbnail'    => __('Thumbnail', 'textdomain'),
        'medium'       => __('Medium', 'textdomain'),
        'medium_large' => __('Medium Large', 'textdomain'),
        'large'        => __('Large', 'textdomain'),
        'full'         => __('Full Size', 'textdomain'),
    ];
});

add_filter('render_block', function ($block_content, $block) {
    if ($block['blockName'] === 'core/navigation' &&
        !is_admin() &&
        !wp_is_json_request()
    ) {
        //return $block_content;
        return preg_replace(
            '/\<svg width(.*?)\<\/svg\>/',
            <<<EOF
            <svg width="24" height="24" 
                 xmlns="http://www.w3.org/2000/svg" 
                 viewBox="0 0 24 24" 
                 aria-hidden="true" focusable="false">
                <rect x="4" y="5" width="16" height="1.5"></rect>
                <rect x="4" y="10" width="16" height="1.5"></rect>
                <rect x="4" y="15" width="16" height="1.5"></rect>
            </svg>
EOF,
            $block_content
        );
    }

    return $block_content;
}, null, 2);
