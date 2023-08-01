<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Post Filter'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Post Filter'))
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $categories = get_categories();
        switch (get_post()->post_name) {
            case "resources":
                $taxonomy = "pl_resource_type";
                $post_type_label = "Resource";
                break;
            case "projects":
                $taxonomy = "pl_project_type";
                $post_type_label = "Project";
                break;
            case "media":
                $taxonomy = "pl_multimedia_type";
                $post_type_label = "Multimedia";
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