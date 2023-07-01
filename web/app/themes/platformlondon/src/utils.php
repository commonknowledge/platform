<?php

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
            return "content-type";
    }
}
