<?php

use Carbon_Fields\Block;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

const ECONOMY_SVG = '<path d="M402.593 197.593V197.659L393.476 202.933L335.78 236.34L335.714 236.373L268.735 197.626L268.647 197.681L201.812 236.373L134.877 275.12V275.164L67.8984 313.911L67.8874 313.9L10.1358 280.471L1.4043 275.417V274.91L67.8984 236.417L67.9425 236.373L134.877 197.626L201.812 158.89L268.735 120.143H268.79L402.593 197.593Z" fill="#7AD7DB"/>';
const CULTURE_SVG = '<path d="M469.577 391.65V391.698L402.646 430.441L335.715 469.185H335.667L201.805 391.698V391.65L268.736 352.906L335.715 314.163L402.646 352.906L469.577 391.65Z" fill="#8AE167"/>';
const LIBERATION_SVG = '<path d="M670.419 275.418L536.556 352.906H536.509L469.625 391.649H469.578L402.646 352.906L335.715 314.162L402.646 275.418L469.578 314.162L536.509 275.371H536.556L603.488 236.627L670.419 275.418Z" fill="#FEA9BE"/>';
const ENERGY_SVG = '<path d="M603.487 236.627L536.556 275.371H536.509L469.577 314.162L402.646 275.419L335.715 236.675V236.627L402.646 197.884L268.783 120.396L335.715 81.6051L402.646 120.349V120.396L469.577 159.14L536.556 197.884L603.487 236.627Z" fill="#FFA81A"/>';
const COMMUNITY_SVG = '<path d="M402.646 275.419L335.715 314.162L268.736 352.906L201.805 391.65V391.697L134.873 352.954V352.906L67.8945 314.162L134.873 275.419V275.371L201.805 236.628L268.736 197.884L335.715 236.628V236.675L402.646 275.419Z" fill="#FFEF56"/>';

const CATEGORY_SVGS = [
    "economy" => ECONOMY_SVG,
    "culture" => CULTURE_SVG,
    "liberation" => LIBERATION_SVG,
    "energy" => ENERGY_SVG,
    "community" => COMMUNITY_SVG,
];

function get_category_svg($slug)
{
    $category = get_category_by_slug($slug);
    if (empty(CATEGORY_SVGS[$slug])) {
        return "";
    }
    return (
        '<a href="' . get_category_link($category) . '">' .
        CATEGORY_SVGS[$slug] .
        '</a>'
    );
}

/* Util Functions */
function get_project_dates(): string
{
    $start_year = carbon_get_the_post_meta('start_year');
    $end_year = carbon_get_the_post_meta('end_year') ?: "Ongoing";

    if ($start_year) {
        $content = "$start_year &mdash; $end_year";
    } else {
        $content = $end_year;
    }
    return $content;
}

function is_project_active(): bool
{
    $end_year = carbon_get_the_post_meta('end_year');
    return !$end_year || ((int) $end_year >= (int) date('Y'));
}

