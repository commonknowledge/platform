<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Project Header'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Project Header')),
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $image = carbon_get_the_post_meta("background_image");
        $website_url = carbon_get_the_post_meta("url");
        $website_title = carbon_get_the_post_meta("url_title");
        $cover_class = $image ? "" : "project-header__cover--no-image";

        $pdfs_meta = carbon_get_the_post_meta("pdfs");
        $files = [];
        foreach ($pdfs_meta as $pdf_meta) {
            $file_id = $pdf_meta['pdf'];
            $files[] = [
                "title" => get_the_title($file_id),
                "url" => wp_get_attachment_url($file_id)
            ];
        }

        ?>
        <div class="project-header">
            <div class="project-header__cover <?= $cover_class ?>" style="background-image:url('<?= $image ?>')">
                Project
                <h1 class="wp-block-post-title"><?= get_the_title() ?></h1>
                <div class="project-header__buttons">
                    <?php if (is_project_active()) : ?>
                        <span class="btn-default btn-active">Active</span>
                    <?php endif; ?>
                    <span class="btn-default bg-cream">
                        <?= get_project_dates() ?>
                    </span>
                    <?php if ($website_url) : ?>
                        <a 
                            target="_blank"
                            class="btn-default bg-cream project-website-link"
                            href="<?= $website_url ?>">
                            <?= $website_title ?: $website_url ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    });