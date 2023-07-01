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
            <img class="platform-illustration__background" src="/app/themes/platformlondon/assets/img/platform-illustration.png" />
            <svg width="671" height="470" viewBox="0 0 671 470" fill="none" xmlns="http://www.w3.org/2000/svg">
                <?= get_category_svg("economy") ?>
                <?= get_category_svg("culture") ?>
                <?= get_category_svg("liberation") ?>
                <?= get_category_svg("energy") ?>
                <?= get_category_svg("community") ?>
            </svg>
        </div>
        <?php
    });