<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make('post_meta', 'Extra Fields')
    ->where('post_type', 'IN', ['page', 'pl_project'])
    ->add_fields(array(
        Field::make('complex', 'background_images', 'Background Images')
            ->add_fields([
                Field::make('image', 'image', 'Image')->set_value_type('url')
            ])
    ));
