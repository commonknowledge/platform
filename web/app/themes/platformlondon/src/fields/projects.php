<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

function get_category_options()
{
    return [
        'community' => 'Community',
        'culture' => 'Culture',
        'energy' => 'Energy',
        'economy' => 'Finance',
        'liberation' => 'Liberation',
        'default' => 'Default'
    ];
}

$projects_page_id = get_page_by_path('projects', OBJECT, 'page')->ID;
Container::make('post_meta', 'Cover Images')
    ->where('post_id', '=', $projects_page_id)
    ->add_fields(array(
        Field::make('complex', 'cover_images', 'Cover Images')
            ->add_fields([
                Field::make('select', 'category', __('Category'))->add_options('get_category_options'),
                Field::make('image', 'image', 'Image')->set_value_type('url')
            ])
    ));
