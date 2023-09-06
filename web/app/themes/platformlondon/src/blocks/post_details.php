<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

/* Post Details Block (used in Query Loop Post Templates) */

Block::make(__('Post Details'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Post Details'))
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $post = get_post();
        $post_type_taxonomy = get_custom_post_type_taxonomy($post);
        $post_types = get_the_terms($post, $post_type_taxonomy) ?: [];
        $first_post_type = array_pop($post_types);
        $post_date = get_the_date('j M Y');
        $author_id = $post->post_author;
        $author = get_the_author_meta("display_name", $author_id);
        $author = $author ? explode(" ", $author)[0] : "";

        ?>
        <div class="post-details">
            <?php if ($first_post_type) :?>
                <div class="post-details__terms">
                    <a href="<?= get_term_link($first_post_type, $post_type_taxonomy) ?>">
                        <?= $first_post_type->name ?><?= $post_types ? ",&nbsp;" : "" ?>
                    </a>
                <?php foreach ($post_types as $post_type) :?>
                    <a href="<?= get_term_link($post_type, $post_type_taxonomy) ?>">
                        <?= $post_type->name ?>
                    </a>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($post->post_type === "pl_project") : ?>
                <?php render_project_dates(display_active: true); ?>
            <?php else : ?>
                <span><?= $post_date ?></span>
                <a  href="/?s&author=<?= $author_id ?>">
                    <?= $author ?>
                </a>
            <?php endif ?>
        </div>
        <?php
    });
