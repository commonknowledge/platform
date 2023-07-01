<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make('post_meta', 'Project Metadata')
    ->where('post_type', '=', 'pl_project')
    ->add_fields(array(
        Field::make('text', 'start_year')->set_attribute('type', 'number'),
        Field::make('text', 'end_year')->set_attribute('type', 'number'),
        Field::make('image', 'background_image', 'Background Image')->set_value_type('url'),
        Field::make('text', 'url', 'Project Website')->set_attribute('type', 'url'),
        Field::make('file', 'pdf', 'Project PDF')->set_type('application/pdf')->set_value_type('url'),
        Field::make('association', 'members', 'Team')
            ->set_types([
                [
                    'type'      => 'post',
                    'post_type' => 'pl_member',
                ]
            ])
    ));
