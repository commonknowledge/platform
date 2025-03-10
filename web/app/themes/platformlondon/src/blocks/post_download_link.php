<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Post Download Link'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Post Download Link'))
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $pdfs_meta = carbon_get_the_post_meta("pdfs");
        $files = [];
        foreach ($pdfs_meta as $pdf_meta) {
            $file_id = $pdf_meta['pdf'];
            $files[] = [
                "title" => get_the_title($file_id),
                "url" => wp_get_attachment_url($file_id)
            ];
        }
        if ($files) : ?>
            <div class="post-download-link-block">
                <h2>Downloads</h2>
                <?php foreach ($files as $file) : ?>
                <a class="btn-default" href="<?= $file['url'] ?>" download><?= $file['title'] ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif;
    });