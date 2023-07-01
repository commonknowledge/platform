<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

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
