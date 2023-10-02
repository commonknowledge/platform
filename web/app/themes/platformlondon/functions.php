<?php

require_once("src/redirects.php");

# Load wp_generate_attachment_metadata function
if (!function_exists('wp_crop_image')) {
    include(ABSPATH . 'wp-admin/includes/image.php');
}

require_once("src/utils.php");

add_action('init', function () {
    require_once("src/commands.php");
    require_once("src/post_types.php");
    require_once("src/taxonomies.php");

    // Remove excerpt filtering for Member post type
    remove_filter('get_the_excerpt', 'wp_trim_excerpt');
    add_filter('get_the_excerpt', function ($text, $post) {
        if ($post->post_type !== "pl_member") {
            return wp_trim_excerpt($text, $post);
        }
        $post = get_post($post);
        $text = get_the_content('', false, $post);
        $text = strip_shortcodes($text);
        $text = apply_filters('the_content', $text);
        $text = str_replace(']]>', ']]&gt;', $text);
        return $text;
    }, 10, 2);
    add_filter('wp_trim_words', function ($text, $num_words, $more, $original_text) {
        global $post;
        if ($post->post_type === "pl_member") {
            return $original_text;
        }
        return $text;
    }, 10, 4);
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
    wp_enqueue_style(
        'platformlondon',
        get_template_directory_uri() . '/style.css',
        ver: "1.10",
    );
    wp_enqueue_script(
        'platformlondon-pre',
        get_template_directory_uri() . '/pre-script.js',
        ver: "1.10",
    );
    wp_enqueue_script(
        'platformlondon-post',
        get_template_directory_uri() . '/script.js',
        ver: "1.10",
        args: true
    );
});

add_action('wp_head', function () {
    ?>
    <script defer data-domain="platformlondon.org" src="https://plausible.io/js/script.js"></script>
    <?php
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
    $category_name = get_query_var("category_name");
    if ($category_name) {
        $query["category_name"] = $category_name;
    }

    // If the search parameter has the special value ":all", only filter
    // by category. This is used in the Carousel block
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

        if (!$explicitly_related_ids) {
            $explicitly_related_ids = [-1];
        }

        $query["post__in"] = $explicitly_related_ids;
        $query["ignore_sticky_posts"] = true;
        return $query;
    }
    foreach (["pl_resource_type", "pl_post_type", "pl_project_type"] as $taxonomy) {
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

// If an image is requested that has no sizes, generate them and update the metadata
// Fixes bug where the image size dropdown does not appear in the block editor
add_filter('rest_prepare_attachment', function ($response, $post, $request) {
    if (array_key_exists("media_details", $response->data) && empty((array) $response->data['media_details'])) {
        $path = explode("uploads", $response->data['source_url'])[1];
        $upload_dir = wp_upload_dir()['basedir'];
        $filepath = $upload_dir . $path;

        // Taken from class-wp-rest-attachments-controller::prepare_item_for_response
        $data = $response->data;
        $data['media_details'] = wp_generate_attachment_metadata($response->data['id'], $filepath);

        // Ensure empty details is an empty object.
        if (empty($data['media_details'])) {
            $data['media_details'] = new stdClass();
        } elseif (!empty($data['media_details']['sizes'])) {
            foreach ($data['media_details']['sizes'] as $size => &$size_data) {
                if (isset($size_data['mime-type'])) {
                    $size_data['mime_type'] = $size_data['mime-type'];
                    unset($size_data['mime-type']);
                }

                // Use the same method image_downsize() does.
                $image_src = wp_get_attachment_image_src($post->ID, $size);
                if (!$image_src) {
                    continue;
                }

                $size_data['source_url'] = $image_src[0];
            }

            $full_src = wp_get_attachment_image_src($post->ID, 'full');

            if (!empty($full_src)) {
                $data['media_details']['sizes']['full'] = array(
                    'file'       => wp_basename($full_src[0]),
                    'width'      => $full_src[1],
                    'height'     => $full_src[2],
                    'mime_type'  => $post->post_mime_type,
                    'source_url' => $full_src[0],
                );
            }
        } else {
            $data['media_details']['sizes'] = new stdClass();
        }
        $response->data = $data;
    }
    return $response;
}, 10, 3);

add_filter('the_title', function ($title, $id) {
    $projects_page_id = get_page_by_path('projects', OBJECT, 'page')->ID;
    if ($id === $projects_page_id) {
        $category_name = $_GET['category_name'] ?? "";
        $category = get_category_by_slug($category_name);
        if ($category) {
            return $category->name;
        }
    }
    return $title;
}, 10, 2);

add_filter('render_block', function ($block_content, $block) {
    // Change navbar icon from two bars to three bars
    if ($block['blockName'] === 'core/navigation' &&
        !is_admin() &&
        !wp_is_json_request()
    ) {
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

    // Unwrap outer <p> from Member post excerpts, because tags are not stripped (see above get_the_excerpt filter)
    if ($block['blockName'] === 'core/post-excerpt' &&
        !is_admin() &&
        !wp_is_json_request()
    ) {
        global $post;
        if ($post->post_type === "pl_member") {
            $block_content = str_replace('<p class="wp-block-post-excerpt__excerpt">', "", $block_content);
            $block_content = preg_replace("#</p></div>$#", "</div>", $block_content);
        }
    }

    // Replace cover image with Projects page metadata images
    if ($block['blockName'] === 'core/cover' &&
        !is_admin() &&
        !wp_is_json_request()
    ) {
        global $post;
        if ($post->post_name === "projects") {
            preg_match('#<img.*src="([^"]*)"#', $block_content, $matches);
            if (count($matches) !== 2) {
                return $block_content;
            }
            $category_slug = $_GET['category_name'] ?? null;
            if (in_array($category_slug, ['community', 'culture', 'economy', 'energy', 'liberation'])) {
                $image_url = '/app/themes/platformlondon/assets/img/svg/' . $category_slug . '.svg';
                $block_content = str_replace($matches[1], $image_url, $block_content);
                preg_match('#([ "])wp-block-cover([ "])#', $block_content, $cover_matches);
                $block_content = str_replace(
                    $cover_matches[0],
                    $cover_matches[1] . 'wp-block-cover wp-block-cover--' . $category_slug . $cover_matches[2],
                    $block_content
                );
            }
            return $block_content;
        }
    }

    // Hide illustration from Twitterbot (it's too big)
    if ($block['blockName'] === 'carbon-fields/platform-illustration') {
        if (str_contains($_SERVER['HTTP_USER_AGENT'], 'Twitterbot')) {
            $block_content = preg_replace('#<svg.*</svg>#s', '', $block_content);
        }
    }

    return $block_content;
}, null, 2);

// Remove custom post meta admin box
add_action('admin_menu', function () {
    remove_meta_box('postcustom', 'page', 'normal');
});


// Remove uncategorised category when another category is selected when post is saved

function platform_categories_save_post($id, $post, $update)
{
    remove_action('save_post', 'platform_categories_save_post', 10, 3);
    platform_categories_remove_uncategorized_category($id);
    add_action('save_post', 'platform_categories_save_post', 10, 3);
}
add_action('save_post', 'platform_categories_save_post', 10, 3);

function platform_categories_remove_uncategorized_category($id)
{
    $categories = get_the_category($id);
    $default = get_cat_name(get_option('default_category'));
    if (count($categories) >= 2 && in_category($default, $id)) {
        wp_remove_object_terms($id, $default, 'category');
    }
}
