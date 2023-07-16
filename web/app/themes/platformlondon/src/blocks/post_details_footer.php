<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Post Details Footer'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Post Details Footer'))
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $post = get_post();
        $categories = wp_get_post_categories($post->ID, ['fields' => 'all']);
        $taxonomies = [
            "pl_project_type",
            "pl_resource_type",
            "pl_place",
            "pl_player",
            "pl_issue",
            "pl_organisation"
        ];
        $members = array_map(function ($m) {
            return get_post($m["id"]);
        }, carbon_get_the_post_meta("members") ?? []);
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
            <?php if ($members) : ?>
                <span class="post-details-footer__label">
                    Team
                </span>
                <ul class="post-details-footer__terms">
                    <?php foreach ($members as $member) : ?>
                        <li class="post-details-footer__term">
                            <a href="<?= get_permalink($member) ?>">
                                <?= $member->post_title ?>
                            </a>
                        </li>
                    <?php endforeach ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    });