<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make('Image Pair')
    ->add_fields([
        Field::make('image', 'image_1', 'Image 1'),
        Field::make('image', 'image_2', 'Image 2')
    ])
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $image_1 = wp_get_attachment_image($fields["image_1"], 'medium_large');
        $image_2 = wp_get_attachment_image($fields["image_2"], 'medium_large');
        ?>
        <div class="platform-image-pair">
            <div class="platform-image-pair__images">
                <?= $image_1 ?>
                <?= $image_2 ?>
            </div>
        </div>
        <?php
    });