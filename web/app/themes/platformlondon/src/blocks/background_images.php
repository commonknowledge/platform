<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Background Images'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Background Images'))
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $background_images = carbon_get_the_post_meta('background_images');
        $count = count($background_images);
        if (!$background_images) {
            return;
        }
        ?>
        <div class="background-images hidden md:block absolute w-full h-full">
            <?php foreach ($background_images as $i => $background_image) {
                $top = (100 * $i / $count) . '%';
                $even = $i % 2 === 0;
                $style = "position:absolute;z-index:0;top:$top;mix-blend-mode:multiply;max-width:20%;";
                if ($even) {
                    $style .= "left:0";
                } else {
                    $style .= "right:0";
                }
                ?>
                <img style="<?= $style ?>" src="<?= $background_image["image"] ?>">
            <?php } ?>
        </div>
        <?php
    });