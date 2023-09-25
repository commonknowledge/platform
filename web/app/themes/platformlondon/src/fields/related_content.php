<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

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
                ],
                [
                    'type'      => 'post',
                    'post_type' => 'pl_multimedia',
                ]
            ])
    ));
