<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make('post_meta', 'Background Image')
    ->where('post_type', '=', 'pl_project')
    ->add_fields(array(
        Field::make('image', 'background_image', 'Background Image')->set_value_type('url'),
    ));

Container::make('post_meta', 'Project Metadata')
    ->where('post_type', '=', 'pl_project')
    ->add_fields(array(
        Field::make('text', 'start_year')->set_attribute('type', 'number'),
        Field::make('text', 'end_year')->set_attribute('type', 'number'),
        Field::make('text', 'url', 'Project Website')->set_attribute('type', 'url'),
        Field::make('text', 'url_title', 'Project Website Title'),
        Field::make('complex', 'pdfs', 'Project PDFs')
            ->add_fields([
                Field::make('file', 'pdf', 'PDF')
            ]),
        Field::make('association', 'members', 'Team')
            ->set_types([
                [
                    'type'      => 'post',
                    'post_type' => 'pl_member',
                ]
                ]),
        Field::make('complex', 'external_members', 'Additional Team Members')
            ->add_fields([
                Field::make('text', 'name', 'Name')
            ]),
    ));
