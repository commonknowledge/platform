<?php

const CATEGORY_SVG_SLUGS = ["community", "economy", "liberation", "culture", "energy"];

// Bump when the files in assets/img/illustration change, so browsers fetch the new versions
const CATEGORY_SVG_VERSION = "1";

/**
 * Placeholder for a category illustration. The SVGs are several MB each, so script.js
 * loads them from assets/img/illustration/ when they come into view instead of
 * inlining them into every page.
 */
function get_category_svg($slug, $link = true)
{
    if (!in_array($slug, CATEGORY_SVG_SLUGS, true)) {
        return "";
    }
    $src = get_template_directory_uri() . "/assets/img/illustration/$slug.svg?ver=" . CATEGORY_SVG_VERSION;
    $placeholder = '<span class="category-svg-placeholder" data-category-svg="' . esc_attr($src) . '"></span>';
    if (!$link) {
        return $placeholder;
    }
    $link = "/projects/?category_name=$slug";
    return (
        '<a href="' . $link . '">' .
        $placeholder .
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
        $content = "ACTIVE&nbsp;&nbsp;&nbsp;&nbsp;" . $content;
    }

    echo "<span class=\"project-dates text-right uppercase\">$content</span>";
}

function get_custom_post_type_taxonomy($post)
{
    switch ($post->post_type) {
        case "post":
            return "pl_post_type";
        case "pl_project":
            return "pl_project_type";
        case "pl_resource":
        default:
            return "pl_resource_type";
    }
}
