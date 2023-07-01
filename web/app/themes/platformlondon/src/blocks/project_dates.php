<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Project Dates'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Project Dates'))
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        render_project_dates();
    });
