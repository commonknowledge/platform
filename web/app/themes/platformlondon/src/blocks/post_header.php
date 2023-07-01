<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

/* Post Header Block (at the top of Single Post page) */

Block::make(__('Post Header'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Post Header')),
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $post = get_post();
        $post_type = get_post_type_object($post->post_type);
        $post_type_href = match ($post->post_type) {
            "post" => "/blog/",
            "pl_resource" => "/resources/",
            default => "/"
        };
        $post_type_taxonomy = get_custom_post_type_taxonomy($post);
        $post_types = get_the_terms($post, $post_type_taxonomy);
        $post_date = get_the_date('j M Y');
        $author_id = $post->post_author;
        $author = get_the_author_meta("display_name", $author_id);
        $download_url = carbon_get_post_meta($post->ID, "pdf");
    ?>
        <div class="post-header">
            <a href="<?= $post_type_href ?>" class="btn-default">
                <?= $post_type->labels->singular_name ?>
            </a>
            <h1 class="wp-block-post-title"><?= get_the_title() ?></h1>
            <div class="post-header__details">
                <?php if ($post_types) : ?>
                    <a href="<?= get_term_link($post_types[0], $post_type_taxonomy) ?>">
                        <?= $post_types[0]->name ?>
                    </a>
                <?php endif ?>
                <span><?= $post_date ?></span>
                <a href="/?s&author=<?= $author_id ?>">
                    <?= $author ?>
                </a>
            </div>
            <?php if ($download_url) : ?>
                <a target="_blank" href="<?= $download_url ?>" class="btn-default project-download-link mt-8">
                    Download PDF
                </a>
            <?php endif ?>
        </div>
        <?php
    });