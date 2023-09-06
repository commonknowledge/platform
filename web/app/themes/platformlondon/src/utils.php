<?php

require_once(__DIR__ . "/svgs/community.php");
require_once(__DIR__ . "/svgs/culture.php");
require_once(__DIR__ . "/svgs/energy.php");
require_once(__DIR__ . "/svgs/economy.php");
require_once(__DIR__ . "/svgs/liberation.php");

const CATEGORY_SVGS = [
    "community" => COMMUNITY_SVG,
    "economy" => ECONOMY_SVG,
    "liberation" => LIBERATION_SVG,
    "culture" => CULTURE_SVG,
    "energy" => ENERGY_SVG,
];

function get_category_svg($slug)
{
    $category = get_category_by_slug($slug);
    if (empty(CATEGORY_SVGS[$slug])) {
        return "";
    }
    $link = "/projects/?category_name=$slug";
    return (
        '<a href="' . $link . '">' .
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