function render_project_dates($display_active = false)
{
    $content = get_project_dates();

    if ($display_active && is_project_active()) {
        $content = "ACTIVE " . $content;
    }

    echo "<span class=\"project-dates text-right uppercase\">$content</span>";
}

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
                    <?= get_category_svg("economy") ?>
                    <?= get_category_svg("culture") ?>
                    <?= get_category_svg("liberation") ?>
                    <?= get_category_svg("energy") ?>
                    <?= get_category_svg("community") ?>
                </svg>
            </div>
            <?php
        });

    Block::make('Image Pair')
        ->add_fields([
            Field::make('image', 'image_1', 'Image 1'),
            Field::make('image', 'image_2', 'Image 2')
        ])
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $image_1 = wp_get_attachment_image($fields["image_1"], 'medium_large');
            $image_2 = wp_get_attachment_image($fields["image_2"], 'medium_large');
            ?>
            <div class="platform-image-pair">
                <div class="platform-image-pair__images">
                    <?= $image_1 ?>
                    <?= $image_2 ?>
                </div>
            </div>
            <?php
        });

    Container::make('post_meta', 'Extra Fields')
        ->where('post_type', 'IN', ['page', 'pl_project'])
        ->add_fields(array(
            Field::make('complex', 'background_images', 'Background Images')
                ->add_fields([
                    Field::make('image', 'image', 'Image')->set_value_type('url')
                ])
        ));

    Block::make(__('Background Images'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Background Images'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $background_images = carbon_get_the_post_meta('background_images');
            $count = count($background_images);
            if (!$background_images) {
                return;
            }
            ?>
            <div class="background-images hidden md:block absolute w-full h-full">
                <?php foreach ($background_images as $i => $background_image) {
                    $top = (100 * $i / $count) . '%';
                    $even = $i % 2 === 0;
                    $style = "position:absolute;z-index:0;top:$top;mix-blend-mode:multiply;max-width:20%;";
                    if ($even) {
                        $style .= "left:0";
                    } else {
                        $style .= "right:0";
                    }
                    ?>
                    <img style="<?= $style ?>" src="<?= $background_image["image"] ?>">
                <?php } ?>
            </div>
            <?php
        });

    /* Index Page Blocks */
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

    /* Search Page Blocks */
    Block::make(__('Search Results Count'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Search Results Count'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            global $wp_query;
            $count = $wp_query->found_posts;
            $plural = $count == 1 ? "" : "S";
            ?>
            <div class="search-results-count">
                <h2><?= $count ?> RESULT<?= $plural ?></h2>
            </div>
            <?php
        });

    Block::make(__('Search Sort'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Search Sort'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $sort = $_GET["sort"] ?? "";
            ?>
            <div class="search-sort">
                <!-- onchange handler is in script.js -->
                <select>
                    <option value="desc" <?= $sort !== "asc" ? "selected" : "" ?>>
                        Sort: newest to oldest
                    </option>
                    <option value="asc" <?= $sort === "asc" ? "selected" : "" ?>>
                        Sort: oldest to newest
                    </option>
                </select>
            </div>
            <?php
        });

    Block::make(__('Search Filter'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Search Filter'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $active_category = $_GET["category_name"] ?? "";
            $active_year = $_GET["year"] ?? "";
            $active_author = $_GET["author"] ?? "";
            $filter_sections = [];
            $filter_sections[] = [
                "title" => "Focus Areas",
                "param" => "category_name",
                "options" => array_map(function ($category) use ($active_category) {
                    return [
                        "name" => $category->name,
                        "value" => $category->slug,
                        "selected" => str_contains($active_category, $category->slug)
                    ];
                }, get_categories())
            ];
            foreach (["content-type", "pl_post_type", "pl_project_type"] as $taxonomy) {
                $active_term = $_GET[$taxonomy] ?? "";
                $title = get_taxonomy($taxonomy)->labels->singular_name;
                $filter_sections[] = [
                    "title" => $title,
                    "param" => $taxonomy,
                    "options" => array_map(function ($term) use ($active_term) {
                        return [
                            "name" => $term->name,
                            "value" => $term->slug,
                            "selected" => str_contains($active_term, $term->slug)
                        ];
                    }, get_terms(["taxonomy" => $taxonomy]))
                ];
            }

            function get_year_options()
            {
                $loop = get_posts('numberposts=10&post_type=any&order=ASC');
                $date = $loop[0]->post_date;
                $year = (int) explode("-", $date)[0];
                $current_year = (int) date("Y");
                $years = [];
                while ($year <= $current_year) {
                    $years[] = $year;
                    $year++;
                }
                return $years;
            }

            $filter_sections[] = [
                "title" => "Year",
                "param" => "year",
                "options" => array_map(function ($year) use ($active_year) {
                    return [
                        "name" => $year,
                        "value" => $year,
                        "selected" => str_contains($active_year, $year)
                    ];
                }, get_year_options())
            ];

            $filter_sections[] = [
                "title" => "Author",
                "param" => "author",
                "options" => array_map(function ($author) use ($active_author) {
                    return [
                        "name" => $author->display_name,
                        "value" => $author->ID,
                        "selected" => str_contains($active_author, $author->ID)
                    ];
                }, get_users())
            ];

            ?>
            <div class="search-filter">
                <?php foreach ($filter_sections as $section) : ?>
                    <div class="search-filter__section">
                        <button class="search-filter__expand"><?= $section['title'] ?></button>
                        <ul class="search-filter__options">
                            <?php foreach ($section['options'] as $option) {
                                $id = "filter-" . $section['param'] . "-" . $option['value'];
                                ?>
                                <li>
                                    <input 
                                        id="<?= $id ?>" 
                                        type="checkbox"
                                        value="<?= $option['value'] ?>"
                                        <?= $option['selected'] ? "checked" : "" ?>
                                        data-param="<?= $section['param'] ?>"
                                    >
                                    <label for="<?= $id ?>"><?= $option['name'] ?></label>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        });

    /* Required for the search page because the built-in "No results"
       displays when there is one result and it has a featured image */
    Block::make(__('Search No Results'))
        ->add_fields(array(
            Field::make('text', 'no_results_text', __('No results text'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            global $wp_query;
            if ($wp_query->found_posts > 0) {
                return;
            }
            ?>
            <div class="wp-block-query-no-results">
                <p><?= $fields['no_results_text'] ?></p>
            </div>
            <?
        });

    /* Post Fields and Blocks */
    Block::make(__('Post Header'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Post Header')),
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            ?>
            <div class="post-header">
                <span class="btn-default">Blog</span>
                <h1 class="wp-block-post-title"><?= get_the_title() ?></h1>
            </div>
            <?php
        });

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
            $first_content_type = array_pop($content_types);
            $post_date = get_the_date('j M Y');
            $author = get_the_author();
            $author_id = get_the_author_meta('ID');
            $author = $author ? explode(" ", $author)[0] : "";

            ?>
            <div class="post-details">
                <?php if ($first_content_type) :?>
                    <div class="post-details__terms">
                        <a href="/?s=&<?= $taxonomy ?>=<?= $first_content_type->slug ?>">
                            <?= $first_content_type->name ?>
                        </a>
                    <?php foreach ($content_types as $content_type) :?>
                        ,&nbsp;
                        <a href="/?s=&<?= $taxonomy ?>=<?= $content_type->slug ?>">
                            <?= $content_type->name ?>
                        </a>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($post->post_type === "pl_project") : ?>
                    <?php render_project_dates(display_active: true); ?>
                <?php else : ?>
                    <span><?= $post_date ?></span>
                    <a href="/?s&author=<?= $author_id ?>">
                        <?= $author ?>
                    </a>
                <?php endif ?>
            </div>
            <?php
        });

    Block::make(__('Post Details Footer'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Post Details Footer'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $post = get_post();
            $categories = wp_get_post_categories($post->ID, ['fields' => 'all']);
            $taxonomies = [
                "pl_project_type",
                "pl_place",
                "pl_player",
                "pl_issue",
                "pl_organisation"
            ];
            ?>
            <div class="post-details-footer">
                <?php if ($categories) : ?>
                    <span class="post-details-footer__label">Focus Areas</span>
                    <ul class="post-details-footer__categories">
                        <?php foreach ($categories as $category) {
                            $svg = get_category_svg($category->slug);
                            ?>
                            <li class="post-details-footer__category">
                                <?php if ($svg) : ?>
                                    <svg fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <?= $svg ?>
                                    </svg>
                                <?php endif; ?>
                                <a class="btn-default category-<?= $category->slug ?>" href="<?= get_category_link($category) ?>">
                                    <?= $category->name ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php endif; ?>
                <?php foreach ($taxonomies as $taxonomy) {
                    $terms = get_the_terms($post, $taxonomy);
                    $title = get_taxonomy($taxonomy)->labels->name;
                    $a = 3;
                    ?>
                    <?php if ($terms) : ?>
                        <span class="post-details-footer__label">
                            <?= $title ?>
                        </span>
                        <ul class="post-details-footer__terms">
                            <?php foreach ($terms as $term) : ?>
                                <li class="post-details-footer__term">
                                    <a href="<?= get_term_link($term, $taxonomy) ?>">
                                        <?= $term->name ?>
                                    </a>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif; ?>
                <?php } ?>
            </div>
            <?php
        });

    /* Project Fields and Blocks */
    Container::make('post_meta', 'Extra Fields')
        ->where('post_type', '=', 'pl_project')
        ->add_fields(array(
            Field::make('text', 'start_year')->set_attribute('type', 'number'),
            Field::make('text', 'end_year')->set_attribute('type', 'number'),
            Field::make('file', 'pdf', 'Project PDF')->set_type('application/pdf')->set_value_type('url')
        ));

    Block::make(__('Project Dates'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Project Dates'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            render_project_dates();
        });

    Block::make(__('Project Header'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Project Header')),
            Field::make('image', 'background_image', 'Background image')->set_value_type('url')
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $image = $fields["background_image"] ?? null;
            $download_url = carbon_get_the_post_meta("pdf");
            ?>
            <div class="project-header">
                <div class="project-header__cover" style="background-image:url('<?= $image ?>')">
                    <span class="btn-default">Project</span>
                    <h1 class="wp-block-post-title"><?= get_the_title() ?></h1>
                    <div class="project-header__buttons">
                        <?php if (is_project_active()) : ?>
                            <span class="btn-default btn-active">Active</span>
                        <?php endif; ?>
                        <span class="btn-default bg-cream">
                            <?= get_project_dates() ?>
                        </span>
                        <?php if ($download_url) : ?>
                            <a 
                                target="_blank"
                                class="btn-default bg-cream project-download-link"
                                href="<?= $download_url ?>">
                                Download PDF
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        });

    Block::make(__('Project Download Link'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Project Download Link'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $download_url = carbon_get_the_post_meta("pdf");
            if ($download_url) : ?>
                <div class="project-download-link-container">
                    <a 
                        target="_blank"
                        class="btn-default bg-cream project-download-link"
                        href="<?= $download_url ?>">
                        Download PDF
                    </a>
                </div>
            <?php endif;
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

    /* Timeline Blocks */
    Container::make('post_meta', 'Extra Fields')
        ->where('post_type', '=', 'pl_timeline_entry')
        ->add_fields(array(
            Field::make('text', 'year', "Year (YYYY)")->set_attribute('type', 'number')
                ->set_required(true),
        ));

    Block::make(__('Timeline Entries'))
        ->add_fields(array(
            Field::make('separator', 'crb_separator', __('Timeline Entries'))
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            $timeline_entries = get_posts([
                'post_type' => 'pl_timeline_entry',
                'post_status' => 'publish',
                'numberposts' => -1
            ]);
            if (!count($timeline_entries)) {
                echo '<p>No timeline entries found</p>';
                return;
            }
            $decades = [];
            usort($timeline_entries, function ($a, $b) use (&$decades) {
                $year_a = carbon_get_post_meta($a->ID, 'year');
                $year_b = carbon_get_post_meta($b->ID, 'year');

                // Side effect in sort function? Gross! But efficient...
                $decade_a = substr($year_a, 0, 3) . '0s';
                $decade_b = substr($year_b, 0, 3) . '0s';
                $decades[$decade_a] = true;
                $decades[$decade_b] = true;

                return $year_a < $year_b ? -1 : 1;
            });
            $decades = array_keys($decades);
            sort($decades);
            $current_decade = "1900s";
            ?>
            <div class="platform-timeline-links">
                <div>
                    <ul class="platform-timeline-links__list">
                    <?php foreach ($decades as $decade) : ?>
                        <li>
                            <a href="#<?= $decade ?>" class="btn-default bg-cream">
                                <?= $decade ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="platform-timeline">
                <div class="platform-timeline__line">
                    <div class="platform-timeline__marker-container">
                        <div class="platform-timeline__marker">
                            <span class="platform-timeline__year">
                                <?= carbon_get_post_meta($timeline_entries[0]->ID, 'year') ?>
                            </span>
                            <div class="platform-timeline__circle"></div>
                        </div>
                        <div class="platform-timeline__active-line"></div>
                    </div>
                </div>
                <div class="platform-timeline__entries">
                    <?php foreach ($timeline_entries as $entry) {
                        $year = carbon_get_post_meta($entry->ID, 'year');
                        if (substr($year, 0, 3) !== substr($current_decade, 0, 3)) {
                            $current_decade = substr($year, 0, 3) . '0s';
                            $id = $current_decade;
                        } else {
                            $id = null;
                        } ?>
                        <div <?= $id ? 'id="' . $id . '"' : '' ?> 
                            class="platform-timeline__entry"
                            data-year="<?= $year ?>"
                        >
                            <div class="platform-timeline__circle"></div>
                            <h2><?= get_the_title($entry) ?></h2>
                            <div>
                                <?= apply_filters('the_content', $entry->post_content); ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php
        });
});

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
    // Special value ":related" means get posts with categories that the
    // current post has
    if (($query['s'] ?? '') === ':related') {
        $current_post = get_post();
        $categories = wp_get_post_categories($current_post->ID);
        if ($categories) {
            $query["category__in"] = $categories;
        }
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

add_action('pre_get_posts', function ($query) {
    // Ignore special parameters in block editor
    if ($query->get("s") === ':all' || $query->get("s") === ':related') {
        $query->set("s", "");
        return $query;
    }
    if ($query->is_search()) {
        $slugs_to_exclude = ["blog", "resources", "projects"];
        $ids_to_exclude = array_map(function ($slug) {
            return url_to_postid($slug);
        }, $slugs_to_exclude);
        $query->set("post__not_in", $ids_to_exclude);
        $sort_order = $_GET["sort"] ?? "";
        $query->set("orderby", [
            'date' => strtoupper($sort_order),
        ]);
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

    register_post_type(
        'pl_timeline_entry',
        array(
            'labels'      => array(
                'name'          => 'Timeline Entries',
                'singular_name' => 'Timeline Entry',
            ),
            'public'      => true,
            'has_archive' => false,
            'menu_icon' => 'dashicons-calendar',
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'author'),
            'exclude_from_search' => true,
            'taxonomies' => []
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

    register_taxonomy('pl_organisation', ['post'], [
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'organisation'],
        'labels'            => [
            'name'              => _x('Organisations', 'taxonomy general name'),
            'singular_name'     => _x('Organisation', 'taxonomy singular name'),
        ]
    ]);

    register_taxonomy('pl_player', ['post'], [
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'player'],
        'labels'            => [
            'name'              => _x('Players', 'taxonomy general name'),
            'singular_name'     => _x('Player', 'taxonomy singular name'),
        ]
    ]);

    register_taxonomy('pl_issue', ['post'], [
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'issue'],
        'labels'            => [
            'name'              => _x('Issues', 'taxonomy general name'),
            'singular_name'     => _x('Issue', 'taxonomy singular name'),
        ]
    ]);

    register_taxonomy('pl_place', ['post'], [
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'place'],
        'labels'            => [
            'name'              => _x('Places', 'taxonomy general name'),
            'singular_name'     => _x('Place', 'taxonomy singular name'),
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
