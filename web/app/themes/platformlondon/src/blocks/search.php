<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

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
        $params = [
            "category_name",
            "author",
            "member",
            "year"
        ];
        $param_values = [];
        foreach ($params as $param) {
            $value = $_GET[$param] ?? null;
            $param_values[$param] = $value ? explode(",", $value) : [];
        }

        $filter_sections = [];
        $filter_sections[] = [
            "title" => "Focus Areas",
            "param" => "category_name",
            "options" => array_map(function ($category) use ($param_values) {
                return [
                    "name" => $category->name,
                    "value" => $category->slug,
                    "selected" => in_array($category->slug, $param_values["category_name"])
                ];
            }, get_categories())
        ];

        $taxonomies = [
            "pl_post_type",
            "pl_resource_type",
            "pl_project_type",
            "pl_place",
            "pl_player",
            "pl_issue",
            "pl_organisation"
        ];
        foreach ($taxonomies as $taxonomy) {
            $active_term = $_GET[$taxonomy] ?? "";
            $active_terms = explode(",", $active_term);
            $title = get_taxonomy($taxonomy)->labels->singular_name;
            $filter_sections[] = [
                "title" => $title,
                "param" => $taxonomy,
                "options" => array_map(function ($term) use ($active_terms) {
                    return [
                        "name" => $term->name,
                        "value" => $term->slug,
                        "selected" => in_array($term->slug, $active_terms)
                    ];
                }, get_terms(["taxonomy" => $taxonomy]))
            ];
        }

        $filter_sections[] = [
            "title" => "Author",
            "param" => "author",
            "options" => array_map(function ($author) use ($param_values) {
                return [
                    "name" => $author->display_name,
                    "value" => $author->ID,
                    "selected" => in_array($author->ID, $param_values["author"])
                ];
            }, get_users())
        ];

        $filter_sections[] = [
            "title" => "Team",
            "param" => "member",
            "options" => array_map(function ($member) use ($param_values) {
                return [
                    "name" => $member->post_title,
                    "value" => $member->ID,
                    "selected" => in_array($member->ID, $param_values["member"])
                ];
            }, get_posts('numberposts=-1&post_type=pl_member&orderby=post_name&order=ASC'))
        ];

        function get_year_options()
        {
            $loop = get_posts('numberposts=1&post_type=any&order=ASC');
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
            "options" => array_map(function ($year) use ($param_values) {
                return [
                    "name" => $year,
                    "value" => $year,
                    "selected" => in_array($year, $param_values["year"])
                ];
            }, get_year_options())
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
        <?php
    });
