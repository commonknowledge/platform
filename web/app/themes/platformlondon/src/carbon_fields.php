<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/* Project Fields and Blocks */


/* Page Fields */
Container::make('post_meta', 'Extra Fields')
    ->where('post_type', 'IN', ['page', 'pl_project'])
    ->add_fields(array(
        Field::make('complex', 'background_images', 'Background Images')
            ->add_fields([
                Field::make('image', 'image', 'Image')->set_value_type('url')
            ])
    ));

Container::make('post_meta', 'Related Content')
    ->where('post_type', 'IN', ['post', 'pl_project', 'pl_resource'])
    ->add_fields(array(
        Field::make('association', 'related', 'Related')
            ->set_types([
                [
                    'type'      => 'post',
                    'post_type' => 'pl_resource',
                ],
                [
                    'type'      => 'post',
                    'post_type' => 'post',
                ],
                [
                    'type'      => 'post',
                    'post_type' => 'pl_project',
                ]
            ])
    ));

/* Member Fields */
Container::make('post_meta', 'Member Metadata')
    ->where('post_type', '=', 'pl_member')
    ->add_fields(array(
        Field::make('text', 'position'),
    ));

/* Resource Fields */
Container::make('post_meta', 'Resource Metadata')
    ->where('post_type', '=', 'pl_resource')
    ->add_fields(array(
        Field::make('file', 'pdf', 'Resource PDF')->set_type('application/pdf')->set_value_type('url'),
        Field::make('association', 'members', 'Team')
        ->set_types([
            [
                'type'      => 'post',
                'post_type' => 'pl_member',
            ]
        ])
    ));

/* Timeline Item Fields */
Container::make('post_meta', 'Timeline Date')
    ->where('post_type', '=', 'pl_timeline_entry')
    ->add_fields(array(
        Field::make('text', 'year', "Year (YYYY)")->set_attribute('type', 'number')
            ->set_required(true),
    ));
