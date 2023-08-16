<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Platform Illustration'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Platform Illustration'))
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        ?>
        <div class="platform-illustration">
            <?= get_category_svg("liberation") ?>
            <?= get_category_svg("community") ?>
            <?= get_category_svg("economy") ?>
            <?= get_category_svg("culture") ?>
            <?= get_category_svg("energy") ?>
        </div>
        <?php
    });