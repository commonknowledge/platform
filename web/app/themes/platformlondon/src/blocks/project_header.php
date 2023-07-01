<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Project Header'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Project Header')),
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $image = carbon_get_the_post_meta("background_image");
        $download_url = carbon_get_the_post_meta("pdf");
        $website_url = carbon_get_the_post_meta("url");
        $cover_class = $image ? "" : "project-header__cover--no-image";
        ?>
        <div class="project-header">
            <div class="project-header__cover <?= $cover_class ?>" style="background-image:url('<?= $image ?>')">
                <a class="btn-default" href="/projects/">Project</a>
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
                    <?php if ($website_url) : ?>
                        <a 
                            target="_blank"
                            class="btn-default bg-cream project-website-link"
                            href="<?= $website_url ?>">
                            <?= $website_url ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    });