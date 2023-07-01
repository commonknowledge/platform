<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Post Download Link'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Post Download Link'))
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $download_url = carbon_get_the_post_meta("pdf");
        if ($download_url) : ?>
            <div class="post-download-link-container">
                <a 
                    target="_blank"
                    class="btn-default bg-cream post-download-link"
                    href="<?= $download_url ?>">
                    Download PDF
                </a>
            </div>
        <?php endif;
    });