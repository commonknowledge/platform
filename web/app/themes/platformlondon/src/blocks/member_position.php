<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Member Position'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Position'))
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $position = carbon_get_the_post_meta('position');

        if ($position) {
            echo "<p class=\"uppercase mb-2\">$position</p>";
        }
    });
